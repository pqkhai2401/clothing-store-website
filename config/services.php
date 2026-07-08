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
