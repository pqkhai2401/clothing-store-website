<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RevenueExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Ngày ghi nhận',
            'Mã đơn',
            'Khách hàng',
            'Doanh thu thuần',
            'Giá vốn (FIFO)',
            'Lợi nhuận',
            'Biên LN %',
            'Phương thức TT',
            'Nhân viên xử lý',
        ];
    }

    public function map($row): array
    {
        return [
            $row['date']?->format('d/m/Y H:i') ?? '—',
            $row['order']->order_code ?? ('#' . $row['order']->id),
            $row['order']->user?->username ?? 'Khách vãng lai',
            (float) $row['revenue'],
            (float) $row['cogs'],
            (float) $row['profit'],
            round((float) $row['margin'], 1),
            $row['order']->paymentMethod?->name ?? '—',
            $row['staff'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
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
