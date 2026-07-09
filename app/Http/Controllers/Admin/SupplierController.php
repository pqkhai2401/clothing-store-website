<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $sort = $request->input('sort', 'id');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'asc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Supplier::withCount('goodsReceipts');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        match ($sort) {
            'id' => $query->orderBy('id', $direction),
            'receipts_count' => $query->orderBy('goods_receipts_count', $direction),
            'created_at' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('name', $direction),
        };

        $suppliers = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.suppliers.partials.table', compact('suppliers'))->render(),
            ]);
        }

        return view('admin.suppliers.index', compact('suppliers', 'keyword', 'perPage'));
    }

    private function validationRules(?string $ignoreId = null): array
    {
        return [
            'name'    => ['required', 'string', 'max:150', Rule::unique('suppliers', 'name')->ignore($ignoreId)->whereNull('deleted_at')],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'note'    => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'name.required' => 'Tên nhà cung cấp không được để trống.',
            'name.max'      => 'Tên nhà cung cấp không được quá 150 ký tự.',
            'name.unique'   => 'Tên nhà cung cấp này đã tồn tại.',
            'email.email'   => 'Email không hợp lệ.',
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules(), $this->validationMessages());
        $validated['status'] = true;

        $supplier = Supplier::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'message'  => "Thêm nhà cung cấp \"{$supplier->name}\" thành công.",
                'supplier' => $supplier,
            ], 201);
        }

        return redirect()->route('admin.suppliers.list')
            ->with('success', "Thêm nhà cung cấp \"{$supplier->name}\" thành công.");
    }

    public function edit(string $id)
    {
        $supplier = Supplier::findOrFail($id);

        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate($this->validationRules($id), $this->validationMessages());
        $supplier->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'message'  => "Cập nhật nhà cung cấp \"{$supplier->name}\" thành công.",
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('admin.suppliers.list')
            ->with('success', "Cập nhật nhà cung cấp \"{$supplier->name}\" thành công.");
    }

    public function toggleStatus(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $newStatus = !$supplier->status;
        $supplier->update(['status' => $newStatus]);

        $msg = $newStatus
            ? "Nhà cung cấp \"{$supplier->name}\" đã được kích hoạt."
            : "Nhà cung cấp \"{$supplier->name}\" đã bị vô hiệu hóa.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'status' => $newStatus]);
        }

        return back()->with('success', $msg);
    }

    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->goodsReceipts()->exists()) {
            return back()->with('error', 'Không thể xóa nhà cung cấp đang có phiếu nhập kho.');
        }

        $supplier->delete();

        return redirect()->route('admin.suppliers.list')->with('success', 'Xóa nhà cung cấp thành công');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một nhà cung cấp để xóa.');
        }

        $blocked = Supplier::whereIn('id', $ids)->whereHas('goodsReceipts')->count();
        $deleted = Supplier::whereIn('id', $ids)->whereDoesntHave('goodsReceipts')->delete();

        $message = "Đã xóa {$deleted} nhà cung cấp thành công.";
        if ($blocked > 0) {
            $message .= " {$blocked} nhà cung cấp không thể xóa do đang có phiếu nhập kho.";
        }

        return back()->with($deleted > 0 ? 'success' : 'error', $message);
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Supplier::onlyTrashed()->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $suppliers = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.suppliers.partials.trash-table', compact('suppliers'))->render(),
            ]);
        }

        return view('admin.suppliers.trash', compact('suppliers', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        Supplier::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.suppliers.trash')->with('success', 'Khôi phục nhà cung cấp thành công');
    }

    public function forceDelete(string $id)
    {
        Supplier::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.suppliers.trash')->with('success', 'Xóa vĩnh viễn nhà cung cấp thành công');
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một nhà cung cấp.');
        }
        $restored = Supplier::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', "Đã khôi phục {$restored} nhà cung cấp thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một nhà cung cấp.');
        }
        $count = Supplier::onlyTrashed()->whereIn('id', $ids)->count();
        Supplier::onlyTrashed()->whereIn('id', $ids)->forceDelete();
        return back()->with('success', "Đã xóa vĩnh viễn {$count} nhà cung cấp.");
    }
}
