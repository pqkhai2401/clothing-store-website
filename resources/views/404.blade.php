<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Trang không tìm thấy</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.min.css') }}">

    <style>
        body {
            background: #0284c7;
            min-height: 100vh;
            font-family: 'Open Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: fadeInUp 0.6s ease-out;
        }

        .error-code {
            font-size: 120px;
            font-weight: 800;
            background: #0284c7;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            line-height: 1;
        }

        .error-title {
            font-size: 32px;
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 15px;
        }

        .error-message {
            font-size: 18px;
            color: #636e72;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .error-icon {
            font-size: 80px;
            color: #dfe6e9;
            margin-bottom: 30px;
            animation: bounce 2s infinite;
        }

        .btn-home {
            background: #0284c7;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-back {
            background: transparent;
            color: #0284c7;
            border: 2px solid #0284c7;
            padding: 13px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            margin-right: 15px;
        }

        .btn-back:hover {
            background: #0284c7;
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 40px 20px;
            }

            .error-code {
                font-size: 80px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-message {
                font-size: 16px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-back {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-search"></i>
        </div>

        <div class="error-code">404</div>

        <h1 class="error-title">Trang không tìm thấy</h1>

        <p class="error-message">
            Rất tiếc! Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.<br>
            Vui lòng kiểm tra lại URL hoặc quay về trang chủ.
        </p>

        <div class="action-buttons">
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
            <a href="/" class="btn-home">
                <i class="fas fa-home"></i>
                Về trang chủ
            </a>
        </div>
    </div>
</body>

</html>
