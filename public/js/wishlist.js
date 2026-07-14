
//Xử lý click nút tim (.wl-toggle-btn) trên mọi trang:

(function () {
    'use strict';

    function getCsrf() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    /** Cập nhật tất cả badge trái tim trên header */
    function updateHeaderBadge(count) {
        document.querySelectorAll('.utility-icons a[href*="wishlist"] .badge-count')
            .forEach(function (el) { el.textContent = count; });
    }

    /**
     * Xử lý một nút tim đơn lẻ.
     * @param {HTMLButtonElement} btn - phần tử .wl-toggle-btn
     */
    function handleWishlistToggle(btn) {
        if (btn.dataset.loading === 'true') return;

        const productId  = btn.dataset.productId;
        const inWishlist = btn.dataset.inWishlist === 'true';
        const icon       = btn.querySelector('i');

        if (!productId) return;

        // Gán cờ loading tránh double-click
        btn.dataset.loading = 'true';

        fetch('/yeu-thich/bat-tat/' + productId, {
            method: 'POST',
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
        })
        .then(function (r) {
            if (r.status === 401) {
                // Chưa đăng nhập → chuyển đến trang login
                window.location.href = '/dang-nhap';
                return null;
            }
            return r.json();
        })
        .then(function (data) {
            if (!data) return;

            if (data.added) {
                // ── Vừa THÊM vào wishlist ──
                btn.dataset.inWishlist = 'true';
                btn.title = 'Xóa khỏi yêu thích';
                if (icon) {
                    icon.className = 'bi bi-heart-fill';
                    icon.style.color = '#d9534f';
                }
                // Nhấp nháy nhẹ để báo thành công
                btn.style.transform = 'scale(1.25)';
                setTimeout(function () { btn.style.transform = ''; }, 250);
            } else {
                // ── Vừa XÓA khỏi wishlist ──
                btn.dataset.inWishlist = 'false';
                btn.title = 'Thêm vào yêu thích';
                if (icon) {
                    icon.className = 'bi bi-heart';
                    icon.style.color = '';
                }
            }

            // Cập nhật badge header
            if (data.count !== undefined) {
                updateHeaderBadge(data.count);
            }
        })
        .catch(function () {
            // Lỗi mạng: giữ nguyên trạng thái, không làm gì
        })
        .finally(function () {
            btn.dataset.loading = 'false';
        });
    }

    /** Gắn event listener bằng event delegation lên document
     *  → hoạt động với cả card được append động vào DOM sau này */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.wl-toggle-btn');
        if (!btn) return;

        // Ngăn click lan sang link cha (nếu nút nằm trong thẻ <a>)
        e.preventDefault();
        e.stopPropagation();

        handleWishlistToggle(btn);
    });

}());
