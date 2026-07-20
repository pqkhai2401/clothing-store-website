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
        $typeFilter   = $request->input('type', '');
        $dateFrom     = $request->input('date_from');
        $dateTo       = $request->input('date_to');

        $query = Voucher::query();

        if ($search !== '') {
            $query->where('code', 'like', "%{$search}%");
        }

        if (in_array($statusFilter, ['0', '1'], true)) {
            $query->where('status', (bool) (int) $statusFilter);
        }

        if (in_array($typeFilter, ['percentage', 'fixed'], true)) {
            $query->where('type', $typeFilter);
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
        $typeFilter   = $request->input('type', '');
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
            'typeFilter'   => $typeFilter,
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

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', ''));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Voucher::onlyTrashed()->orderByDesc('deleted_at');

        if ($keyword !== '') {
            $query->where('code', 'like', "%{$keyword}%");
        }

        $vouchers = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.vouchers.partials.trash-table', compact('vouchers'))->render(),
            ]);
        }

        return view('admin.vouchers.trash', compact('vouchers', 'keyword', 'perPage'));
    }

    private function validationRules(?string $ignoreId = null): array
    {
        $rules = [
            'code'                => [
                'required', 'string', 'max:50',
                Rule::unique('vouchers', 'code')->ignore($ignoreId),
            ],
            'type'                => ['required', Rule::in(['percentage', 'fixed'])],
            'min_order_amount'    => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'quantity'            => ['required', 'integer', 'min:1'],
            'start_date'          => ['required', 'date'],
            'end_date'            => ['required', 'date', 'after:start_date'],
            'status'              => ['nullable', 'boolean'],
            'description'         => ['nullable', 'string', 'max:1000'],
        ];

        if (request()->input('type') === 'percentage') {
            $rules['value'] = ['required', 'numeric', 'min:0', 'max:100'];
        } else {
            $rules['value'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }

    private function validationMessages(): array
    {
        return [
            'code.required'       => 'Vui lòng nhập mã voucher.',
            'code.unique'         => 'Mã voucher này đã tồn tại.',
            'type.required'       => 'Vui lòng chọn kiểu giảm giá.',
            'value.required'      => 'Vui lòng nhập giá trị giảm.',
            'value.min'           => 'Giá trị giảm không được nhỏ hơn 0.',
            'value.max'           => 'Phần trăm giảm không được vượt quá 100%.',
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

        if ($request->input('type') === 'fixed') {
            $request->merge(['max_discount_amount' => null]);
        }

        $validated = $request->validate($this->validationRules(), $this->validationMessages());
        $validated['status'] = $request->boolean('status');

        $voucher = Voucher::create($validated);

        return redirect()->route('admin.vouchers.list')
            ->with('success', "Thêm voucher \"{$voucher->code}\" thành công.");
    }

    public function edit(Request $request, string $id)
    {
        $voucher = Voucher::findOrFail($id);

        if ($request->ajax()) {
            return view('admin.vouchers.edit', compact('voucher'));
        }

        return redirect()->route('admin.vouchers.list', ['edit' => $id]);
    }

    public function update(Request $request, string $id)
    {
        $voucher = Voucher::findOrFail($id);

        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);

        if ($request->input('type') === 'fixed') {
            $request->merge(['max_discount_amount' => null]);
        }

        $rules = $this->validationRules($id);
        $rules['quantity'][] = function ($attribute, $value, $fail) use ($voucher) {
            if ((int) $value < $voucher->used_count) {
                $fail("Số lượng phát hành không được nhỏ hơn số lượt đã dùng ({$voucher->used_count}).");
            }
        };

        $validated = $request->validate($rules, $this->validationMessages());
        $validated['status'] = $request->boolean('status');

        // Cảnh báo (không chặn) khi sửa các field ảnh hưởng đến ưu đãi của voucher đã có người
        // dùng: đơn cũ không bị đổi số liệu (discount_amount đã chốt lúc đặt hàng), nhưng khách
        // dùng mã từ giờ trở đi sẽ nhận điều khoản mới — admin cần biết để cân nhắc, không nên
        // âm thầm đổi luật chơi giữa chừng một chiến dịch đang chạy.
        $numericFields = ['value', 'min_order_amount', 'max_discount_amount'];
        $dateFields = ['start_date', 'end_date'];
        $changedSensitiveFields = [];
        if ($voucher->used_count > 0) {
            if (array_key_exists('type', $validated) && $voucher->type !== $validated['type']) {
                $changedSensitiveFields[] = 'type';
            }

            foreach ($numericFields as $field) {
                if (!array_key_exists($field, $validated)) {
                    continue;
                }
                // Cast decimal:2 trả về string "200000.00" — so bằng (float) để tránh báo
                // "thay đổi" giả khi client chỉ gửi lại đúng giá trị cũ ở định dạng khác.
                $oldVal = $voucher->{$field} === null ? null : (float) $voucher->{$field};
                $newVal = $validated[$field] === null || $validated[$field] === '' ? null : (float) $validated[$field];
                if ($oldVal !== $newVal) {
                    $changedSensitiveFields[] = $field;
                }
            }

            foreach ($dateFields as $field) {
                if (!array_key_exists($field, $validated)) {
                    continue;
                }
                $oldVal = $voucher->{$field}?->timestamp;
                $newVal = $validated[$field] ? \Illuminate\Support\Carbon::parse($validated[$field])->timestamp : null;
                if ($oldVal !== $newVal) {
                    $changedSensitiveFields[] = $field;
                }
            }
        }

        $voucher->update($validated);

        if (!empty($changedSensitiveFields)) {
            session()->flash('warning', sprintf(
                'Lưu ý: voucher "%s" đã được sử dụng %d lượt — bạn vừa thay đổi %s. Các đơn hàng đã đặt trước đó không bị ảnh hưởng, nhưng khách dùng mã từ giờ trở đi sẽ áp dụng theo điều khoản mới.',
                $voucher->code,
                $voucher->used_count,
                implode(', ', $changedSensitiveFields)
            ));
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Cap nhat voucher \"{$voucher->code}\" thanh cong.",
                'warning' => $changedSensitiveFields ? session('warning') : null,
            ]);
        }

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
    public function restore(string $id)
    {
        $voucher = Voucher::onlyTrashed()->findOrFail($id);
        $voucher->restore();

        return back()->with('success', "Da khoi phuc voucher \"{$voucher->code}\" thanh cong.");
    }

    public function forceDelete(string $id)
    {
        $voucher = Voucher::onlyTrashed()->findOrFail($id);
        $code = $voucher->code;
        $voucher->forceDelete();

        return back()->with('success', "Da xoa vinh vien voucher \"{$code}\".");
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui long chon it nhat mot voucher de khoi phuc.');
        }

        $restored = Voucher::onlyTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', "Da khoi phuc {$restored} voucher thanh cong.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui long chon it nhat mot voucher de xoa vinh vien.');
        }

        $count = Voucher::onlyTrashed()->whereIn('id', $ids)->count();
        Voucher::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return back()->with('success', "Da xoa vinh vien {$count} voucher.");
    }
}
