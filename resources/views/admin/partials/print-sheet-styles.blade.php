<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: "Segoe UI", Roboto, Arial, sans-serif; background: #eef2f7; color: #1f2937; font-size: 12.5px; }

    .pp-wrap { padding: 24px; }

    /* Tờ phiếu (đồng bộ với thiết kế .sid-print của kho) */
    .sid-print-sheet { background: #fff; max-width: 720px; margin: 0 auto; padding: 34px 38px; border-radius: 10px; box-shadow: 0 10px 30px rgba(15, 23, 42, .12); position: relative; }
    .sid-print-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .sid-print-logo { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; background: #059669; color: #fff; font-weight: 800; font-size: 12px; }
    .sid-print-brand { font-weight: 850; font-size: 15px; color: #0f172a; }
    .sid-print-addr { margin-top: 8px; line-height: 1.7; color: #374151; }
    .sid-print-title { font-weight: 850; font-size: 15px; color: #0f172a; letter-spacing: .02em; }
    .sid-print-meta { margin-top: 6px; color: #64748b; }
    .sid-print-meta strong { color: #0f172a; }
    .sid-print-divider { border: none; border-top: 2px solid #059669; margin: 18px 0; }
    .sid-print-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 18px; }
    .sid-print-label { font-weight: 850; color: #059669; letter-spacing: .04em; text-transform: uppercase; font-size: 11px; margin-bottom: 8px; }
    .sid-print-line { margin-bottom: 4px; }
    .sid-print-note { font-style: italic; color: #4b5563; line-height: 1.6; }
    .sid-print-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .sid-print-table thead th { text-align: left; text-transform: uppercase; font-size: 11px; letter-spacing: .03em; color: #374151; padding: 10px 6px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb; }
    .sid-print-table tbody td { padding: 12px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
    .sid-print-table .text-center { text-align: center; }
    .sid-print-table .text-end { text-align: right; }
    .sid-print-product-name { font-weight: 800; color: #0f172a; }
    .sid-print-product-sku { color: #6b7280; margin-top: 2px; font-size: 11.5px; }
    .sid-print-line-total { font-weight: 800; color: #0f172a; }
    .sid-print-totals { margin-top: 18px; margin-left: auto; width: 260px; }
    .sid-print-totals-row { display: flex; justify-content: space-between; padding: 4px 0; color: #4b5563; }
    .sid-print-grand { border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 10px; font-weight: 850; color: #0f172a; font-size: 14px; }
    .sid-print-grand span:last-child { color: #059669; }
    .sid-print-in-words { text-align: right; font-style: italic; color: #6b7280; margin-top: 6px; font-size: 11.5px; }
    .sid-print-signs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; text-align: center; margin-top: 46px; }
    .sid-print-sign-title { font-weight: 850; color: #0f172a; text-transform: uppercase; font-size: 11.5px; }
    .sid-print-sign-sub { color: #6b7280; font-size: 11px; margin-top: 2px; }
    .sid-print-sign-name { margin-top: 48px; font-weight: 700; }
    .sid-print-footer { display: flex; justify-content: space-between; margin-top: 34px; padding-top: 12px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 11px; }

    /* Đóng dấu ĐÃ HỦY */
    .sid-print-watermark { position: absolute; top: 46%; left: 50%; transform: translate(-50%, -50%) rotate(-22deg); font-size: 82px; font-weight: 900; letter-spacing: .1em; color: rgba(220, 38, 38, 0.12); border: 7px solid rgba(220, 38, 38, 0.12); padding: 8px 34px; border-radius: 14px; pointer-events: none; white-space: nowrap; }

    @media print {
        body { background: #fff; }
        .pp-wrap { padding: 0; }
        .sid-print-sheet { max-width: 100%; margin: 0; box-shadow: none; border-radius: 0; padding: 10mm; }
        @page { size: A4; margin: 0; }
    }
</style>
