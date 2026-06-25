
<!-- Newsletter -->
<section class="newsletter-section text-center">
    <div class="container">
        <h2 class="section-title mb-3">GIA NHẬP HKCLUB NGAY</h2>
        <p class="text-muted mb-4 max-w-xl mx-auto">
            Đăng ký tài khoản HKClub để mua sắm nhanh hơn, lưu sản phẩm yêu thích,
             theo dõi đơn hàng dễ dàng và nhận những ưu đãi dành riêng cho thành viên HKStore.
        </p>
        <form action="{{ url('/newsletter/subscribe') }}" method="POST" class="newsletter-form d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2">
            @csrf
            {{-- <input type="email" name="email" class="form-control" placeholder="ENTER YOUR EMAIL ADDRESS" required> --}}
            <button type="text" class="btn btn-black">THAM GIA HKCLUB NGAY</button>
        </form>
    </div>
</section>

