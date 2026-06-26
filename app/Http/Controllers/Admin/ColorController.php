<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Color::withCount('productVariants')->orderBy('name');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $colors = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.colors.list', compact('colors', 'keyword', 'perPage'));
    }

    public function edit(string $id)
    {
        $color = Color::findOrFail($id);

        return view('admin.colors.edit', compact('color'));
    }

    public function update(Request $request, string $id)
    {
        $color = Color::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('colors', 'name')->ignore($id)],
        ], [
            'name.required' => 'Tên màu sắc không được để trống.',
            'name.max'      => 'Tên màu sắc không được quá 100 ký tự.',
            'name.unique'   => 'Tên màu sắc này đã tồn tại.',
        ]);

        $color->update(['name' => $request->input('name')]);

        return redirect()->route('admin.colors.list')
            ->with('success', "Cập nhật màu sắc \"{$color->name}\" thành công.");
    }

    public function destroy(string $id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return redirect()->route('admin.colors.list')->with('success', 'Xóa màu sắc thành công');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một màu sắc để xóa.');
        }

        $deleted = Color::whereIn('id', $ids)->delete();

        return back()->with('success', "Đã xóa {$deleted} màu sắc thành công.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Color::onlyTrashed()->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $colors = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.colors.trash', compact('colors', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        Color::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.colors.trash')->with('success', 'Khôi phục màu sắc thành công');
    }

    public function forceDelete(string $id)
    {
        Color::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.colors.trash')->with('success', 'Xóa vĩnh viễn màu sắc thành công');
    }
}
