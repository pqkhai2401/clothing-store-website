<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Size::withCount('productVariants')->orderBy('name');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $sizes = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.sizes.list', compact('sizes', 'keyword', 'perPage'));
    }

    public function edit(string $id)
    {
        $size = Size::findOrFail($id);

        return view('admin.sizes.edit', compact('size'));
    }

    public function update(Request $request, string $id)
    {
        $size = Size::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('sizes', 'name')->ignore($id)],
        ], [
            'name.required' => 'Tên kích thước không được để trống.',
            'name.max'      => 'Tên kích thước không được quá 50 ký tự.',
            'name.unique'   => 'Tên kích thước này đã tồn tại.',
        ]);

        $size->update(['name' => $request->input('name')]);

        return redirect()->route('admin.sizes.list')
            ->with('success', "Cập nhật kích thước \"{$size->name}\" thành công.");
    }

    public function destroy(string $id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return redirect()->route('admin.sizes.list')->with('success', 'Xóa kích thước thành công');
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Size::onlyTrashed()->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $sizes = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.sizes.trash', compact('sizes', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        Size::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.sizes.trash')->with('success', 'Khôi phục kích thước thành công');
    }

    public function forceDelete(string $id)
    {
        Size::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.sizes.trash')->with('success', 'Xóa vĩnh viễn kích thước thành công');
    }
}
