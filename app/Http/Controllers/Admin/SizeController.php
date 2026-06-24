<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

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

    public function destroy(string $id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return redirect()->route('admin.sizes.list')->with('success', 'Xóa kích thước thành công');
    }
}
