<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockIssue;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InventoryReportController extends Controller
{
    /**
     * Báo cáo LÃI GỘP theo FIFO.
     *
     * Doanh thu = tổng giá bán (stock_issues.total_sale_amount của phiếu xuất loại "bán").
     * Giá vốn (COGS) = tổng (unit_cost × số lượng) của các bút toán EXPORT trong stock_movements
     *                  → chính là giá vốn theo LÔ đã trừ FIFO tại thời điểm bán.
     * Lãi gộp = Doanh thu − Giá vốn.
     */
    public function profit(Request $request)
    {
        // Khoảng thời gian: mặc định tháng hiện tại.
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        // Các phiếu xuất BÁN đã hoàn tất trong kỳ.
        $issues = StockIssue::query()
            ->where('issue_type', StockIssue::ISSUE_TYPE_SALE)
            ->where('status', StockIssue::STATUS_COMPLETED)
            ->whereBetween('issued_at', [$from, $to])
            ->with(['order:id,order_code'])
            ->orderByDesc('issued_at')
            ->get();

        // COGS theo phiếu: gom bút toán export theo reference_id (= stock_issue_id).
        $issueIds = $issues->pluck('id');
        $cogsByIssue = StockMovement::query()
            ->where('reference_type', 'stock_issue')
            ->whereIn('reference_id', $issueIds)
            ->where('movement_type', 'export')
            ->selectRaw('reference_id, SUM(ABS(quantity) * unit_cost) as cogs, SUM(ABS(quantity)) as qty')
            ->groupBy('reference_id')
            ->get()
            ->keyBy('reference_id');

        $rows = $issues->map(function (StockIssue $issue) use ($cogsByIssue) {
            $agg = $cogsByIssue->get($issue->id);
            $revenue = (float) $issue->total_sale_amount;

            // Giá vốn ưu tiên theo LÔ (FIFO) từ sổ cái. Với phiếu cũ (phát sinh trước khi
            // quản lý theo lô) bút toán export không gắn lô/unit_cost = 0 → fallback về
            // total_cost_amount đã chốt lúc tạo phiếu, để không khai khống lãi.
            $cogs = (float) ($agg->cogs ?? 0);
            if ($cogs <= 0) {
                $cogs = (float) $issue->total_cost_amount;
            }
            $profit = $revenue - $cogs;

            return [
                'issue'   => $issue,
                'qty'     => (int) ($agg->qty ?? $issue->total_quantity),
                'revenue' => $revenue,
                'cogs'    => $cogs,
                'profit'  => $profit,
                'margin'  => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
            ];
        });

        $summary = [
            'orders'  => $rows->count(),
            'qty'     => $rows->sum('qty'),
            'revenue' => $rows->sum('revenue'),
            'cogs'    => $rows->sum('cogs'),
            'profit'  => $rows->sum('profit'),
        ];
        $summary['margin'] = $summary['revenue'] > 0
            ? ($summary['profit'] / $summary['revenue']) * 100
            : 0;

        return view('admin.reports.profit', [
            'rows'    => $rows,
            'summary' => $summary,
            'from'    => $from->format('Y-m-d'),
            'to'      => $to->format('Y-m-d'),
        ]);
    }
}
