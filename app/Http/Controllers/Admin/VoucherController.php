<?php

namespace App\Http\Controllers\Admin;

use App\Exports\VouchersExport;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class VoucherController extends Controller
{
    public const TYPE_LABELS = [
        'percentage' => 'Theo %',
        'fixed'      => 'Tiền mặt',
    ];

    /**
     * Xây dựng query voucher theo các bộ lọc dùng chung cho danh sách (index) và xuất Excel (export).
     */
    public function buildFilteredQuery(Request $request): Builder
    {
        $search       = trim((string) $request->input('search', $request->input('keyword')));
        $statusFilter = $request->input('status', '');
        $dateFrom     = $request->input('date_from');
        $dateTo       = $request->input('date_to');

        $query = Voucher::query();

        if ($search !== '') {
            $query->where('code', 'like', "%{$search}%");
        }

        if (in_array($statusFilter, ['0', '1'], true)) {
            $query->where('status', (bool) (int) $statusFilter);
        }

        if ($dateFrom) {
            $query->whereDate('end_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('start_date', '<=', $dateTo);
        }

        return $query;
    }

    private function buildStatsData(): array
    {
        $now = now();

        return [
            'totalVouchers'    => Voucher::count(),
            'activeVouchers'   => Voucher::where('status', true)
                ->where('end_date', '>=', $now)
                ->whereColumn('used_count', '<', 'quantity')
                ->count(),
            'expiredVouchers'  => Voucher::where('end_date', '<', $now)->count(),
            'depletedVouchers' => Voucher::whereColumn('used_count', '>=', 'quantity')->count(),
        ];
    }

    public function index(Request $request)
    {
        $keyword      = trim((string) $request->input('search', $request->input('keyword')));
        $statusFilter = $request->input('status', '');
        $dateFrom     = $request->input('date_from', '');
        $dateTo       = $request->input('date_to', '');
        $sort         = $request->input('sort', 'created_at');
        $direction    = in_array($request->input('direction'), ['asc', 'desc'], true)
            ? $request->input('direction') : 'desc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = $this->buildFilteredQuery($request);

        match ($sort) {
            'value'       => $query->orderBy('value', $direction),
            'quantity'    => $query->orderBy('quantity', $direction),
            'used_count'  => $query->orderBy('used_count', $direction),
            'start_date'  => $query->orderBy('start_date', $direction),
            'end_date'    => $query->orderBy('end_date', $direction),
            default       => $query->orderBy('created_at', $direction),
        };

        $vouchers = $query->paginate($perPage)->withQueryString();

        $tableData = [
            'vouchers'   => $vouchers,
            'typeLabels' => self::TYPE_LABELS,
            'sort'       => $sort,
            'direction'  => $direction,
        ];

        $statsData = $this->buildStatsData();

        if ($request->ajax()) {
            return response()->json([
                'html'  => view('admin.vouchers.partials.table', $tableData)->render(),
                'stats' => view('admin.vouchers.partials.stats', $statsData)->render(),
            ]);
        }

        return view('admin.vouchers.index', array_merge($tableData, $statsData, [
            'keyword'      => $keyword,
            'statusFilter' => $statusFilter,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'perPage'      => $perPage,
        ]));
    }

    public function export(Request $request)
    {
        return Excel::download(new VouchersExport($request), 'voucher-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function create()
    {
        return view('admin.vouchers.create');
    }

    private function validationRules(?string $ignoreId = null): array
    {
        return [
            'code'                => [
                'required', 'string', 'max:50',
                Rule::unique('vouchers', 'code')->ignore($ignoreId),
            ],
            'type'                => ['required', Rule::in(['percentage', 'fixed'])],
            'value'               => ['required', 'numeric', 'min:0'],
            'min_order_amount'    => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'quantity'            => ['required', 'integer', 'min:1'],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['required', 'date', 'after:start_date'],
            'status'              => ['nullable', 'boolean'],
            'description'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'code.required'       => 'Vui lòng nhập mã voucher.',
            'code.unique'         => 'Mã voucher này đã tồn tại.',
            'type.required'       => 'Vui lòng chọn kiểu giảm giá.',
            'value.required'      => 'Vui lòng nhập giá trị giảm.',
            'quantity.required'   => 'Vui lòng nhập số lượng phát hành.',
            'quantity.min'        => 'Số lượng phát hành phải lớn hơn 0.',
            'start_date.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'end_date.required'   => 'Vui lòng chọn thời gian kết thúc.',
            'end_date.after'      => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ];
    }

    public function store(Request $request)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        $validated = $request->validate($this->validationRules(), $this->validationMessages());
        $validated['status'] = $request->boolean('status');

        $voucher = Voucher::create($validated);

        return redirect()->route('admin.vouchers.list')
            ->with('success', "Thêm voucher \"{$voucher->code}\" thành công.");
    }

    public function edit(string $id)
    {
        $voucher = Voucher::findOrFail($id);

        return view('admin.vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, string $id)
    {
        $voucher = Voucher::findOrFail($id);

        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        $rules = $this->validationRules($id);
        $rules['quantity'][] = function ($attribute, $value, $fail) use ($voucher) {
            if ((int) $value < $voucher->used_count) {
                $fail("Số lượng phát hành không được nhỏ hơn số lượt đã dùng ({$voucher->used_count}).");
            }
        };

        $validated = $request->validate($rules, $this->validationMessages());
        $validated['status'] = $request->boolean('status');

        $voucher->update($validated);

        return redirect()->route('admin.vouchers.list')
            ->with('success', "Cập nhật voucher \"{$voucher->code}\" thành công.");
    }

    public function destroy(string $id)
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->delete();

        return back()->with('success', "Đã xóa voucher \"{$voucher->code}\" thành công.");
    }

    public function toggleStatus(Request $request, string $id)
    {
        $voucher = Voucher::findOrFail($id);
        $newStatus = ! $voucher->status;
        $voucher->update(['status' => $newStatus]);

        $msg = $newStatus
            ? "Voucher \"{$voucher->code}\" đã được kích hoạt."
            : "Voucher \"{$voucher->code}\" đã bị khóa.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'status' => $newStatus]);
        }

        return back()->with('success', $msg);
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một voucher để xóa.');
        }

        $deleted = Voucher::whereIn('id', $ids)->delete();

        return back()->with('success', "Đã xóa {$deleted} voucher thành công.");
    }
}
