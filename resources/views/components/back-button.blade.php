{{--
    Component: <x-back-button />

    Nút "Quay lại" tối giản, đồng bộ phong cách với website HK STORE.
    - Mặc định hiển thị dạng nút link (btn-link) với icon mũi tên trái + chữ "Quay lại".
    - Khi click: dùng JavaScript window.history.back() để quay về trang trước đó
      trong lịch sử trình duyệt (không cần load lại toàn bộ trang qua server).
    - Dự phòng (Graceful Fallback): nếu người dùng vào thẳng trang này từ một
      liên kết ngoài (không có trang trước trong lịch sử trình duyệt), script sẽ
      tự động chuyển nút này thành thẻ <a> trỏ thẳng về trang chủ, tránh trường hợp
      bấm "Quay lại" nhưng không có gì để quay về (đứng yên hoặc thoát khỏi web).

    Props (tuỳ chọn):
    - label: Đổi chữ hiển thị (mặc định: "Quay lại")
    - class: Thêm class tuỳ biến bổ sung (ví dụ margin, spacing riêng cho từng trang)
--}}

@props([
    'label' => 'Quay lại',
])

{{-- Thẻ <a> vừa đóng vai trò nút bấm, vừa là link dự phòng khi cần fallback về trang chủ --}}
<a
    href="{{ url('/') }}"
    id="backButton"
    onclick="return handleBackButtonClick(event, this)"
    {{ $attributes->merge(['class' => 'btn text-dark text-decoration-none d-inline-flex align-items-center fw-bold mb-3 back-button-hk']) }}
>
    {{-- Icon mũi tên trái vẽ bằng SVG thuần, không cần thư viện icon ngoài --}}
    <svg
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="me-2"
        aria-hidden="true"
    >
        <path d="M19 12H5"></path>
        <path d="M12 19l-7-7 7-7"></path>
    </svg>
    <span>{{ $label }}</span>
</a>

<style>
    /* CSS tối giản, vuông vức, có viền rõ ràng, đồng bộ với hệ thống thiết kế chung của HK STORE */
    .back-button-hk {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 16px;
        border: 1.5px solid var(--border-color, #1a1a1a);
        border-radius: 0; /* Vuông vức, không bo góc */
        background-color: transparent;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    }

    /* Hover: đảo màu nền/chữ + hơi nhích sang trái để tạo hiệu ứng "nổi" khi rê chuột */
    .back-button-hk:hover {
        background-color: var(--primary-color, #000);
        border-color: var(--primary-color, #000);
        color: #fff !important;
        transform: translateX(-3px);
    }
</style>

{{--
    @once đảm bảo đoạn script chỉ được nhúng MỘT LẦN vào trang,
    kể cả khi component <x-back-button /> được gọi nhiều lần trên cùng một view.
--}}
@once
    @push('scripts')
        <script>
            /**
             * Xử lý sự kiện click nút "Quay lại".
             *
             * Logic chống lỗi (Graceful Fallback):
             * - window.history.length <= 1 nghĩa là trình duyệt KHÔNG có trang nào
             *   trước đó trong phiên duyệt web hiện tại (ví dụ: người dùng dán link
             *   trực tiếp vào thanh địa chỉ, hoặc mở từ một trang web/ứng dụng khác).
             * - Trong trường hợp đó, ta để mặc định hành vi của thẻ <a> xảy ra
             *   (return true) để trình duyệt điều hướng thẳng về trang chủ (href="/").
             * - Ngược lại, nếu có lịch sử duyệt web hợp lệ, ta chặn hành vi mặc định
             *   của thẻ <a> (return false) và gọi window.history.back() để quay lại
             *   đúng trang trước đó, giữ nguyên trải nghiệm điều hướng tự nhiên.
             */
            function handleBackButtonClick(event, element) {
                if (window.history.length <= 1) {
                    // Không có trang trước đó -> để thẻ <a> điều hướng về trang chủ như bình thường
                    return true;
                }

                // Có lịch sử duyệt web -> chặn link mặc định, quay lại trang trước đó
                event.preventDefault();
                window.history.back();
                return false;
            }
        </script>
    @endpush
@endonce
