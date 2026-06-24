<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

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

    public function destroy(string $id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return redirect()->route('admin.colors.list')->with('success', 'Xóa màu sắc thành công');
    }
}
