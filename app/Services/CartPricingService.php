<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CartPricingService
{
    public const FREE_SHIPPING_THRESHOLD = 500000;

    public const SHIPPING_FEE = 35000;

    /**
     * @param  Collection<int, \App\Models\CartItem>  $cartItems
     * @return array{0: float, 1: float, 2: float} [subtotal, shippingFee, total]
     */
    public static function totals(Collection $cartItems): array
    {
        $subtotal = $cartItems->sum(
            fn ($item) => $item->productVariant->product->final_price * $item->quantity
        );

        $shippingFee = $subtotal == 0 || $subtotal >= self::FREE_SHIPPING_THRESHOLD
            ? 0
            : self::SHIPPING_FEE;

        return [$subtotal, $shippingFee, $subtotal + $shippingFee];
    }
}
