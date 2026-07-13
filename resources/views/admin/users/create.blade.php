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
        $cityOptions = ['Hà Nội', 'TP. Hồ Chí Minh', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng'];
        $wardOptions = ['Phường Bến Nghé', 'Phường Bến Thành', 'Phường Nguyễn Thái Bình', 'Phường Tân Định', 'Phường Cầu Ông Lãnh'];
    @endphp

    <div class="min-h-screen bg-slate-50 pb-32">
        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <x-notification />

            <div class="mb-6">
                <h1 class="account-header-title mb-2" style="color: black;">{{ $pageHeading }}</h1>
                <p class="account-header-desc mb-0" style="color: #64748b;">Tạo tài khoản nội bộ với thông tin nhân sự, vai trò và địa chỉ liên hệ.</p>
            </div>

            <form method="POST" action="{{ route($routeBase.'.store') }}" autocomplete="off" class="space-y-5">
                @csrf
                <input type="hidden" name="is_active" value="1">

                <section class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm flex flex-col gap-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="m-0 text-base font-semibold text-slate-950">Thông tin cá nhân</h2>
                            <p class="m-0 mt-1 text-sm text-slate-500">Thông tin định danh dùng trong hồ sơ quản trị.</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <i class="fa-regular fa-user"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="username" class="{{ $labelClass }}">Họ và Tên</label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}"
                                placeholder="Ví dụ: Nguyễn Văn A" class="{{ $fieldClass }}" required>
                            @error('username')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="phone_number" class="{{ $labelClass }}">Số Điện Thoại</label>
                            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"
                                placeholder="Ví dụ: 0901234567" class="{{ $fieldClass }}">
                            @error('phone_number')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-1.5 md:col-span-2">
                            <label for="email" class="{{ $labelClass }}">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                placeholder="name@company.com" class="{{ $fieldClass }}" required>
                            @error('email')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm flex flex-col gap-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="m-0 text-base font-semibold text-slate-950">Bảo mật &amp; Phân quyền</h2>
                            <p class="m-0 mt-1 text-sm text-slate-500">Thiết lập mật khẩu đăng nhập và phạm vi quyền hạn.</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="password" class="{{ $labelClass }}">Mật Khẩu</label>
                            <div class="relative">
                                <input type="password" name="password" id="password"
                                    placeholder="Tối thiểu 6 ký tự" class="{{ $fieldClass }} pr-11" required>
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

                        <div class="flex flex-col gap-1.5">
                            <label for="role_id" class="{{ $labelClass }}">Vai Trò</label>
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

                    </div>
                </section>

                <section class="bg-white border border-slate-100 rounded-xl p-6 shadow-sm flex flex-col gap-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="m-0 text-base font-semibold text-slate-950">Địa chỉ liên hệ</h2>
                            <p class="m-0 mt-1 text-sm text-slate-500">Thông tin hỗ trợ liên hệ nội bộ và giao nhận hồ sơ.</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="city" class="{{ $labelClass }}">Tỉnh, Thành Phố</label>
                            <div class="relative">
                                <select name="city" id="city" class="{{ $fieldClass }} appearance-none pr-10">
                                    <option value="">Chọn tỉnh / thành phố</option>
                                    @if(old('city') && !in_array(old('city'), $cityOptions, true))
                                        <option value="{{ old('city') }}" selected>{{ old('city') }}</option>
                                    @endif
                                    @foreach ($cityOptions as $city)
                                        <option value="{{ $city }}" @selected(old('city') === $city)>{{ $city }}</option>
                                    @endforeach
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
                                <select name="ward" id="ward" class="{{ $fieldClass }} appearance-none pr-10">
                                    <option value="">Chọn phường / xã</option>
                                    @if(old('ward') && !in_array(old('ward'), $wardOptions, true))
                                        <option value="{{ old('ward') }}" selected>{{ old('ward') }}</option>
                                    @endif
                                    @foreach ($wardOptions as $ward)
                                        <option value="{{ $ward }}" @selected(old('ward') === $ward)>{{ $ward }}</option>
                                    @endforeach
                                </select>
                                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                            @error('ward')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="apartment_number" class="{{ $labelClass }}">Số Nhà / Tên Đường</label>
                            <input type="text" name="apartment_number" id="apartment_number"
                                value="{{ old('apartment_number') }}" placeholder="Ví dụ: 12 Nguyễn Huệ"
                                class="{{ $fieldClass }}">
                            @error('apartment_number')
                                <p class="{{ $errorClass }}">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <div class="fixed bottom-0 left-0 right-0 z-[1030] bg-white border-t border-slate-100 shadow-[0_-4px_12px_rgba(0,0,0,0.03)] py-4 px-8 flex flex-col gap-3 md:flex-row md:justify-between md:items-center">
                    <p class="m-0 text-sm text-slate-500">* Vui lòng điền chính xác thông tin nhân sự.</p>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <a href="{{ route($routeBase.'.list') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:text-slate-950">
                            ← Hủy
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            💾 {{ $submitLabel }}
                        </button>
                    </div>
                </div>
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
</script>
@endpush
