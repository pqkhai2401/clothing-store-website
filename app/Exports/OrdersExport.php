<?php

namespace App\Exports;

use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private ?Request $request = null)
    {
    }

    public function query()
    {
        if (! $this->request) {
            return Order::query()->with(['user', 'paymentMethod'])->orderByDesc('created_at');
        }

        return app(OrderController::class)
            ->buildFilteredQuery($this->request)
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Mã đơn hàng',
            'Khách hàng',
            'SĐT',
            'Tổng tiền',
            'Phí ship',
            'Trạng thái đơn',
            'Thanh toán',
            'Ngày đặt',
        ];
    }

    public function map($order): array
    {
        $phone = ($order->phone && $order->phone !== '0') ? $order->phone : 'Chưa cập nhật';

        return [
            $order->order_code ?? '—',
            $order->user?->username ?? 'Khách vãng lai',
            $phone,
            (float) $order->total_money,
            (float) $order->shipping_fee,
            OrderController::STATUS_LABELS[$order->status] ?? $order->status,
            OrderController::PAYMENT_STATUS_LABELS[$order->payment_status] ?? $order->payment_status,
            $order->created_at?->format('d/m/Y H:i') ?? '—',
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
