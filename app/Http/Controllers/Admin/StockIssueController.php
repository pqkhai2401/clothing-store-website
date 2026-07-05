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
        $variants = ProductVariant::query()
            ->with(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name'])
            ->whereHas('product', fn ($q) => $q->whereNull('deleted_at'))
            ->where('stock', '>', 0)
            ->get()
            ->map(fn (ProductVariant $v) => [
                'id'           => $v->id,
                'sku'          => $v->sku,
                'product_name' => $v->product?->name,
                'thumbnail'    => $v->product?->thumbnail,
                'color_name'   => $v->color?->name,
                'color_hex'    => $v->color?->display_hex_code,
                'size_name'    => $v->size?->name,
                'stock'        => $v->stock,
                'unit_price'   => (float) $v->sale_price,
            ])
            ->values();

        return view('admin.stock-issues.create', compact('variants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason'                      => ['required', 'string', 'max:255'],
            'action'                      => ['required', Rule::in(['draft', 'issue'])],
            'items'                       => ['required', 'array', 'min:1'],
            'items.*.product_variant_id'  => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity'            => ['required', 'integer', 'min:1'],
            'items.*.unit_price'          => ['required', 'numeric', 'min:0'],
        ], [
            'reason.required' => 'Vui lòng nhập lý do xuất kho.',
            'items.required'  => 'Vui lòng chọn ít nhất một sản phẩm để xuất kho.',
            'items.min'       => 'Vui lòng chọn ít nhất một sản phẩm để xuất kho.',
        ]);

        if ($validated['action'] === 'issue') {
            $this->assertSufficientStock($validated['items']);
        }

        $stockIssue = DB::transaction(function () use ($validated) {
            $totalAmount = collect($validated['items'])
                ->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

            $stockIssue = StockIssue::create([
                'code'         => $this->generateCode(),
                'reason'       => $validated['reason'],
                'status'       => StockIssue::STATUS_DRAFT,
                'total_amount' => $totalAmount,
                'created_by'   => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $stockIssue->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                ]);
            }

            if ($validated['action'] === 'issue') {
                $this->issueStock($stockIssue);
            }

            return $stockIssue;
        });

        return redirect()->route('admin.stock-issues.show', $stockIssue->id)
            ->with('success', "Tạo phiếu xuất kho \"{$stockIssue->code}\" thành công.");
    }

    public function show(string $id)
    {
        $stockIssue = StockIssue::with([
            'creator',
            'items.productVariant.product:id,name,thumbnail',
            'items.productVariant.color:id,name,hex_code',
            'items.productVariant.size:id,name',
        ])->findOrFail($id);

        return view('admin.stock-issues.show', compact('stockIssue'));
    }

    public function issue(string $id)
    {
        $stockIssue = StockIssue::with('items')->findOrFail($id);

        if (!$stockIssue->isDraft()) {
            return back()->with('error', 'Phiếu xuất kho này đã được xuất trước đó.');
        }

        try {
            $this->assertSufficientStock($stockIssue->items->map(fn ($item) => [
                'product_variant_id' => $item->product_variant_id,
                'quantity'           => $item->quantity,
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

        if (!$stockIssue->isDraft()) {
            return back()->with('error', 'Không thể xóa phiếu xuất kho đã hoàn tất.');
        }

        $stockIssue->delete();

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'outbound'])
            ->with('success', 'Xóa phiếu xuất kho thành công');
    }

    private function assertSufficientStock(array $items): void
    {
        foreach ($items as $item) {
            $variant = ProductVariant::find($item['product_variant_id']);
            if (!$variant || $variant->stock < $item['quantity']) {
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
            if (!$variant) continue;

            $variant->update([
                'stock' => max(0, $variant->stock - $item->quantity),
            ]);
        }

        $stockIssue->update([
            'status'    => StockIssue::STATUS_ISSUED,
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
