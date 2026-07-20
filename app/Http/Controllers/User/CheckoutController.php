<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Jobs\CancelUnpaidOrderJob;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Voucher;
use App\Models\VoucherHistory;
use App\Services\CartPricingService;
use App\Services\InventoryBatchService;
use App\Services\OrderCancellationService;
use App\Services\PayosService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        // product() dùng withTrashed() nên phải check trashed() riêng: sản phẩm bị xóa mềm có thể vẫn còn status=true.
        $inactive = $cartItems->filter(fn ($item) => $item->productVariant?->status !== 'Active'
            || ! (bool) ($item->productVariant?->product?->status)
            || (bool) $item->productVariant?->product?->trashed());
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
            'address'        => $user->addresses()->orderByDesc('is_default')->orderByDesc('id')->first(),
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

        // Supersede đơn online cũ TRƯỚC KHI xác thực voucher của đơn mới: nếu không nhả voucher
        // đơn cũ trước, khách đặt lại đúng voucher đang dùng dở sẽ bị hệ thống âm thầm BỎ voucher
        // (do vẫn thấy VoucherHistory đơn cũ còn sống → coi là "đã dùng mã này rồi") — đơn vẫn tạo
        // thành công (không còn chặn cứng như trước) nhưng khách mất oan phần giảm giá đáng lẽ được
        // hưởng. Làm sớm ở đây để giữ đúng trải nghiệm "đặt lại thì vẫn còn ưu đãi".
        // cancelPendingUnpaid() tự mở transaction riêng cho từng đơn (atomic + idempotent), không
        // cần bọc thêm transaction ở đây.
        if ($paymentMethod->isOnlineGateway()) {
            $staleOrders = Order::where('user_id', $user->id)
                ->where('status', OrderStatus::PENDING->value)
                ->where('payment_status', PaymentStatus::UNPAID->value)
                ->whereIn('payment_method_id', $this->onlinePaymentMethodIds())
                ->get();

            foreach ($staleOrders as $staleOrder) {
                app(OrderCancellationService::class)->cancelPendingUnpaid(
                    $staleOrder,
                    'Bị thay thế bởi đơn thanh toán online mới'
                );
            }
        }

        $cartItems = $this->cartItems($user);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('warning', 'Giỏ hàng của bạn đang trống.');
        }

        // Backend recheck: từ chối nếu có sản phẩm/biến thể bị ẩn (tránh bypass qua Postman)
        $hasInactive = $cartItems->contains(fn ($item) => $item->productVariant?->status !== 'Active'
            || ! (bool) ($item->productVariant?->product?->status)
            || (bool) $item->productVariant?->product?->trashed());
        if ($hasInactive) {
            return redirect()->route('checkout.index')
                ->with('warning', 'Một số sản phẩm không còn hoạt động. Vui lòng kiểm tra lại giỏ hàng.');
        }

        [$subtotal, $shippingFee, ] = CartPricingService::totals($cartItems);

        $voucherCode = trim((string) ($validated['voucher_code'] ?? ''));
        // Khi voucher không còn hợp lệ LÚC ĐẶT HÀNG THẬT (khác lúc "áp mã" ở UI — có thể khách đã
        // đổi số lượng/giỏ hàng sau đó khiến subtotal không còn đạt min_order_amount, hoặc mã bị
        // người khác dùng hết/admin tắt giữa chừng): KHÔNG chặn cả đơn, chỉ âm thầm bỏ voucher và
        // vẫn cho đặt hàng theo giá gốc — báo cho khách biết lý do qua $voucherDroppedReason.
        $voucherDroppedReason = null;

        try {
            $order = DB::transaction(function () use ($user, $validated, $paymentMethod, $cartItems, $subtotal, $shippingFee, $voucherCode, &$voucherDroppedReason) {
            $address = Address::firstOrCreate([
                'user_id'          => $user->id,
                'city'             => $validated['city'],
                'ward'             => $validated['ward'],
                'apartment_number' => $validated['apartment_number'],
            ]);

            $orderCode = app(\App\Services\DocumentSequenceService::class)->generateOrderCode();

            // Xác thực voucher NGAY TỪ ĐẦU trong transaction (khóa dòng voucher ngay khi đọc) —
            // gộp luôn bước "validate" và "recheck chống race" cũ thành MỘT lần duy nhất, không còn
            // cửa sổ race giữa validate (ngoài transaction, không lock) và ghi nhận (trong transaction)
            // như thiết kế trước đây.
            $finalVoucher = null;
            $discountAmount = 0.0;

            if ($voucherCode !== '') {
                $voucher = Voucher::where('code', $voucherCode)->lockForUpdate()->first();
                $now = Carbon::now();

                $reason = match (true) {
                    ! $voucher || ! $voucher->status => 'Mã giảm giá không tồn tại hoặc đã bị vô hiệu hóa.',
                    $now->lt($voucher->start_date) || $now->gt($voucher->end_date) => 'Mã giảm giá đã hết hạn hoặc chưa đến thời gian sử dụng.',
                    $voucher->used_count >= $voucher->quantity => 'Mã giảm giá đã hết lượt sử dụng.',
                    $subtotal < (float) $voucher->min_order_amount => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã giảm giá này.',
                    VoucherHistory::where('user_id', $user->id)->where('voucher_id', $voucher->id)->exists() => 'Bạn đã sử dụng mã giảm giá này rồi.',
                    default => null,
                };

                if ($reason === null) {
                    $finalVoucher = $voucher;
                    $discountAmount = $this->calculateDiscount($voucher, $subtotal);
                } else {
                    $voucherDroppedReason = $reason;
                }
            }

            $total = $subtotal + $shippingFee - $discountAmount;

            $order = Order::create([
                'user_id'           => $user->id,
                'address_id'        => $address->id,
                'payment_method_id' => $paymentMethod->id,
                'order_code'        => $orderCode,
                'phone'             => $validated['phone'],
                'note'              => $validated['note'] ?? null,
                'total_money'       => $total,
                'shipping_fee'      => $shippingFee,
                'voucher_id'        => $finalVoucher?->id,
                'discount_amount'   => $discountAmount,
                'status'            => OrderStatus::PENDING->value,
                'payment_status'    => PaymentStatus::UNPAID->value,
            ]);

            if ($finalVoucher) {
                VoucherHistory::create([
                    'user_id'   => $user->id,
                    'voucher_id' => $finalVoucher->id,
                    'order_id'  => $order->id,
                    'used_at'   => now(),
                ]);

                $finalVoucher->increment('used_count');
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

                // Giá vốn được snapshot qua StockIssueItem lúc xuất kho, không lưu ở order_items
                // (cột này không tồn tại + không fillable nên trước đây bị Eloquent bỏ im lặng).
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'unit_price'         => $unitPrice,
                    'quantity'           => $item->quantity,
                ]);

                // HOLD: giữ hàng ngay lúc đặt — trừ tồn theo FIFO qua các lô, ghi sổ cái với
                // reference_type='order'. Mọi đường hủy sau này nhả kho qua đúng reference này
                // (OrderCancellationService::releaseHold). Áp dụng cho cả COD để chống oversell.
                app(InventoryBatchService::class)->consumeFifo(
                    $variant,
                    (int) $item->quantity,
                    'order',
                    $order->id,
                    $user->id
                );
            }

            // Đơn online (PayOS/MoMo) giữ nguyên giỏ hàng để người dùng có thể "Tiếp tục thanh toán"
            // hoặc đặt lại với phương thức khác nếu chưa trả tiền. Giỏ chỉ bị xóa khi thanh toán
            // thành công (markPaid()). Đơn COD/chuyển khoản là đơn đã chốt → xóa giỏ ngay.
            if (! $paymentMethod->isOnlineGateway()) {
                $cartItems->each(fn ($item) => $item->delete());
            }

            return $order;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('warning', $e->getMessage());
        } catch (ValidationException $e) {
            // consumeFifo ném ValidationException key 'stock' khi lô không đủ hàng.
            $message = collect($e->errors())->flatten()->first()
                ?? 'Sản phẩm vừa hết hàng. Vui lòng kiểm tra lại giỏ hàng.';

            return redirect()->route('cart.index')->with('warning', $message);
        }

        // RELEASE: hẹn job nhả kho nếu đơn online không được thanh toán đúng hạn.
        // Mốc trễ = ĐÚNG hạn sống của mã QR (30') — hủy sớm hơn sẽ khiến khách thanh toán
        // ở phút cuối trả tiền cho đơn đã hủy. Đơn COD KHÔNG hẹn job (COD luôn 'unpaid'
        // tới khi giao xong, hẹn job sẽ tự hủy oan).
        if ($paymentMethod->isOnlineGateway()) {
            CancelUnpaidOrderJob::dispatch($order)
                ->delay(now()->addMinutes(PayosService::EXPIRE_MINUTES));
        }

        // Voucher bị âm thầm bỏ lúc tạo đơn (xem $voucherDroppedReason ở trên): đơn vẫn đặt thành
        // công theo giá gốc, chỉ cần báo cho khách biết vì sao không thấy giảm giá — không chặn
        // luồng thanh toán (kể cả khi chuyển tiếp sang trang QR online).
        if ($voucherDroppedReason) {
            session()->flash('warning', 'Mã giảm giá không được áp dụng: '.$voucherDroppedReason.' Đơn hàng vẫn được tạo theo giá gốc.');
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
     * Đổi phương thức thanh toán của một đơn "Chờ xác nhận, chưa thanh toán" — ĐỔI TRÊN CÙNG
     * ĐƠN (giống Coolmate/Shopee: "thanh toán lại" chọn cổng khác), KHÔNG tạo đơn mới. Nhờ vậy
     * kho đang giữ + voucher đã ghi nhận của đơn không bị đụng tới (không supersede, không
     * double-hold, không bị chặn "đã dùng mã này rồi" khi đổi qua đổi lại phương thức).
     */
    public function changePaymentMethod(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ], [
            'payment_method_id.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method_id.exists'   => 'Phương thức thanh toán không hợp lệ.',
        ]);

        $newMethod = PaymentMethod::where('id', $validated['payment_method_id'])->where('status', true)->first();

        if (! $newMethod) {
            return back()->withErrors(['payment_method_id' => 'Phương thức thanh toán không hợp lệ.']);
        }

        try {
            DB::transaction(function () use ($order, $newMethod) {
                // Khóa + kiểm tra lại trong transaction: đơn có thể vừa được webhook xác nhận
                // thanh toán, admin xử lý, hoặc job 30' hủy ngay trước khi request này chạy tới.
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== OrderStatus::PENDING->value || $locked->payment_status !== PaymentStatus::UNPAID->value) {
                    throw new \App\Exceptions\StaleOrderStateException();
                }

                if ((int) $locked->payment_method_id === (int) $newMethod->id) {
                    return; // không đổi gì — giữ nguyên mã/link đang có, tránh sinh lại vô ích
                }

                $wasOnline = $locked->paymentMethod?->isOnlineGateway() ?? false;
                $isOnline  = $newMethod->isOnlineGateway();

                $locked->payment_method_id = $newMethod->id;

                // Xóa mã cổng cũ: mỗi lần đổi cổng phải sinh lại link mới ở lần mở trang sau,
                // không được để mã cũ "sống" song song (webhook đến trễ tra theo mã cũ sẽ
                // đối soát nhầm đơn, hoặc trang mới hiển thị QR/link của cổng không còn dùng).
                $locked->payos_order_code = null;
                $locked->payos_payload    = null;
                $locked->momo_order_id    = null;
                $locked->momo_payload     = null;
                $locked->save();

                // Chuyển từ online sang COD/chuyển khoản (offline): đơn coi như đã chốt ngay,
                // xóa giỏ hàng giống hệt hành vi lúc tạo đơn offline ở store(). Chiều ngược lại
                // (offline -> online) KHÔNG khôi phục giỏ — giỏ đã bị xóa từ trước, chấp nhận
                // không đối xứng vì hiếm khi xảy ra (đơn offline coi như đã chốt).
                if ($wasOnline && ! $isOnline) {
                    $locked->clearPurchasedItemsFromCart();
                }
            });
        } catch (\App\Exceptions\StaleOrderStateException) {
            return redirect()->route('orders.show', $order->id)
                ->with('warning', 'Đơn hàng vừa được xử lý/thanh toán, không thể đổi phương thức thanh toán nữa.');
        }

        $order->refresh();

        return redirect($order->paymentResumeUrl() ?? route('orders.show', $order->id))
            ->with('success', 'Đã đổi phương thức thanh toán sang "'.$newMethod->name.'".');
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
