<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cartItems = $this->cartItems($request->user());

        [$subtotal, $shippingFee, $total] = CartPricingService::totals($cartItems);

        return view('user.cart.index', [
            'cartItems'   => $cartItems,
            'subtotal'    => $subtotal,
            'shippingFee' => $shippingFee,
            'total'       => $total,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'color_id'   => ['nullable', 'integer', 'exists:colors,id'],
            'size_id'    => ['nullable', 'integer', 'exists:sizes,id'],
            'quantity'   => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::with(['productVariants' => function ($query) {
            $query->where('stock', '>', 0)->orderBy('id');
        }])->findOrFail($validated['product_id']);

        if (!empty($validated['color_id']) && !empty($validated['size_id'])) {
            $variant = $product->productVariants
                ->where('color_id', $validated['color_id'])
                ->where('size_id', $validated['size_id'])
                ->first();
        } else {
            $variant = $product->productVariants->first();
        }

        if (! $variant) {
            return response()->json([
                'message' => 'Sản phẩm này hiện đã hết hàng.',
            ], 422);
        }

        $requestedQty = $validated['quantity'] ?? 1;
        $user = $request->user();

        DB::transaction(function () use ($user, $variant, $requestedQty) {
            $cart = $user->cart()->firstOrCreate(['user_id' => $user->id]);

            $cartItem = $cart->cartItems()->where('product_variant_id', $variant->id)->first();

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => min($cartItem->quantity + $requestedQty, $variant->stock),
                ]);
            } else {
                $cart->cartItems()->create([
                    'product_variant_id' => $variant->id,
                    'quantity'           => min($requestedQty, $variant->stock),
                ]);
            }
        });

        $cartCount = (int) $user->cart()->first()?->cartItems()->sum('quantity');

        return response()->json([
            'message'    => 'Đã thêm sản phẩm vào giỏ hàng.',
            'cart_url'   => route('cart.index'),
            'cart_count' => $cartCount,
        ]);
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeOwnership($request, $cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $stock = $cartItem->productVariant->stock;
        $cartItem->update([
            'quantity' => min($validated['quantity'], $stock),
        ]);

        return $this->cartSummaryResponse($request->user(), $cartItem);
    }

    public function switchVariant(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeOwnership($request, $cartItem);

        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
        ]);

        $newVariant = \App\Models\ProductVariant::with(['color', 'size'])->findOrFail($validated['product_variant_id']);

        abort_unless($newVariant->product_id === $cartItem->productVariant->product_id, 422, 'Variant không thuộc sản phẩm này.');
        abort_unless($newVariant->stock > 0, 422, 'Variant này đã hết hàng.');

        // If the target variant already exists in cart, merge quantities
        $existing = $cartItem->cart->cartItems()
            ->where('product_variant_id', $newVariant->id)
            ->where('id', '!=', $cartItem->id)
            ->first();

        if ($existing) {
            $mergedQty = min($cartItem->quantity + $existing->quantity, $newVariant->stock);
            $existing->update(['quantity' => $mergedQty]);
            $cartItem->delete();
            $cartItem = $existing->fresh(['productVariant.product', 'productVariant.color', 'productVariant.size']);
        } else {
            $cartItem->update([
                'product_variant_id' => $newVariant->id,
                'quantity'           => min($cartItem->quantity, $newVariant->stock),
            ]);
            $cartItem = $cartItem->fresh(['productVariant.product', 'productVariant.color', 'productVariant.size']);
        }

        $variant = $cartItem->productVariant;

        return response()->json([
            'item_id'          => $cartItem->id,
            'item_quantity'    => $cartItem->quantity,
            'item_subtotal'    => $variant->product->final_price * $cartItem->quantity,
            'variant_id'       => $variant->id,
            'color_id'         => $variant->color_id,
            'color_name'       => $variant->color?->name,
            'size_id'          => $variant->size_id,
            'size_name'        => $variant->size?->name,
            'variant_image'    => $variant->image ?: $variant->product->thumbnail,
            'stock'            => $variant->stock,
            'merged'           => isset($existing),
            'merged_item_id'   => isset($existing) ? $existing->id : null,
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeOwnership($request, $cartItem);

        $cartItem->delete();

        return $this->cartSummaryResponse($request->user());
    }

    private function authorizeOwnership(Request $request, CartItem $cartItem): void
    {
        abort_unless($cartItem->cart->user_id === $request->user()->id, 403);
    }

    private function cartSummaryResponse($user, ?CartItem $cartItem = null): JsonResponse
    {
        $cartItems = $this->cartItems($user);

        [$subtotal, $shippingFee, $total] = CartPricingService::totals($cartItems);

        return response()->json([
            'item_quantity' => $cartItem?->quantity,
            'item_subtotal' => $cartItem
                ? $cartItem->productVariant->product->final_price * $cartItem->quantity
                : null,
            'subtotal'      => $subtotal,
            'shipping_fee'  => $shippingFee,
            'total'         => $total,
            'is_empty'      => $cartItems->isEmpty(),
            'cart_count'    => (int) $cartItems->sum('quantity'),
        ]);
    }

    private function cartItems($user)
    {
        $cart = $user->cart()->with([
            'cartItems.productVariant.product.productVariants.color',
            'cartItems.productVariant.product.productVariants.size',
            'cartItems.productVariant.color',
            'cartItems.productVariant.size',
        ])->first();

        return $cart?->cartItems ?? collect();
    }
}
