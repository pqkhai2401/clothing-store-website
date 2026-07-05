<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\StockIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockIssueController extends Controller
{
    public function create()
    {
        // Trang tạo phiếu xuất kho hiện được thực hiện qua modal ngay tại danh sách
        // (xem admin.stock-issues.partials.create-modal), không còn dùng trang riêng.
        return redirect()->route('admin.goods-receipts.list', ['tab' => 'outbound']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason_type' => ['required', Rule::in(array_keys(StockIssue::REASON_TYPE_LABELS))],
            'note' => ['nullable', 'string', 'max:2000'],
            'submit_action' => ['required', Rule::in(['draft', 'issue'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ], [
            'reason_type.required' => 'Vui lòng chọn loại lý do xuất kho.',
            'reason_type.in' => 'Loại lý do xuất kho không hợp lệ.',
            'items.required' => 'Vui lòng chọn ít nhất một sản phẩm để xuất kho.',
            'items.min' => 'Vui lòng chọn ít nhất một sản phẩm để xuất kho.',
        ]);

        // Chỉ "Xuất trả đối tác - Nhà cung cấp" mới được phép tự đặt giá xuất.
        // Các loại lý do khác: BỎ QUA giá client gửi lên, luôn lấy giá vốn thật từ DB
        // để chặn hành vi sửa giá qua devtools/inspect-element.
        $allowsPriceEdit = in_array($validated['reason_type'], StockIssue::REASON_TYPES_ALLOWING_PRICE_EDIT, true);

        $normalizedItems = collect($validated['items'])->map(function (array $item) use ($allowsPriceEdit) {
            $variant = ProductVariant::findOrFail($item['product_variant_id']);

            return [
                'product_variant_id' => $variant->id,
                'quantity' => (int) $item['quantity'],
                'unit_price' => $allowsPriceEdit ? (float) $item['unit_price'] : (float) $variant->cost_price,
            ];
        })->all();

        if ($validated['submit_action'] === 'issue') {
            $this->assertSufficientStock($normalizedItems);
        }

        $stockIssue = DB::transaction(function () use ($validated, $normalizedItems) {
            $totalAmount = collect($normalizedItems)
                ->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

            $stockIssue = StockIssue::create([
                'code' => $this->generateCode(),
                'reason' => StockIssue::REASON_TYPE_LABELS[$validated['reason_type']],
                'reason_type' => $validated['reason_type'],
                'note' => $validated['note'] ?? null,
                'status' => StockIssue::STATUS_DRAFT,
                'total_amount' => $totalAmount,
                'created_by' => Auth::id(),
            ]);

            foreach ($normalizedItems as $item) {
                $stockIssue->items()->create($item);
            }

            if ($validated['submit_action'] === 'issue') {
                $this->issueStock($stockIssue);
            }

            return $stockIssue;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Tạo phiếu xuất kho \"{$stockIssue->code}\" thành công.",
                'code' => $stockIssue->code,
                'show_url' => route('admin.stock-issues.show', $stockIssue->id),
                'table_url' => route('admin.goods-receipts.list', ['tab' => 'outbound']),
            ]);
        }

        return redirect()->route('admin.stock-issues.show', $stockIssue->id)
            ->with('success', "Tạo phiếu xuất kho \"{$stockIssue->code}\" thành công.");
    }

    public function show(Request $request, string $id)
    {
        $stockIssue = StockIssue::with([
            'creator',
            'items.productVariant.product:id,name,thumbnail',
            'items.productVariant.color:id,name,hex_code',
            'items.productVariant.size:id,name',
        ])->findOrFail($id);

        // Panel trượt tại danh sách tải nội dung chi tiết qua AJAX (không chuyển trang).
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.stock-issues.partials.show-content', compact('stockIssue'))->render(),
            ]);
        }

        return view('admin.stock-issues.show', compact('stockIssue'));
    }

    public function issue(string $id)
    {
        $stockIssue = StockIssue::with('items')->findOrFail($id);

        if (! $stockIssue->isDraft()) {
            return back()->with('error', 'Phiếu xuất kho này đã được xuất trước đó.');
        }

        try {
            $this->assertSufficientStock($stockIssue->items->map(fn ($item) => [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ])->all());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        DB::transaction(fn () => $this->issueStock($stockIssue));

        return back()->with('success', "Đã xuất kho phiếu \"{$stockIssue->code}\" và cập nhật tồn kho.");
    }

    public function destroy(string $id)
    {
        $stockIssue = StockIssue::findOrFail($id);

        if (! $stockIssue->isDraft()) {
            return back()->with('error', 'Không thể xóa phiếu xuất kho đã hoàn tất.');
        }

        $stockIssue->delete();

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'outbound'])
            ->with('success', 'Xóa phiếu xuất kho thành công');
    }

    private function availableVariants()
    {
        return ProductVariant::query()
            ->with(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name'])
            ->whereHas('product', fn ($q) => $q->whereNull('deleted_at'))
            ->where('stock', '>', 0)
            ->get()
            ->map(fn (ProductVariant $v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'product_name' => $v->product?->name,
                'thumbnail' => $v->product?->thumbnail,
                'color_name' => $v->color?->name,
                'color_hex' => $v->color?->display_hex_code,
                'size_name' => $v->size?->name,
                'stock' => $v->stock,
                // Giá xuất mặc định luôn theo giá vốn hệ thống; chỉ "Xuất trả NCC" mới cho phép Admin sửa.
                'unit_price' => (float) $v->cost_price,
                'original_cost_price' => (float) $v->cost_price,
            ])
            ->values();
    }

    private function assertSufficientStock(array $items): void
    {
        foreach ($items as $item) {
            $variant = ProductVariant::find($item['product_variant_id']);

            if (! $variant || $variant->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => "Sản phẩm SKU \"{$variant?->sku}\" không đủ tồn kho (còn {$variant?->stock}, cần {$item['quantity']}).",
                ]);
            }
        }
    }

    private function issueStock(StockIssue $stockIssue): void
    {
        foreach ($stockIssue->items as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

            if (! $variant) {
                continue;
            }

            $variant->update([
                'stock' => max(0, $variant->stock - $item->quantity),
            ]);
        }

        $stockIssue->update([
            'status' => StockIssue::STATUS_ISSUED,
            'issued_at' => now(),
        ]);
    }

    private function generateCode(): string
    {
        $prefix = 'PXK' . now()->format('Ymd');
        $lastToday = StockIssue::where('code', 'like', "{$prefix}%")
            ->orderByDesc('code')
            ->first();

        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
