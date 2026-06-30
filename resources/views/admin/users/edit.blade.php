<div class="modal fade account-modal" id="userEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="userEditForm" method="POST" autocomplete="off">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h2 class="modal-title">Cập nhật {{ $itemLabel ?? 'Tài khoản' }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal_username" class="form-label">Họ và tên</label>
                            <input type="text" name="username" id="modal_username" class="form-control">
                            <div class="invalid-feedback d-block" data-error-for="username"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_email" class="form-label">Email</label>
                            <input type="email" name="email" id="modal_email" class="form-control">
                            <div class="invalid-feedback d-block" data-error-for="email"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_phone_number" class="form-label">Số điện thoại</label>
                            <input type="text" name="phone_number" id="modal_phone_number" class="form-control">
                            <div class="invalid-feedback d-block" data-error-for="phone_number"></div>
                        </div>

                        @if($showRoleColumn)
                            <div class="col-md-6" data-role-field>
                                <label for="modal_role_id" class="form-label">Vai trò</label>
                                <select name="role_id" id="modal_role_id" class="form-select">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $roleLabels[$role->name] ?? $role->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback d-block" data-error-for="role_id"></div>
                                <small data-role-locked-note class="d-none" style="color:#6b7280;font-size:12px;margin-top:4px;display:flex;align-items:center;gap:4px;">
                                    <i class="fa-solid fa-lock" style="font-size:11px;color:#9ca3af;"></i>
                                    Không thể thay đổi vai trò của tài khoản đang đăng nhập
                                </small>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label for="modal_is_active" class="form-label">Trạng thái</label>
                            <select name="is_active" id="modal_is_active" class="form-select">
                                <option value="1">Hoạt động</option>
                                <option value="0">Ngưng hoạt động</option>
                            </select>
                            <div class="invalid-feedback d-block" data-error-for="is_active"></div>
                        </div>

                        <div class="col-md-6 d-none" data-modal-lock-reason-row>
                            <label for="modal_lock_reason" class="form-label">Lý do ngưng hoạt động</label>
                            <input type="text" name="lock_reason" id="modal_lock_reason" class="form-control">
                            <div class="invalid-feedback d-block" data-error-for="lock_reason"></div>
                        </div>

                        @if($showAddressFields)
                            <div class="col-md-6">
                                <label for="modal_city" class="form-label">Tỉnh, Thành phố</label>
                                <input type="text" name="city" id="modal_city" class="form-control">
                                <div class="invalid-feedback d-block" data-error-for="city"></div>
                            </div>


                            <div class="col-md-6">
                                <label for="modal_ward" class="form-label">Phường, Xã</label>
                                <input type="text" name="ward" id="modal_ward" class="form-control">
                                <div class="invalid-feedback d-block" data-error-for="ward"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="modal_apartment_number" class="form-label">Số nhà</label>
                                <input type="text" name="apartment_number" id="modal_apartment_number" class="form-control">
                                <div class="invalid-feedback d-block" data-error-for="apartment_number"></div>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label for="modal_password" class="form-label">Mật khẩu mới</label>
                            <input type="password" name="password" id="modal_password" class="form-control" autocomplete="new-password">
                            <div class="invalid-feedback d-block" data-error-for="password"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" name="password_confirmation" id="modal_password_confirmation" class="form-control" autocomplete="new-password">
                        </div>

                        @if(auth()->user()?->isAdmin() && (bool) auth()->user()?->is_protected)
                        <div class="col-12 d-none" data-protected-row>
                            <div class="d-flex align-items-start gap-3 p-3 rounded border" style="background:var(--bs-warning-bg-subtle,#fff3cd);">
                                <div class="form-check mb-0">
                                    <input type="hidden" name="is_protected" value="0" data-protected-hidden>
                                    <input type="checkbox" name="is_protected" value="1"
                                        id="modal_is_protected" class="form-check-input">
                                    <label for="modal_is_protected" class="form-check-label fw-semibold">
                                        Admin hệ thống được bảo vệ
                                    </label>
                                </div>
                                <small class="text-muted lh-sm mt-1">Kích hoạt để bảo vệ tài khoản khỏi bị thay đổi role, mật khẩu, hoặc khóa bởi admin thường.</small>
                            </div>
                            <div class="invalid-feedback d-block" data-error-for="is_protected"></div>
                        </div>
                        @endif

                        <div class="col-12 d-none" data-permission-error-row>
                            <div class="alert alert-danger py-2 mb-0" role="alert" data-error-for="permission"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
