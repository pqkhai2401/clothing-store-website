<!-- Newsletter -->
<section class="newsletter-section text-center">
    <div class="container">
        <h2 class="section-title mb-3">Tham Gia Cùng Chúng Tôi</h2>
        <p class="text-muted mb-4 max-w-xl mx-auto">Đăng ký để nhận quyền truy cập sớm vào các bộ sưu tập mới, xem trước các chiến dịch độc quyền và cập nhật các sáng kiến thân thiện môi trường.</p>
        <form action="{{ url('/newsletter/subscribe') }}" method="POST" class="newsletter-form d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2">
            @csrf
            <input type="email" name="email" class="form-control" placeholder="NHẬP ĐỊA CHỈ EMAIL CỦA BẠN" required>
            <button type="submit" class="btn btn-black">ĐĂNG KÝ</button>
        </form>
    </div>
</section>
