@extends('layouts.app')
@section('title', 'Tạo người dùng')

@section('css')
    <style>
        .input-group .btn-toggle-password {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-left: none;
            color: #6c757d;
            padding: 0.375rem 0.75rem;
            transition: all 0.2s ease;
            z-index: 5;
        }

        .input-group .btn-toggle-password:hover {
            background-color: #e9ecef;
            color: #0284c7;
        }

        .input-group .btn-toggle-password:active {
            background-color: #dee2e6;
        }

        .input-group .btn-toggle-password i {
            font-size: 0.875rem;
            width: 1rem;
            text-align: center;
        }

        .input-group .form-control:focus+.btn-toggle-password,
        .input-group .form-control:focus~.btn-toggle-password {
            border-color: #86b7fe;
        }

        .input-group .form-control.is-invalid~.btn-toggle-password {
            border-color: #dc3545;
        }

        .input-group .form-control.is-valid~.btn-toggle-password {
            border-color: #198754;
        }
    </style>
@endsection

@section('content')
    <main class="app-main container">

        <x-notification />

        <div class="container-fluid my-4">
            <div class="card form-card">
                <div class="form-hero px-5 pt-4 pb-3">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="status-badge status-info mb-3">
                                <i class="fa-solid fa-user-plus"></i>
                                <span>Tạo người dùng</span>
                            </div>
                            <h4 class="mb-1 fw-bold">Thiết lập hồ sơ người dùng</h4>
                            <p class="form-subtitle mb-0">
                                Điền đầy đủ thông tin để tạo mới hồ sơ người dùng trong hệ thống.
                            </p>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off" autocorrect="off"
                    autocapitalize="off" spellcheck="false" class="needs-validation" novalidate>

                    @csrf

                    <div class="form-body px-5 pt-4 pb-4 bg-white">
                        <div class="row g-3 form-grid">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center" style="color: #2d3436;">
                                        <span class="bg-primary rounded-pill me-2"
                                            style="width: 6px; height: 22px; display: inline-block; box-shadow: 2px 0 5px rgba(13, 110, 253, 0.2);"></span>
                                        <i class="fa-solid fa-address-card me-2 text-primary"></i>
                                        Thông tin người dùng
                                    </h6>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Họ và tên: <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    placeholder="Nhập họ và tên" />
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">
                                    Vai trò: <span class="text-danger">*</span>
                                </label>
                                <select name="role" id="role" class="form-control">
                                    <option value="viewer" {{ old('role') == 'viewer' ? 'selected' : '' }}>
                                        Viewer
                                    </option>
                                    <option value="contributor" {{ old('role') == 'contributor' ? 'selected' : '' }}>
                                        Contributor
                                    </option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                                        Admin
                                    </option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="user_name" class="form-label">
                                    Tên đăng nhập: <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="user_name" id="user_name"
                                    class="form-control @error('user_name') is-invalid @enderror"
                                    value="{{ old('user_name') }}" placeholder="Nhập tên đăng nhập" />
                                @error('user_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="password" class="form-label">
                                    Mật khẩu: <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Nhập mật khẩu" />
                                    <button type="button" class="btn btn-toggle-password" id="togglePassword"
                                        title="Hiển thị mật khẩu">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="password_confirmation" class="form-label">
                                    Xác nhận mật khẩu: <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control" placeholder="Nhập lại mật khẩu" />
                                    <button type="button" class="btn btn-toggle-password" id="toggleConfirmPassword"
                                        title="Hiển thị mật khẩu">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer px-5 pt-3 pb-3">
                        <a href="{{ url()->previous() }}" class="btn btn-light border fw-semibold text-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    @push('scripts')
        <script>
            $(document).ready(function () {
                // Add custom method for username pattern
                $.validator.addMethod('usernamePattern', function (value, element) {
                    return this.optional(element) || /^[a-zA-Z0-9_]+$/.test(value);
                }, 'Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới');

                // Initialize form validation
                $("form.needs-validation").validate({
                    // Validation rules
                    rules: {
                        name: {
                            required: true,
                            minlength: 2,
                            maxlength: 255
                        },
                        user_name: {
                            required: true,
                            minlength: 3,
                            maxlength: 50,
                            usernamePattern: true
                        },
                        password: {
                            required: true,
                            minlength: 6,
                            maxlength: 255
                        },
                        password_confirmation: {
                            required: true,
                            equalTo: '#password'
                        },
                        role: {
                            required: true
                        }
                    },

                    // Custom error messages
                    messages: {
                        name: {
                            required: "Vui lòng nhập họ và tên",
                            minlength: "Họ và tên phải có ít nhất 2 ký tự",
                            maxlength: "Họ và tên không được vượt quá 255 ký tự"
                        },
                        user_name: {
                            required: "Vui lòng nhập tên đăng nhập",
                            minlength: "Tên đăng nhập phải có ít nhất 3 ký tự",
                            maxlength: "Tên đăng nhập không được vượt quá 50 ký tự"
                        },
                        password: {
                            required: "Vui lòng nhập mật khẩu",
                            minlength: "Mật khẩu phải có ít nhất 6 ký tự",
                            maxlength: "Mật khẩu không được vượt quá 255 ký tự"
                        },
                        password_confirmation: {
                            required: "Vui lòng xác nhận mật khẩu",
                            equalTo: "Mật khẩu xác nhận không khớp"
                        },
                        role: {
                            required: "Vui lòng chọn vai trò"
                        }
                    },

                    // Error element configuration
                    errorElement: 'div',
                    errorClass: 'invalid-feedback',
                    validClass: 'is-valid',

                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback d-block');

                        if (element.prop('type') === 'checkbox') {
                            error.insertAfter(element.parent());
                        } else if (element.hasClass('form-select') || element.hasClass(
                            'form-control')) {
                            if (element.parent().hasClass('input-group')) {
                                error.insertAfter(element.parent());
                            } else {
                                error.insertAfter(element);
                            }
                        } else {
                            error.insertAfter(element);
                        }
                    },

                    // Highlight invalid fields
                    highlight: function (element, errorClass, validClass) {
                        $(element).addClass('is-invalid').removeClass('is-valid');

                        // Handle input groups (like password field)
                        if ($(element).parent().hasClass('input-group')) {
                            $(element).parent().addClass('is-invalid');
                        }
                    },

                    // Unhighlight valid fields
                    unhighlight: function (element, errorClass, validClass) {
                        $(element).removeClass('is-invalid');

                        // Only add is-valid class if the field has a value
                        if ($(element).val() && $(element).val().trim() !== '') {
                            $(element).addClass('is-valid');
                        } else {
                            $(element).removeClass('is-valid');
                        }
                        if ($(element).parent().hasClass('input-group')) {
                            $(element).parent().removeClass('is-invalid');
                        }
                    },

                    // Form submission handler
                    submitHandler: function (form) {
                        // Show loading state
                        const $submitButton = $(form).find('button[type="submit"]');
                        const originalText = $submitButton.html();

                        $submitButton.html(
                            '<i class="btn-primary fas fa-spinner fa-spin me-1"></i>Đang xử lý...'
                        );
                        $submitButton.prop('disabled', true);

                        // Submit the form
                        form.submit();
                    },

                    // Validate on various events
                    onkeyup: function (element) {
                        // Validate on keyup for better UX
                        $(element).valid();
                    },

                    onfocusout: function (element) {
                        // Validate when user leaves the field
                        $(element).valid();
                    },

                    onclick: function (element) {
                        // Validate checkboxes and radio buttons on click
                        if (element.type === 'checkbox' || element.type === 'radio') {
                            $(element).valid();
                        }
                    }
                });

                // Toggle password visibility
                $('#togglePassword').on('click', function () {
                    const passwordInput = $('#password');
                    const icon = $(this).find('i');

                    if (passwordInput.attr('type') === 'password') {
                        passwordInput.attr('type', 'text');
                        icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    } else {
                        passwordInput.attr('type', 'password');
                        icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    }
                });

                // Toggle confirm password visibility
                $('#toggleConfirmPassword').on('click', function () {
                    const passwordInput = $('#password_confirmation');
                    const icon = $(this).find('i');

                    if (passwordInput.attr('type') === 'password') {
                        passwordInput.attr('type', 'text');
                        icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    } else {
                        passwordInput.attr('type', 'password');
                        icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    }
                });
            });
        </script>
    @endpush
@endsection