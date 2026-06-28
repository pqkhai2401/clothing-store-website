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
        $sort = $request->input('sort', 'name');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'asc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Category::with(['parentCategory'])
            ->withCount('products');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        match ($sort) {
            'id' => $query->orderBy('id', $direction),
            'products_count' => $query->orderBy('products_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('parent_id')->orderBy('name', $direction),
        };

        $categories = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.categories.partials.table', compact('categories'))->render(),
            ]);
        }

        return view('admin.categories.index', compact('categories', 'keyword', 'perPage'));
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

        if (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $slug . '-' . $id;
        }

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

        // Kiểm tra chính danh mục có sản phẩm đang bán không
        $selfActiveCount = $category->products()->where('status', true)->count();
        if ($selfActiveCount > 0) {
            return redirect()->route('admin.categories.list')
                ->with('error', 'Không thể xóa danh mục này vì vẫn còn sản phẩm đang bán. Vui lòng chuyển sản phẩm sang danh mục khác hoặc ngừng bán sản phẩm trước khi xóa.');
        }

        // Kiểm tra danh mục con có sản phẩm đang bán không
        $childHasActive = $category->childrenCategories()
            ->whereHas('products', fn ($q) => $q->where('status', true))
            ->exists();
        if ($childHasActive) {
            return redirect()->route('admin.categories.list')
                ->with('error', 'Không thể xóa danh mục này vì danh mục con vẫn còn sản phẩm đang bán.');
        }

        $category->delete();

        return redirect()->route('admin.categories.list')->with('success', 'Xóa danh mục thành công');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một danh mục để xóa.');
        }

        $deleted = Category::whereIn('id', $ids)->delete();

        return back()->with('success', "Đã xóa {$deleted} danh mục thành công.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Category::onlyTrashed()->with('parentCategory')->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $categories = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.categories.trash', compact('categories', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        Category::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.categories.trash')->with('success', 'Khôi phục danh mục thành công');
    }

    public function forceDelete(string $id)
    {
        Category::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.categories.trash')->with('success', 'Xóa vĩnh viễn danh mục thành công');
    }
}
