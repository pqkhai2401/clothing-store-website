<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Events\AuditCustom;

/**
 * Ghi nhật ký (audit) cho sự kiện đăng nhập / đăng xuất của nhân sự quản trị.
 *
 * Package owen-it/laravel-auditing chỉ tự động ghi log cho các sự kiện Eloquent
 * (created/updated/deleted/restored). Đăng nhập/đăng xuất không gắn với thay đổi
 * model nên phải phát AuditCustom thủ công tại đây.
 */
class LogAuthenticationActivity
{
    /**
     * Chống ghi trùng trong cùng một request.
     *
     * Luồng đăng nhập của dự án bắn sự kiện Login hai lần (một lần từ
     * Auth::attempt, một lần từ sub-request OAuth password-grant), nên
     * cần khử trùng theo "event:userId" trong vòng đời tiến trình.
     *
     * @var array<string, bool>
     */
    protected static array $recorded = [];

    public function handleLogin(Login $event): void
    {
        $this->record($event->user, 'login', 'Đăng nhập hệ thống quản trị');
    }

    public function handleLogout(Logout $event): void
    {
        $this->record($event->user, 'logout', 'Đăng xuất khỏi hệ thống quản trị');
    }

    /**
     * Chỉ ghi log cho tài khoản có quyền truy cập trang quản trị
     * (bỏ qua đăng nhập của khách hàng để nhật ký làm việc gọn gàng).
     */
    protected function record($user, string $event, string $message): void
    {
        if (! $user instanceof Auditable) {
            return;
        }

        if (! method_exists($user, 'can') || ! $user->can('access-admin')) {
            return;
        }

        $key = $event . ':' . $user->getKey();
        if (isset(self::$recorded[$key])) {
            return;
        }
        self::$recorded[$key] = true;

        $user->auditEvent = $event;
        $user->isCustomEvent = true;
        $user->auditCustomOld = [];
        $user->auditCustomNew = ['message' => $message];

        Event::dispatch(new AuditCustom($user));
    }
}