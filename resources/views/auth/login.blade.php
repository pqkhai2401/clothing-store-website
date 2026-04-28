<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ 'Đăng nhập' }}</title>
    <link href="{{ asset('assets/css/font-awesome.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/adminlte.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/login.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/source-sans-3@5.0.12.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/overlayscrollbars.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">
    <style>
        .input-group #togglePassword {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            border: none;
            background: transparent;
            padding: 0;
        }

        .input-group #togglePassword:hover,
        .input-group #togglePassword:focus {
            background: transparent;
            box-shadow: none;
        }

        .input-group #togglePassword i {
            color: #6c757d;
        }

        .input-group .form-floating input[type="password"],
        .input-group .form-floating input[type="text"] {
            padding-right: 40px;
        }

        /* Remove input group styling */
        .input-group {
            position: relative;
        }

        .input-group>.form-floating {
            flex: 1 1 auto;
            width: 1%;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            margin: 0 auto;
            object-fit: contain;
        }
    </style>
</head>

<body>
    <div class="container">

        <x-notification />

        <div class="row justify-content-center align-items-center">
            <div class="col-md-3">
                <img src="{{ asset('assets/img/AdminLTELogo.png') }}" alt="Not found" class="logo">
            </div>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <form id="loginForm" action="{{ route('auth.login') }}" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <div class="form-floating flex-grow-1">
                            <input type="user_name" class="form-control" id="user_name" name="user_name"
                                value="{{ old('user_name') }}" placeholder="{{ "Nhập username.." }}" required>
                            <label for="user_name">{{ "Username" }}</label>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input style="border-radius: 5px;" type="password" class="form-control" id="password"
                                name="password" placeholder="{{ "Nhập mật khẩu.." }}" required>
                            <label for="password">{{ "Password" }}</label>
                        </div>
                        <button class="btn px-2" type="button" id="togglePassword">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <div class="d-grid">
                        <div class="mb-3 d-flex justify-content-between">
                            <a href="{{ url('forgot-password') }}">{{ "Quên mật khẩu" }}</a>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ "Đăng nhập" }}</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</body>
@include('auth.script')

<script>
    $(document).ready(function () {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const toggleIcon = document.querySelector('#toggleIcon');

        togglePassword.addEventListener('click', function () {
            // Toggle password visibility
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle icon
            toggleIcon.classList.toggle('fa-eye');
            toggleIcon.classList.toggle('fa-eye-slash');
        });
    });
</script>

</html>