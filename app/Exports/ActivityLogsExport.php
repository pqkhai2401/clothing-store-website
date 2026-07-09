<?php

namespace App\Exports;

use App\Http\Controllers\Admin\ActivityLogController;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivityLogsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private ?Request $request = null)
    {
    }

    public function query()
    {
        if (! $this->request) {
            return ActivityLog::query()->with(['user', 'auditable'])->orderByDesc('created_at');
        }

        // Ưu tiên 1: nếu Admin chọn checkbox cụ thể, chỉ xuất đúng các ID đó
        $ids = array_filter((array) $this->request->input('ids', []), 'is_numeric');
        if (! empty($ids)) {
            return ActivityLog::query()
                ->with(['user', 'auditable'])
                ->whereIn('id', $ids)
                ->orderByDesc('created_at');
        }

        // Ưu tiên 2: xuất theo bộ lọc động từ giao diện
        return app(ActivityLogController::class)
            ->buildFilteredQuery($this->request)
            ->with(['user', 'auditable'])
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Thời gian',
            'Người thực hiện',
            'Email',
            'Hành động',
            'Đối tượng',
            'Chi tiết đối tượng',
            'Địa chỉ IP',
            'Tóm tắt thay đổi',
        ];
    }

    public function map($log): array
    {
        $user = $log->user;

        return [
            $log->created_at?->format('d/m/Y H:i:s') ?? '—',
            $log->causer_name,
            $user?->email ?? '—',
            $log->event_label,
            $log->subject_label,
            $log->subject_description ?: '—',
            $log->ip_address ?? '—',
            $this->summarizeChanges($log),
        ];
    }

    /**
     * Gộp các thay đổi thành chuỗi "trường: cũ → mới".
     */
    private function summarizeChanges($log): string
    {
        $new = (array) $log->new_values;
        $old = (array) $log->old_values;

        if (in_array($log->event, ['login', 'logout'], true)) {
            return $new['message'] ?? '';
        }

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $lines = [];

        foreach ($keys as $key) {
            $oldVal = $this->stringify($old[$key] ?? null);
            $newVal = $this->stringify($new[$key] ?? null);

            if ($log->event === 'created') {
                $lines[] = "{$key}: {$newVal}";
            } elseif ($log->event === 'deleted') {
                $lines[] = "{$key}: {$oldVal}";
            } else {
                $lines[] = "{$key}: {$oldVal} → {$newVal}";
            }
        }

        return implode('; ', $lines);
    }

    private function stringify($value): string
    {
        if (is_null($value)) {
            return '∅';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
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
