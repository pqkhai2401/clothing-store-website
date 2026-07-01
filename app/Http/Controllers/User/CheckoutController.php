<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Services\CartPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $cartItems = $this->cartItems($user);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Giỏ hàng của bạn đang trống.');
        }

        [$subtotal, $shippingFee, $total] = CartPricingService::totals($cartItems);

        return view('user.checkout.index', [
            'cartItems'      => $cartItems,
            'subtotal'       => $subtotal,
            'shippingFee'    => $shippingFee,
            'total'          => $total,
            'address'        => $user->addresses()->latest()->first(),
            'paymentMethods' => PaymentMethod::where('status', true)->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone'             => ['required', 'string', 'max:20'],
            'city'              => ['required', 'string', 'max:255'],
            'district'          => ['required', 'string', 'max:255'],
            'ward'              => ['required', 'string', 'max:255'],
            'apartment_number'  => ['required', 'string', 'max:255'],
            'note'              => ['nullable', 'string', 'max:1000'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'agree_policy'      => ['accepted'],
        ], [
            'phone.required'             => 'Vui lòng nhập số điện thoại.',
            'city.required'              => 'Vui lòng nhập tỉnh/thành phố.',
            'district.required'          => 'Vui lòng nhập quận/huyện.',
            'ward.required'              => 'Vui lòng nhập phường/xã.',
            'apartment_number.required'  => 'Vui lòng nhập địa chỉ cụ thể.',
            'payment_method_id.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method_id.exists'   => 'Phương thức thanh toán không hợp lệ.',
            'agree_policy.accepted'      => 'Bạn cần đồng ý với điều khoản trước khi đặt hàng.',
        ]);

        $paymentMethod = PaymentMethod::where('id', $validated['payment_method_id'])
            ->where('status', true)
            ->first();

        if (! $paymentMethod) {
            return back()->withErrors(['payment_method_id' => 'Phương thức thanh toán không hợp lệ.'])->withInput();
        }

        $cartItems = $this->cartItems($user);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Giỏ hàng của bạn đang trống.');
        }

        [, $shippingFee, $total] = CartPricingService::totals($cartItems);

        $order = DB::transaction(function () use ($user, $validated, $paymentMethod, $cartItems, $shippingFee, $total) {
            $address = Address::firstOrCreate([
                'user_id'          => $user->id,
                'city'             => $validated['city'],
                'district'         => $validated['district'],
                'ward'             => $validated['ward'],
                'apartment_number' => $validated['apartment_number'],
            ]);

            do {
                $orderCode = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (Order::where('order_code', $orderCode)->exists());

            $order = Order::create([
                'user_id'           => $user->id,
                'address_id'        => $address->id,
                'payment_method_id' => $paymentMethod->id,
                'order_code'        => $orderCode,
                'phone'             => $validated['phone'],
                'note'              => $validated['note'] ?? null,
                'total_money'       => $total,
                'shipping_fee'      => $shippingFee,
                'status'            => OrderStatus::PENDING->value,
                'payment_status'    => PaymentStatus::UNPAID->value,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'unit_price'         => $item->productVariant->product->final_price,
                    'quantity'           => $item->quantity,
                ]);
            }

            $cartItems->each(fn ($item) => $item->delete());

            return $order;
        });

        return redirect()->route('home')
            ->with('success', 'Đặt hàng thành công! Mã đơn hàng của bạn là '.$order->order_code.'.');
    }

    private function cartItems($user)
    {
        $cart = $user->cart()->with([
            'cartItems.productVariant.product',
            'cartItems.productVariant.color',
            'cartItems.productVariant.size',
        ])->first();

        return $cart?->cartItems ?? collect();
    }
}