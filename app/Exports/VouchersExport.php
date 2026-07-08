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
            'Đã dùng / Tổng phát hành',
            'Bắt đầu',
            'Kết thúc',
            'Trạng thái',
        ];
    }

    public function map($voucher): array
    {
        return [
            $voucher->code,
            VoucherController::TYPE_LABELS[$voucher->type] ?? $voucher->type,
            $voucher->type === 'percentage'
                ? ((float) $voucher->value . '%')
                : number_format((float) $voucher->value, 0, ',', '.') . 'đ',
            number_format((float) $voucher->min_order_amount, 0, ',', '.') . 'đ',
            $voucher->max_discount_amount !== null
                ? number_format((float) $voucher->max_discount_amount, 0, ',', '.') . 'đ'
                : '—',
            $voucher->used_count . ' / ' . $voucher->quantity,
            $voucher->start_date?->format('d/m/Y H:i') ?? '—',
            $voucher->end_date?->format('d/m/Y H:i') ?? '—',
            $voucher->status ? 'Hoạt động' : 'Khóa',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = "A1:{$highestColumn}{$highestRow}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ]);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
