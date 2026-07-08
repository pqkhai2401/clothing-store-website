<?php

namespace App\Exports;

use App\Http\Controllers\Admin\VoucherController;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VouchersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private ?Request $request = null)
    {
    }

    public function query()
    {
        if (! $this->request) {
            return Voucher::query()->orderByDesc('created_at');
        }

        // Ưu tiên 1: Nếu Admin chọn checkbox cụ thể, chỉ xuất đúng các ID đó
        $ids = array_filter((array) $this->request->input('ids', []), 'is_numeric');
        if (! empty($ids)) {
            return Voucher::query()
                ->whereIn('id', $ids)
                ->orderByDesc('created_at');
        }

        // Ưu tiên 2: Chạy theo bộ lọc động từ giao diện (status, type, date_from, date_to, search)
        return app(VoucherController::class)
            ->buildFilteredQuery($this->request)
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Mã voucher',
            'Kiểu giảm',
            'Giá trị giảm',
            'Đơn tối thiểu',
            'Giảm tối đa',
            'Tỷ lệ sử dụng (Lượt dùng / Tổng)',
            'Ngày bắt đầu',
            'Ngày kết thúc',
            'Trạng thái',
        ];
    }

    public function map($voucher): array
    {
        // Định dạng giá trị giảm
        $discountValue = $voucher->type === 'percentage'
            ? rtrim(rtrim(number_format((float) $voucher->value, 2, ',', '.'), '0'), ',') . '%'
            : number_format((float) $voucher->value, 0, ',', '.') . 'đ';

        // Định dạng Giảm tối đa:
        // - Nếu giảm theo tiền cố định: không cần giảm tối đa → "Không áp dụng"
        // - Nếu giảm theo %: null hoặc 0 → "Không giới hạn", có giá trị → hiển thị số
        if ($voucher->type === 'fixed') {
            $maxDiscount = 'Không áp dụng';
        } elseif ($voucher->max_discount_amount === null || (float) $voucher->max_discount_amount == 0) {
            $maxDiscount = 'Không giới hạn';
        } else {
            $maxDiscount = number_format((float) $voucher->max_discount_amount, 0, ',', '.') . 'đ';
        }

        // Tỷ lệ sử dụng: "used_count / quantity (X%)"
        $usageRate = $voucher->quantity > 0
            ? round(($voucher->used_count / $voucher->quantity) * 100)
            : 0;
        $usageDisplay = $voucher->used_count . ' / ' . $voucher->quantity . ' (' . $usageRate . '%)';

        return [
            $voucher->code,
            VoucherController::TYPE_LABELS[$voucher->type] ?? $voucher->type,
            $discountValue,
            $voucher->min_order_amount > 0
                ? number_format((float) $voucher->min_order_amount, 0, ',', '.') . 'đ'
                : 'Không yêu cầu',
            $maxDiscount,
            $usageDisplay,
            $voucher->start_date?->format('d/m/Y H:i') ?? '—',
            $voucher->end_date?->format('d/m/Y H:i') ?? '—',
            $voucher->status ? 'Hoạt động' : 'Khóa',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow    = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range         = "A1:{$highestColumn}{$highestRow}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'D1D5DB'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => 'FFFFFF'],
            ],
        ]);

        // Header row style
        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => 'F0FDF4'],
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['rgb' => '16A34A'],
                ],
            ],
        ]);

        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => '14532D']]],
        ];
    }
}
