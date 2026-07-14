<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Voucher;
use App\Models\VoucherHistory;
use App\Services\CartPricingService;
use Carbon\Carbon;
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

        // Xóa sản phẩm đã bị ẩn khỏi giỏ và báo user (cả sản phẩm và biến thể).
        // Lưu ý: status của ProductVariant là chuỗi 'Active'/'Inactive' (không phải boolean như Product).
        $inactive = $cartItems->filter(fn ($item) => $item->productVariant?->status !== 'Active'
            || ! (bool) ($item->productVariant?->product?->status));
        if ($inactive->isNotEmpty()) {
            $inactive->each(fn ($item) => $item->delete());
            return redirect()->route('cart.index')
                ->with('warning', 'Một số sản phẩm trong giỏ hàng đã ngừng bán và đã được xóa. Vui lòng kiểm tra lại.');
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
            'ward'              => ['required', 'string', 'max:255'],
            'apartment_number'  => ['required', 'string', 'max:255'],
            'note'              => ['nullable', 'string', 'max:1000'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'voucher_code'      => ['nullable', 'string', 'max:255'],
            'agree_policy'      => ['accepted'],
        ], [
            'phone.required'             => 'Vui lòng nhập số điện thoại.',
            'city.required'              => 'Vui lòng chọn tỉnh/thành phố.',
            'ward.required'              => 'Vui lòng chọn phường/xã.',
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

        // Backend recheck: từ chối nếu có sản phẩm/biến thể bị ẩn (tránh bypass qua Postman)
        $hasInactive = $cartItems->contains(fn ($item) => $item->productVariant?->status !== 'Active'
            || ! (bool) ($item->productVariant?->product?->status));
        if ($hasInactive) {
            return redirect()->route('checkout.index')
                ->with('warning', 'Một số sản phẩm không còn hoạt động. Vui lòng kiểm tra lại giỏ hàng.');
        }

        [$subtotal, $shippingFee, $total] = CartPricingService::totals($cartItems);

        // Xác thực lại voucher ở backend (không tin dữ liệu discount từ client) để tránh bypass qua Postman.
        $voucher = null;
        $discountAmount = 0.0;

        if (! empty($validated['voucher_code'])) {
            $voucher = $this->validateVoucher($validated['voucher_code'], $subtotal, $user);

            if ($voucher instanceof \Illuminate\Http\RedirectResponse) {
                return $voucher;
            }

            $discountAmount = $this->calculateDiscount($voucher, $subtotal);
            $total -= $discountAmount;
        }

        try {
            $order = DB::transaction(function () use ($user, $validated, $paymentMethod, $cartItems, $shippingFee, $total, $voucher, $discountAmount) {
            $address = Address::firstOrCreate([
                'user_id'          => $user->id,
                'city'             => $validated['city'],
                'ward'             => $validated['ward'],
                'apartment_number' => $validated['apartment_number'],
            ]);

            $orderCode = app(\App\Services\DocumentSequenceService::class)->generateOrderCode();

            $order = Order::create([
                'user_id'           => $user->id,
                'address_id'        => $address->id,
                'payment_method_id' => $paymentMethod->id,
                'order_code'        => $orderCode,
                'phone'             => $validated['phone'],
                'note'              => $validated['note'] ?? null,
                'total_money'       => $total,
                'shipping_fee'      => $shippingFee,
                'voucher_id'        => $voucher?->id,
                'discount_amount'   => $discountAmount,
                'status'            => OrderStatus::PENDING->value,
                'payment_status'    => PaymentStatus::UNPAID->value,
            ]);

            if ($voucher) {
                // Khóa dòng voucher + recheck trong transaction để chống race-condition:
                // 2 checkout đồng thời có thể cùng vượt qua validateVoucher() (chạy ngoài transaction,
                // không lock) rồi cùng increment -> dùng quá số lượng / cùng user dùng lại 1 mã.
                $lockedVoucher = Voucher::whereKey($voucher->id)->lockForUpdate()->first();

                if (! $lockedVoucher || $lockedVoucher->used_count >= $lockedVoucher->quantity) {
                    throw new \RuntimeException('Mã giảm giá đã hết lượt sử dụng.');
                }

                $alreadyUsed = VoucherHistory::where('user_id', $user->id)
                    ->where('voucher_id', $lockedVoucher->id)
                    ->exists();

                if ($alreadyUsed) {
                    throw new \RuntimeException('Bạn đã sử dụng mã giảm giá này rồi.');
                }

                VoucherHistory::create([
                    'user_id'   => $user->id,
                    'voucher_id' => $lockedVoucher->id,
                    'order_id'  => $order->id,
                    'used_at'   => now(),
                ]);

                $lockedVoucher->increment('used_count');
            }

            foreach ($cartItems as $item) {
                // Khóa + recheck lại status/tồn kho ngay trước khi tạo đơn (chống race-condition
                // giữa lúc user xem giỏ hàng và lúc bấm đặt hàng — 2 tab, 2 user cùng mua...).
                $variant = ProductVariant::lockForUpdate()->with('product')->find($item->product_variant_id);

                if (! $variant || $variant->status !== 'Active' || ! $variant->product?->status) {
                    throw new \RuntimeException('Một số sản phẩm trong giỏ hàng đã ngừng bán. Vui lòng kiểm tra lại giỏ hàng.');
                }

                if ($variant->stock < $item->quantity) {
                    $name = trim(($variant->product->name ?? 'Sản phẩm').' - '.($variant->sku ?? ''));
                    throw new \RuntimeException("Sản phẩm [{$name}] chỉ còn {$variant->stock} sản phẩm trong kho. Vui lòng cập nhật lại giỏ hàng.");
                }

                $unitPrice = (float) $variant->final_price;

                // Giá vốn được snapshot qua StockIssueItem lúc admin xuất kho, không lưu ở order_items
                // (cột này không tồn tại + không fillable nên trước đây bị Eloquent bỏ im lặng).
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'unit_price'         => $unitPrice,
                    'quantity'           => $item->quantity,
                ]);
            }

            // Đơn online (PayOS/MoMo) giữ nguyên giỏ hàng để người dùng có thể "Tiếp tục thanh toán"
            // hoặc đặt lại với phương thức khác nếu chưa trả tiền. Giỏ chỉ bị xóa khi thanh toán
            // thành công (markPaid()). Đơn COD/chuyển khoản là đơn đã chốt → xóa giỏ ngay.
            if (! $paymentMethod->isOnlineGateway()) {
                $cartItems->each(fn ($item) => $item->delete());
            }

            // Đơn online (PayOS/MoMo) mới thay thế các đơn online chưa thanh toán cũ của user:
            // hủy chúng để tránh đơn treo tích tụ (chỉ đổi status, KHÔNG hoàn kho vì
            // đơn pending/unpaid chưa từng bị trừ kho — xem OrderController::cancelOrder).
            if ($paymentMethod->isOnlineGateway()) {
                Order::where('user_id', $user->id)
                    ->where('id', '!=', $order->id)
                    ->where('status', OrderStatus::PENDING->value)
                    ->where('payment_status', PaymentStatus::UNPAID->value)
                    ->whereIn('payment_method_id', $this->onlinePaymentMethodIds())
                    ->update(['status' => OrderStatus::CANCELLED->value]);
            }

            return $order;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('warning', $e->getMessage());
        }

        // Cổng online: chuyển sang trang QR để thanh toán ngay thay vì kết thúc.
        if ($paymentMethod->isPayos()) {
            return redirect()->route('checkout.payos.show', $order->id);
        }

        if ($paymentMethod->isMomo()) {
            return redirect()->route('checkout.momo.show', $order->id);
        }

        return redirect()->route('home')
            ->with('success', 'Đặt hàng thành công! Mã đơn hàng của bạn là '.$order->order_code.'.');
    }

    /**
     * Xác thực mã giảm giá ở backend trước khi đặt hàng (đồng bộ luật với Api\VoucherController::apply).
     * Trả về Voucher hợp lệ, hoặc RedirectResponse kèm lỗi nếu không hợp lệ.
     */
    private function validateVoucher(string $code, float $subtotal, $user)
    {
        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher || ! $voucher->status) {
            return back()->withErrors(['voucher_code' => 'Mã giảm giá không tồn tại hoặc đã bị vô hiệu hóa.'])->withInput();
        }

        $now = Carbon::now();
        if ($now->lt($voucher->start_date) || $now->gt($voucher->end_date)) {
            return back()->withErrors(['voucher_code' => 'Mã giảm giá đã hết hạn hoặc chưa đến thời gian sử dụng.'])->withInput();
        }

        if ($voucher->used_count >= $voucher->quantity) {
            return back()->withErrors(['voucher_code' => 'Mã giảm giá đã hết lượt sử dụng.'])->withInput();
        }

        if ($subtotal < (float) $voucher->min_order_amount) {
            return back()->withErrors(['voucher_code' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã giảm giá này.'])->withInput();
        }

        $alreadyUsed = VoucherHistory::where('user_id', $user->id)
            ->where('voucher_id', $voucher->id)
            ->exists();

        if ($alreadyUsed) {
            return back()->withErrors(['voucher_code' => 'Bạn đã sử dụng mã giảm giá này rồi.'])->withInput();
        }

        return $voucher;
    }

    private function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        if ($voucher->type === 'percentage') {
            $discountAmount = $subtotal * ((float) $voucher->value / 100);

            if ($voucher->max_discount_amount !== null) {
                $discountAmount = min($discountAmount, (float) $voucher->max_discount_amount);
            }
        } else {
            $discountAmount = (float) $voucher->value;
        }

        return min($discountAmount, $subtotal);
    }

    /**
     * ID các phương thức thanh toán online (PayOS/MoMo) — dùng để lọc đơn online treo.
     */
    private function onlinePaymentMethodIds(): array
    {
        return PaymentMethod::onlineGatewayIds();
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
