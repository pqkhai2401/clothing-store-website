<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $status = $request->input('status');
        $sort = $request->input('sort', 'name');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'asc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Category::with(['parentCategory'])
            ->withCount(['products', 'activeProducts', 'childrenCategories']);

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        if (in_array($status, ['0', '1'], true)) {
            $query->where('status', (bool) (int) $status);
        }

        match ($sort) {
            'id' => $query->orderBy('id', $direction),
            'products_count' => $query->orderBy('products_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('parent_id')->orderBy('name', $direction),
        };

        $categories = $query->paginate($perPage)->withQueryString();
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get(['id', 'name']);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.categories.partials.table', compact('categories'))->render(),
            ]);
        }

        return view('admin.categories.index', compact('categories', 'keyword', 'perPage', 'parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'status'    => ['required', Rule::in([0, 1, '0', '1'])],
        ], [
            'name.required'    => 'Tên danh mục không được để trống.',
            'name.max'         => 'Tên danh mục không được quá 255 ký tự.',
            'parent_id.exists' => 'Danh mục cha không hợp lệ.',
            'status.required'  => 'Vui lòng chọn trạng thái.',
        ]);

        $slug = $this->uniqueCategorySlug(Str::slug($validated['name']));

        $category = Category::create([
            'name'      => $validated['name'],
            'slug'      => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'status'    => (bool) (int) $validated['status'],
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message'  => "Thêm danh mục \"{$category->name}\" thành công.",
                'category' => $category,
            ], 201);
        }

        return redirect()->route('admin.categories.list')
            ->with('success', "Thêm danh mục \"{$category->name}\" thành công.");
    }

    public function edit(string $id)
    {
        $category = Category::findOrFail($id);

        // Lấy danh sách danh mục cha (không chọn chính nó hoặc con của nó để tránh vòng lặp)
        $parentOptions = Category::whereNull('parent_id')
            ->where('id', '!=', $id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentOptions'));
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($id)],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id'), Rule::notIn([$id])],
        ], [
            'name.required'      => 'Tên danh mục không được để trống.',
            'name.max'           => 'Tên danh mục không được quá 255 ký tự.',
            'slug.unique'        => 'Slug này đã tồn tại, vui lòng dùng slug khác.',
            'parent_id.exists'   => 'Danh mục cha không hợp lệ.',
            'parent_id.not_in'   => 'Danh mục không thể là cha của chính nó.',
        ]);

        // Tạo slug từ tên nếu không nhập, đảm bảo không trùng (trừ bản thân)
        $slug = $validated['slug']
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $slug = $this->uniqueCategorySlug($slug, (int) $id);

        $category->update([
            'name'      => $validated['name'],
            'slug'      => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return redirect()->route('admin.categories.list')
            ->with('success', "Cập nhật danh mục \"{$category->name}\" thành công.");
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if ($message = $this->categoryDeleteBlocker($category)) {
            return redirect()->route('admin.categories.list')
                ->with('error', $message);
        }

        $category->delete();

        return redirect()->route('admin.categories.list')->with('success', 'Xóa danh mục thành công');
    }

    public function toggleStatus(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $newStatus = !$category->status;
        $category->update(['status' => $newStatus]);

        $msg = $newStatus
            ? "Danh mục \"{$category->name}\" đã được hiển thị."
            : "Danh mục \"{$category->name}\" đã được ẩn khỏi website.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'status' => $newStatus]);
        }

        return back()->with('success', $msg);
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một danh mục để xóa.');
        }

        $categories = Category::whereIn('id', $ids)->get();
        $blockers = $categories
            ->map(fn (Category $category) => $this->categoryDeleteBlocker($category, $ids))
            ->filter();

        if ($blockers->isNotEmpty()) {
            return back()->with('error', $blockers->first());
        }

        $deleted = 0;
        foreach ($categories as $category) {
            $deleted += (int) $category->delete();
        }

        return back()->with('success', "Đã xóa {$deleted} danh mục thành công.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Category::onlyTrashed()
            ->with('parentCategory')
            ->withCount('products')
            ->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $categories = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.categories.partials.trash-table', compact('categories'))->render(),
            ]);
        }

        return view('admin.categories.trash', compact('categories', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        Category::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.categories.trash')->with('success', 'Khôi phục danh mục thành công');
    }

    public function forceDelete(string $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);

        if ($message = $this->categoryForceDeleteBlocker($category)) {
            return redirect()->route('admin.categories.trash')
                ->with('error', $message);
        }

        $category->forceDelete();

        return redirect()->route('admin.categories.trash')->with('success', 'Xóa vĩnh viễn danh mục thành công');
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một danh mục.');
        }
        $restored = Category::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', "Đã khôi phục {$restored} danh mục thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một danh mục.');
        }
        $categories = Category::onlyTrashed()->whereIn('id', $ids)->get();
        $blockers = $categories
            ->map(fn (Category $category) => $this->categoryForceDeleteBlocker($category))
            ->filter();

        if ($blockers->isNotEmpty()) {
            return back()->with('error', $blockers->first());
        }

        $count = 0;
        foreach ($categories as $category) {
            $count += (int) $category->forceDelete();
        }

        return back()->with('success', "Đã xóa vĩnh viễn {$count} danh mục.");
    }

    private function categoryDeleteBlocker(Category $category, array $selectedIds = []): ?string
    {
        if ($category->products()->where('status', true)->exists()) {
            return 'Không thể xóa danh mục này vì vẫn còn sản phẩm đang bán. Vui lòng chuyển sản phẩm sang danh mục khác hoặc ngừng bán sản phẩm trước khi xóa.';
        }

        if ($category->childrenCategories()
            ->whereHas('products', fn ($q) => $q->where('status', true))
            ->exists()) {
            return 'Không thể xóa danh mục này vì danh mục con vẫn còn sản phẩm đang bán.';
        }

        $selectedIds = array_map('intval', $selectedIds);
        if ($category->childrenCategories()
            ->when($selectedIds !== [], fn ($query) => $query->whereNotIn('id', $selectedIds))
            ->exists()) {
            return 'Không thể xóa danh mục cha khi vẫn còn danh mục con. Vui lòng xóa hoặc chuyển danh mục con trước.';
        }

        return null;
    }

    private function categoryForceDeleteBlocker(Category $category): ?string
    {
        if ($category->products()->withTrashed()->exists()) {
            return 'Không thể xóa vĩnh viễn danh mục này vì vẫn còn sản phẩm liên kết.';
        }

        if ($category->childrenCategories()
            ->withTrashed()
            ->whereHas('products', fn ($q) => $q->withTrashed())
            ->exists()) {
            return 'Không thể xóa vĩnh viễn danh mục này vì danh mục con vẫn còn sản phẩm liên kết.';
        }

        return null;
    }

    private function uniqueCategorySlug(string $slug, ?int $ignoreId = null): string
    {
        $baseSlug = $slug !== '' ? $slug : 'danh-muc';
        $candidate = $baseSlug;
        $suffix = 1;

        while (Category::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
