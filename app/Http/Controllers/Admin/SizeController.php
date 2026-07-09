<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $keyword   = trim((string) $request->input('search', $request->input('keyword')));
        $sort      = $request->input('sort', 'sort_weight');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true)
            ? $request->input('direction')
            : 'asc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = $this->sizeIndexQuery();

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $query->orderBy('sort_weight', 'asc');

        match ($sort) {
            'id'                 => $query->orderBy('id', $direction),
            'name'               => $query->orderBy('name', $direction),
            'used_products_count'=> $query->orderBy('used_products_count', $direction),
            'created_at'         => $query->orderBy('created_at', $direction),
            'status'             => $query->orderBy('status', $direction),
            default              => $query->orderBy('name', 'asc'),
        };

        $sizes = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.sizes.partials.table', compact('sizes'))->render(),
            ]);
        }

        return view('admin.sizes.index', compact('sizes', 'keyword', 'perPage'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules(), $this->validationMessages());
        $validated['sort_weight'] = $validated['sort_weight'] ?? 0;

        $size = Size::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'message' => "Thêm kích thước \"{$size->name}\" thành công.",
                'size'    => $size,
            ], 201);
        }

        return redirect()->route('admin.sizes.list')
            ->with('success', "Thêm kích thước \"{$size->name}\" thành công.");
    }

    public function edit(string $id)
    {
        $size = Size::findOrFail($id);

        return view('admin.sizes.edit', compact('size'));
    }

    public function update(Request $request, string $id)
    {
        $size = Size::findOrFail($id);

        $validated = $request->validate($this->validationRules($id), $this->validationMessages());
        $size->update($validated);

        if ($request->ajax()) {
            return response()->json(['message' => "Cập nhật kích thước \"{$size->name}\" thành công.", 'size' => $size]);
        }

        return redirect()->route('admin.sizes.list')
            ->with('success', "Cập nhật kích thước \"{$size->name}\" thành công.");
    }

    public function destroy(string $id)
    {
        $size = Size::findOrFail($id);

        if ($size->productVariants()->exists()) {
            return redirect()->route('admin.sizes.list')
                ->with('error', 'Kích thước này đang được sử dụng, không thể xóa!');
        }

        $size->delete();

        return redirect()->route('admin.sizes.list')->with('success', 'Xóa kích thước thành công');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một kích thước để xóa.');
        }

        if (Size::whereIn('id', $ids)->whereHas('productVariants')->exists()) {
            return back()->with('error', 'Kích thước này đang được sử dụng, không thể xóa!');
        }

        $deleted = Size::whereIn('id', $ids)->delete();

        return back()->with('success', "Đã xóa {$deleted} kích thước thành công.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Size::onlyTrashed()
            ->withCount('productVariants')
            ->orderBy('deleted_at', 'desc');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $sizes = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.sizes.partials.trash-table', compact('sizes'))->render(),
            ]);
        }

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

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một kích thước.');
        }

        $restored = Size::onlyTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', "Đã khôi phục {$restored} kích thước thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một kích thước.');
        }

        $count = Size::onlyTrashed()->whereIn('id', $ids)->count();
        Size::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return back()->with('success', "Đã xóa vĩnh viễn {$count} kích thước.");
    }

    private function sizeIndexQuery(): Builder
    {
        return Size::query()
            ->withCount([
                'productVariants as used_products_count' => function (Builder $query) {
                    $query->select(DB::raw('COUNT(DISTINCT product_id)'));
                },
            ]);
    }

    private function validationRules(?string $ignoreId = null): array
    {
        return [
            'name'        => [
                'required', 'string', 'max:50',
                Rule::unique('sizes', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($ignoreId),
            ],
            'sort_weight' => ['nullable', 'integer', 'min:0'],
            'status'      => ['required', Rule::in([0, 1, '0', '1'])],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'name.required'    => 'Tên kích thước không được để trống.',
            'name.max'         => 'Tên kích thước không được quá 50 ký tự.',
            'name.unique'      => 'Tên kích thước này đã tồn tại.',
            'sort_weight.integer' => 'Thứ tự hiển thị phải là số nguyên.',
            'sort_weight.min'     => 'Thứ tự hiển thị không được nhỏ hơn 0.',
            'status.required'  => 'Vui lòng chọn trạng thái.',
        ];
    }
}