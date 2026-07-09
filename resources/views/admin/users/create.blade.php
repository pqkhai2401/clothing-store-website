@extends('layouts.admin')

@section('title', ($type ?? 'all') === 'staff' ? 'Thêm quản trị viên mới' : ($createLabel ?? 'Thêm tài khoản'))

@push('styles')
    @include('admin.users.styles')
@endpush

@section('content')
    @php
        $isStaffContext = ($type ?? 'all') === 'staff';
        $pageHeading = $isStaffContext ? 'Thêm mới quản trị viên' : (($createLabel ?? 'Thêm tài khoản').' mới');
        $cardHeading = $isStaffContext ? 'Thông tin Quản trị viên' : 'Thông tin '.($itemLabel ?? 'Tài khoản');
        $submitLabel = $isStaffContext ? 'Thêm mới quản trị viên' : 'Thêm mới '.($itemLabelLower ?? 'người dùng');
    @endphp

    <main class="app-main container-fluid py-4">
        <x-notification />

        <h1 class="h4 fw-semibold mb-4">{{ $pageHeading }}</h1>

        <div class="card account-create-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2 class="h6 fw-semibold mb-0">{{ $cardHeading }}</h2>
                <span class="text-muted">-</span>
            </div>

            <form method="POST" action="{{ route(($routePrefix ?? 'admin.users').'.store') }}" autocomplete="off">
                @csrf
                <input type="hidden" name="is_active" value="1">

                <div class="card-body p-3">
                    <div class="account-form-row">
                        <label for="username" class="account-form-label">Họ Và Tên</label>
                        <div>
                            <input type="text" name="username" id="username"
                                class="form-control account-form-control @error('username') is-invalid @enderror"
                                value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="email" class="account-form-label">Email</label>
                        <div>
                            <input type="email" name="email" id="email"
                                class="form-control account-form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="password" class="account-form-label">Mật Khẩu</label>
                        <div>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control account-form-control @error('password') is-invalid @enderror"
                                    required>
                                <button type="button" class="btn btn-outline-secondary pw-toggle"
                                    data-target="password" tabindex="-1"
                                    title="Hiện / Ẩn mật khẩu">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="phone_number" class="account-form-label">Số Điện Thoại</label>
                        <div>
                            <input type="text" name="phone_number" id="phone_number"
                                class="form-control account-form-control @error('phone_number') is-invalid @enderror"
                                value="{{ old('phone_number') }}">
                            @error('phone_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="role_id" class="account-form-label">Vai Trò</label>
                        <div>
                            @php
                                $roleLabels = ['admin' => 'Quản trị viên', 'staff' => 'Nhân viên', 'customer' => 'Khách hàng'];
                            @endphp
                            <select name="role_id" id="role_id"
                                class="form-select account-form-control @error('role_id') is-invalid @enderror"
                                required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected((string) old('role_id', $roles->first()?->id) === (string) $role->id)>
                                        {{ $roleLabels[$role->name] ?? $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if(!empty($currentUserIsProtectedAdmin))
                    <div class="account-form-row" id="isProtectedRow" style="display:none;">
                        <label class="account-form-label">Quyền bảo vệ</label>
                        <div>
                            <div class="form-check">
                                <input type="checkbox" name="is_protected" value="1"
                                    id="is_protected"
                                    class="form-check-input @error('is_protected') is-invalid @enderror"
                                    {{ old('is_protected') ? 'checked' : '' }}>
                                <label for="is_protected" class="form-check-label fw-semibold">
                                    Admin hệ thống được bảo vệ
                                </label>
                                <small class="text-muted d-block mt-1">Chỉ admin hệ thống khác mới được thay đổi role, mật khẩu hoặc khóa tài khoản này.</small>
                            </div>
                            @error('is_protected')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <div class="account-form-row">
                        <label for="city" class="account-form-label">Tỉnh, Thành Phố</label>
                        <div>
                            <input type="text" name="city" id="city"
                                class="form-control account-form-control @error('city') is-invalid @enderror"
                                value="{{ old('city') }}">
                            @error('city')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    

                    <div class="account-form-row">
                        <label for="ward" class="account-form-label">Phường, Xã</label>
                        <div>
                            <input type="text" name="ward" id="ward"
                                class="form-control account-form-control @error('ward') is-invalid @enderror"
                                value="{{ old('ward') }}">
                            @error('ward')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="account-form-row">
                        <label for="apartment_number" class="account-form-label">Số Nhà</label>
                        <div>
                            <input type="text" name="apartment_number" id="apartment_number"
                                class="form-control account-form-control @error('apartment_number') is-invalid @enderror"
                                value="{{ old('apartment_number') }}">
                            @error('apartment_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-white border-top">
                    <div class="account-form-actions">
                        <a href="{{ route(($routePrefix ?? 'admin.users').'.list') }}" class="btn btn-light border">Hủy</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> {{ $submitLabel }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.pw-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.target);
        var icon  = btn.querySelector('i');
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    });
});

(function () {
    var roleSelect       = document.getElementById('role_id');
    var protectedRow     = document.getElementById('isProtectedRow');
    var protectedCb      = document.getElementById('is_protected');
    if (!roleSelect || !protectedRow) return;

    var adminRoleIds = @json(
        ($roles ?? collect())->filter(fn ($r) => $r->name === 'admin')->pluck('id')->values()
    );

    function syncProtectedRow() {
        var isAdmin = adminRoleIds.includes(parseInt(roleSelect.value));
        protectedRow.style.display = isAdmin ? '' : 'none';
        if (!isAdmin && protectedCb) protectedCb.checked = false;
    }

    roleSelect.addEventListener('change', syncProtectedRow);
    syncProtectedRow();
})();
</script>
@endpush
