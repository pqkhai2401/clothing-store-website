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
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $status = $request->input('status');
        $sort = $request->input('sort', 'id');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'asc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Color::withCount('productVariants');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        if (in_array($status, ['0', '1'], true)) {
            $query->where('status', (bool) (int) $status);
        }

        match ($sort) {
            'id' => $query->orderBy('id', $direction),
            'variants_count' => $query->orderBy('product_variants_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('name', $direction),
        };

        $colors = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.colors.partials.table', compact('colors'))->render(),
            ]);
        }

        return view('admin.colors.index', compact('colors', 'keyword', 'perPage'));
    }

    public function toggleStatus(Request $request, string $id)
    {
        $color = Color::findOrFail($id);
        $newStatus = !$color->status;
        $color->update(['status' => $newStatus]);

        $msg = $newStatus
            ? "Màu sắc \"{$color->name}\" đã được hiển thị."
            : "Màu sắc \"{$color->name}\" đã được ẩn khỏi website.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'status' => $newStatus]);
        }

        return back()->with('success', $msg);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100', Rule::unique('colors', 'name')],
            'hex_code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'name.required' => 'Tên màu sắc không được để trống.',
            'name.max'      => 'Tên màu sắc không được quá 100 ký tự.',
            'name.unique'   => 'Tên màu sắc này đã tồn tại.',
            'hex_code.regex' => 'Mã màu không hợp lệ. Vui lòng nhập đúng định dạng #RRGGBB (ví dụ: #FF5733).',
        ]);

        if (isset($validated['hex_code'])) {
            $validated['hex_code'] = strtoupper($validated['hex_code']);
        }

        $color = Color::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'message' => "Thêm màu sắc \"{$color->name}\" thành công.",
                'color'   => $color,
            ], 201);
        }

        return redirect()->route('admin.colors.list')
            ->with('success', "Thêm màu sắc \"{$color->name}\" thành công.");
    }

    public function edit(string $id)
    {
        $color = Color::findOrFail($id);

        return view('admin.colors.edit', compact('color'));
    }

    public function update(Request $request, string $id)
    {
        $color = Color::findOrFail($id);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:100', Rule::unique('colors', 'name')->ignore($id)],
            'hex_code' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'name.required'  => 'Tên màu sắc không được để trống.',
            'name.max'       => 'Tên màu sắc không được quá 100 ký tự.',
            'name.unique'    => 'Tên màu sắc này đã tồn tại.',
            'hex_code.regex' => 'Mã màu không hợp lệ. Vui lòng nhập đúng định dạng #RRGGBB (ví dụ: #FF5733).',
        ]);

        if (isset($validated['hex_code'])) {
            $validated['hex_code'] = strtoupper($validated['hex_code']);
        }

        $color->update(['name' => $validated['name'], 'hex_code' => $validated['hex_code'] ?? $color->hex_code]);

        if ($request->ajax()) {
            return response()->json(['message' => "Cập nhật màu sắc \"{$color->name}\" thành công.", 'color' => $color]);
        }

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
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Color::onlyTrashed()
            ->withCount('productVariants')
            ->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $colors = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.colors.partials.trash-table', compact('colors'))->render(),
            ]);
        }

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

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một màu sắc.');
        }
        $restored = Color::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', "Đã khôi phục {$restored} màu sắc thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một màu sắc.');
        }
        $count = Color::onlyTrashed()->whereIn('id', $ids)->count();
        Color::onlyTrashed()->whereIn('id', $ids)->forceDelete();
        return back()->with('success', "Đã xóa vĩnh viễn {$count} màu sắc.");
    }
}