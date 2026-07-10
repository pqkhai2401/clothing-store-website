<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự hủy đơn PayOS chưa thanh toán đã quá hạn (cần chạy `php artisan schedule:run` định kỳ).
Schedule::command('orders:expire-stale-payos')->everyFiveMinutes();
