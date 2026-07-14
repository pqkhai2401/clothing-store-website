<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    'payos' => [
        'client_id' => env('PAYOS_CLIENT_ID'),
        'api_key' => env('PAYOS_API_KEY'),
        'checksum_key' => env('PAYOS_CHECKSUM_KEY'),
        'base_url' => env('PAYOS_BASE_URL', 'https://api-merchant.payos.vn'),
        'return_url' => env('PAYOS_RETURN_URL', env('APP_URL').'/checkout/payos/return'),
        'cancel_url' => env('PAYOS_CANCEL_URL', env('APP_URL').'/checkout/payos/cancel'),
        // Chỉ đặt false ở môi trường local nếu PHP chưa có CA bundle (lỗi cURL 60).
        'verify_ssl' => env('PAYOS_VERIFY_SSL', true),
    ],

    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE'),
        'access_key'   => env('MOMO_ACCESS_KEY'),
        'secret_key'   => env('MOMO_SECRET_KEY'),
        // MOMO_ENDPOINT trỏ tới .../api/create; URL query suy ra bằng cách đổi 'create' -> 'query'.
        'endpoint'     => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'redirect_url' => env('MOMO_REDIRECT_URL', env('APP_URL').'/checkout/momo-return'),
        'ipn_url'      => env('MOMO_IPN_URL', env('APP_URL').'/api/payment/momo/ipn'),
        'request_type' => env('MOMO_REQUEST_TYPE', 'captureWallet'),
        // Đặt false ở local nếu PHP chưa có CA bundle (lỗi cURL 60), như PayOS.
        'verify_ssl'   => env('MOMO_VERIFY_SSL', false),
    ],
    /*
    |--------------------------------------------------------------------------
    | Google Gemini API (Kiểm duyệt đánh giá bằng AI)
    |--------------------------------------------------------------------------
    | - key   : API key lấy từ Google AI Studio (đặt trong .env: GEMINI_API_KEY).
    | - model : Dòng mô hình sử dụng; mặc định 'gemini-1.5-flash' vì nhanh & rẻ.
    */
    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        // Chế độ Demo: true -> dùng bộ lọc từ khóa nội bộ (Fake AI) test nhanh
        // tại local, không gọi Google. false -> gọi Gemini API thật.
        'demo_mode' => env('GEMINI_DEMO_MODE', false),
    ],

];