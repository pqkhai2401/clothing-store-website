@extends('layouts.admin')

@section('title', ($type ?? 'all') === 'staff' ? 'Thêm quản trị viên mới' : ($createLabel ?? 'Thêm tài khoản'))

@push('styles')
    @include('admin.users.styles')
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            corePlugins: {
                preflight: false,
            },
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
    @php
        $isStaffContext = ($type ?? 'all') === 'staff';
        $pageHeading = $isStaffContext ? 'Thêm mới quản trị viên' : (($createLabel ?? 'Thêm tài khoản').' mới');
        $submitLabel = $isStaffContext ? 'Thêm mới quản trị viên' : 'Thêm mới '.($itemLabelLower ?? 'người dùng');
        $routeBase = $routePrefix ?? 'admin.users';
        $fieldClass = 'w-full rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm outline-none transition-all focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20';
        $labelClass = 'text-sm font-semibold text-slate-700';
        $errorClass = 'mt-1 text-xs font-medium text-rose-600';
        $roleLabels = [
            'admin' => 'Quản trị viên',
            'warehouse' => 'Thủ kho',
            'stock' => 'Thủ kho',
            'staff' => 'Nhân viên bán hàng',
            'customer' => 'Khách hàng',
        ];
    @endphp

    <div class="min-h-screen bg-slate-50 pb-32">
        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <x-notification />

            <div class="mb-6">
                <h1 class="account-header-title mb-2" style="color: black;">{{ $pageHeading }}</h1>
                <p class="account-header-desc mb-0" style="color: #64748b;">Tạo tài khoản nội bộ với thông tin nhân sự, vai trò và địa chỉ liên hệ.</p>
            </div>

            <form method="POST" action="{{ route($routeBase.'.store') }}" autocomplete="off"
                enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-[320px_1fr] gap-6 items-start">
                @csrf

                {{-- ─── Cột trái: Ảnh đại diện ─── --}}
                <section class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm flex flex-col gap-5">
                    <div>
                        <h2 class="m-0 text-base font-semibold text-slate-950">Ảnh đại diện</h2>
                        <p class="m-0 mt-1 text-sm text-slate-500">Chọn ảnh hiển thị.</p>
                    </div>

                    <div class="flex flex-col items-center gap-4">
                        <div class="h-32 w-32 overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200 flex items-center justify-center">
                            <img id="avatarPreview" src="" alt="Ảnh đại diện"
                                class="h-full w-full object-cover hidden">
                            <i id="avatarPlaceholder" class="fa-solid fa-user text-5xl text-slate-300"></i>
                        </div>

                        <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden">

                        <div class="flex items-center gap-2">
                            <button type="button" data-avatar-choose
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50">
                                <i class="fa-regular fa-image"></i> Chọn ảnh
                            </button>
                            <button type="button" data-avatar-remove
                                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-400 transition-all hover:text-rose-600 disabled:opacity-50"
                                disabled>
                                <i class="fa-regular fa-trash-can"></i> Xóa ảnh
                            </button>
                        </div>
                        <p class="m-0 text-xs text-slate-400">Hỗ trợ PNG, JPG, WEBP.</p>
                    </div>
                    @error('avatar')
                        <p class="{{ $errorClass }} text-center">{{ $message }}</p>
                    @enderror
                </section>

                {{-- ─── Cột phải: Thông tin người dùng ─── --}}
                <section class="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm flex flex-col gap-6">
                    <div>
                        <h2 class="m-0 text-base font-semibold text-slate-950">Thông tin người dùng</h2>
                        <p class="m-0 mt-1 text-sm text-slate-500">Điền đầy đủ thông tin để tạo người dùng mới. Các trường có dấu <span class="text-rose-500">*</span> là bắt buộc.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Họ tên --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="username" class="{{ $labelClass }}">Họ tên <span class="text-rose-500">*</span></label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}"
                                placeholder="Nguyễn Văn A" class="{{ $fieldClass }}" required>
                            @error('username')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Giới tính --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="gender" class="{{ $labelClass }}">Giới tính</label>
                            <div class="relative">
                                <select name="gender" id="gender" class="{{ $fieldClass }} appearance-none pr-10">
                                    <option value="">Chọn giới tính</option>
                                    <option value="nam" @selected(old('gender') === 'nam')>Nam</option>
                                    <option value="nu" @selected(old('gender') === 'nu')>Nữ</option>
                                    <option value="khac" @selected(old('gender') === 'khac')>Khác</option>
                                </select>
                                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                            @error('gender')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Số điện thoại --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="phone_number" class="{{ $labelClass }}">Số điện thoại</label>
                            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"
                                placeholder="0901234567" class="{{ $fieldClass }}">
                            @error('phone_number')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="{{ $labelClass }}">Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                placeholder="name@company.com" class="{{ $fieldClass }}" required>
                            @error('email')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Vai trò --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="role_id" class="{{ $labelClass }}">Vai trò <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="role_id" id="role_id" class="{{ $fieldClass }} appearance-none pr-10" required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected((string) old('role_id', $roles->first()?->id) === (string) $role->id)>
                                            {{ $roleLabels[$role->name] ?? $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                            @error('role_id')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Trạng thái --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="is_active" class="{{ $labelClass }}">Trạng thái</label>
                            <div class="relative">
                                <select name="is_active" id="is_active" class="{{ $fieldClass }} appearance-none pr-10">
                                    <option value="1" @selected(old('is_active', '1') === '1')>Hoạt động</option>
                                    <option value="0" @selected(old('is_active') === '0')>Ngưng hoạt động</option>
                                </select>
                                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                            @error('is_active')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Mật khẩu --}}
                        <div class="flex flex-col gap-1.5">
                            <label for="password" class="{{ $labelClass }}">Mật khẩu <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" id="password"
                                    placeholder="Nhập mật khẩu" class="{{ $fieldClass }} pr-11" required>
                                <button type="button"
                                    class="pw-toggle absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-400 transition-colors hover:text-slate-700"
                                    data-target="password" title="Hiện / Ẩn mật khẩu" tabindex="-1">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- ─── Địa chỉ (thay cho Khoa/Ngành/Lớp) ─── --}}
                    <div class="border-t border-slate-100 pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label for="city" class="{{ $labelClass }}">Tỉnh, Thành phố</label>
                                <div class="relative">
                                    <select name="city" id="city" class="{{ $fieldClass }} appearance-none pr-10"
                                        data-selected="{{ old('city') }}">
                                        <option value="">Chọn tỉnh / thành phố</option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                </div>
                                @error('city')
                                    <p class="{{ $errorClass }}">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="ward" class="{{ $labelClass }}">Phường, Xã</label>
                                <div class="relative">
                                    <select name="ward" id="ward" class="{{ $fieldClass }} appearance-none pr-10 disabled:bg-slate-50 disabled:text-slate-400"
                                        data-selected="{{ old('ward') }}" disabled>
                                        <option value="">Chọn tỉnh / thành phố trước</option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                </div>
                                @error('ward')
                                    <p class="{{ $errorClass }}">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="apartment_number" class="{{ $labelClass }}">Số nhà / Tên đường</label>
                                <input type="text" name="apartment_number" id="apartment_number"
                                    value="{{ old('apartment_number') }}" placeholder="Ví dụ: 12 Nguyễn Huệ"
                                    class="{{ $fieldClass }}">
                                @error('apartment_number')
                                    <p class="{{ $errorClass }}">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ─── Footer: Reset / Hủy / Lưu ─── --}}
                    <div class="border-t border-slate-100 pt-5 flex items-center justify-between gap-3">
                        <button type="reset"
                            class="text-sm font-semibold text-slate-500 transition-colors hover:text-slate-800">
                            Reset
                        </button>
                        <div class="flex items-center gap-2">
                            <a href="{{ route($routeBase.'.list') }}"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50">
                                Hủy
                            </a>
                            <button type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/30">
                                Lưu
                            </button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
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
        if (icon) icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    });
});

// Nạp Tỉnh/Thành phố từ API địa giới (v2, 2 cấp) và cascade Phường/Xã theo Tỉnh.
(function () {
    const citySelect = document.getElementById('city');
    const wardSelect = document.getElementById('ward');
    if (!citySelect) return;

    const wardsUrl = '{{ url('api/location/provinces') }}'; // + /{code}/wards

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function selectByName(select, name) {
        const match = Array.from(select.options).find(opt => opt.value === name);
        if (match) { select.value = name; return true; }
        return false;
    }

    function resetWard(placeholder) {
        if (!wardSelect) return;
        wardSelect.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>`;
        wardSelect.disabled = true;
    }

    // Nạp Phường/Xã theo mã Tỉnh; giữ lại giá trị cần chọn (nếu có).
    async function loadWards(provinceCode, keepWard) {
        if (!wardSelect || !provinceCode) { resetWard('Chọn tỉnh / thành phố trước'); return; }
        wardSelect.innerHTML = '<option value="">Đang tải...</option>';
        wardSelect.disabled = true;
        try {
            const res = await fetch(`${wardsUrl}/${provinceCode}/wards`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            wardSelect.innerHTML = '<option value="">Chọn phường / xã</option>' + data.map(item =>
                `<option value="${escapeHtml(item.name)}">${escapeHtml(item.name)}</option>`
            ).join('');
            wardSelect.disabled = false;
            if (keepWard && ! selectByName(wardSelect, keepWard)) {
                wardSelect.insertAdjacentHTML('beforeend', `<option value="${escapeHtml(keepWard)}" selected>${escapeHtml(keepWard)}</option>`);
                wardSelect.value = keepWard;
            }
        } catch {
            resetWard('Không tải được phường / xã');
            if (keepWard) {
                wardSelect.insertAdjacentHTML('beforeend', `<option value="${escapeHtml(keepWard)}" selected>${escapeHtml(keepWard)}</option>`);
                wardSelect.disabled = false;
            }
        }
    }

    function currentProvinceCode() {
        return citySelect.selectedOptions[0]?.dataset.code || '';
    }

    citySelect.addEventListener('change', function () {
        loadWards(currentProvinceCode(), null);
    });

    document.addEventListener('DOMContentLoaded', async function () {
        const oldCity = citySelect.dataset.selected || '';
        const oldWard = wardSelect?.dataset.selected || '';
        try {
            const res = await fetch('{{ route('location.provinces') }}', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            citySelect.insertAdjacentHTML('beforeend', data.map(item =>
                `<option value="${escapeHtml(item.name)}" data-code="${item.code}">${escapeHtml(item.name)}</option>`
            ).join(''));
        } catch {
            // API lỗi: giữ lại giá trị cũ (nếu có) để không mất dữ liệu đã nhập.
            if (oldCity) {
                citySelect.insertAdjacentHTML('beforeend', `<option value="${escapeHtml(oldCity)}">${escapeHtml(oldCity)}</option>`);
            }
        }

        if (oldCity && ! selectByName(citySelect, oldCity)) {
            // Giá trị cũ không có trong danh sách API — thêm vào để không mất.
            citySelect.insertAdjacentHTML('beforeend', `<option value="${escapeHtml(oldCity)}" selected>${escapeHtml(oldCity)}</option>`);
            citySelect.value = oldCity;
        }

        // Nếu đã chọn sẵn Tỉnh (submit lỗi quay lại) → nạp Phường/Xã và giữ giá trị cũ.
        if (oldCity) {
            loadWards(currentProvinceCode(), oldWard);
        }
    });
})();

// Ảnh đại diện: chọn file → xem trước; xóa → về ảnh mặc định.
(function () {
    const input       = document.getElementById('avatar');
    const preview     = document.getElementById('avatarPreview');
    const placeholder = document.getElementById('avatarPlaceholder');
    const chooseBtn   = document.querySelector('[data-avatar-choose]');
    const removeBtn   = document.querySelector('[data-avatar-remove]');
    if (!input || !preview || !placeholder) return;

    let objectUrl = null;

    function showPreview(src) {
        preview.src = src;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        if (removeBtn) removeBtn.disabled = false;
    }

    function clearPreview() {
        if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; }
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        input.value = '';
        if (removeBtn) removeBtn.disabled = true;
    }

    chooseBtn?.addEventListener('click', () => input.click());

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) { clearPreview(); return; }
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        showPreview(objectUrl);
    });

    removeBtn?.addEventListener('click', clearPreview);
})();
</script>
@endpush
