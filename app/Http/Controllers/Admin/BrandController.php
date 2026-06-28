<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Brand::withCount('products')->orderBy('name');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $brands = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.brands.index', compact('brands', 'keyword', 'perPage'));
    }

    public function edit(string $id)
    {
        $brand = Brand::findOrFail($id);

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($id)],
        ], [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'name.max'      => 'Tên thương hiệu không được quá 255 ký tự.',
            'name.unique'   => 'Tên thương hiệu này đã tồn tại.',
        ]);

        $brand->update(['name' => $request->input('name')]);

        return redirect()->route('admin.brands.list')
            ->with('success', "Cập nhật thương hiệu \"{$brand->name}\" thành công.");
    }

    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('admin.brands.list')->with('success', 'Xóa thương hiệu thành công');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một thương hiệu để xóa.');
        }

        $deleted = Brand::whereIn('id', $ids)->delete();

        return back()->with('success', "Đã xóa {$deleted} thương hiệu thành công.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Brand::onlyTrashed()->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $brands = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.brands.trash', compact('brands', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        Brand::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.brands.trash')->with('success', 'Khôi phục thương hiệu thành công');
    }

    public function forceDelete(string $id)
    {
        Brand::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.brands.trash')->with('success', 'Xóa vĩnh viễn thương hiệu thành công');
    }
}
