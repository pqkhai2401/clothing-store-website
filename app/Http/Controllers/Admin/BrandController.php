<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

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

        return view('admin.brands.list', compact('brands', 'keyword', 'perPage'));
    }

    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('admin.brands.list')->with('success', 'Xóa thương hiệu thành công');
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
