<div class="modal fade account-modal" id="userResetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="userResetPasswordForm" method="POST" autocomplete="off">
                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <h2 class="modal-title">Đặt lại mật khẩu cho <span data-reset-username></span></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Tài khoản sẽ phải đổi mật khẩu này ngay ở lần đăng nhập kế tiếp và sẽ nhận được email thông báo.
                    </p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="reset_password" class="form-label">Mật khẩu mới</label>
                            <input type="password" name="password" id="reset_password" class="form-control" autocomplete="new-password">
                            <div class="invalid-feedback d-block" data-error-for="password"></div>
                        </div>

                        <div class="col-12">
                            <label for="reset_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" name="password_confirmation" id="reset_password_confirmation" class="form-control" autocomplete="new-password">
                        </div>

                        <div class="col-12 d-none" data-reset-permission-error-row>
                            <div class="alert alert-danger py-2 mb-0" role="alert" data-error-for="permission"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="fa-solid fa-key me-1"></i> Đặt lại mật khẩu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
