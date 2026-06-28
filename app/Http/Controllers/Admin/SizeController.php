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
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $sort = $request->input('sort', 'name');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'asc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Size::withCount('productVariants');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        match ($sort) {
            'id' => $query->orderBy('id', $direction),
            'variants_count' => $query->orderBy('product_variants_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('name', $direction),
        };

        $sizes = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.sizes.partials.table', compact('sizes'))->render(),
            ]);
        }

        return view('admin.sizes.index', compact('sizes', 'keyword', 'perPage'));
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

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một kích thước để xóa.');
        }

        $deleted = Size::whereIn('id', $ids)->delete();

        return back()->with('success', "Đã xóa {$deleted} kích thước thành công.");
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
