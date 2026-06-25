<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Category::with(['parentCategory'])
            ->withCount('products')
            ->orderBy('parent_id')
            ->orderBy('name');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $categories = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.categories.list', compact('categories', 'keyword', 'perPage'));
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
