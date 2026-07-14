<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetByAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $performedByUsername)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mật khẩu của bạn vừa được đặt lại')
            ->greeting('Xin chào '.$notifiable->username.',')
            ->line('Quản trị viên "'.$this->performedByUsername.'" vừa đặt lại mật khẩu cho tài khoản của bạn.')
            ->line('Bạn sẽ được yêu cầu đổi mật khẩu ngay khi đăng nhập lần tới.')
            ->line('Nếu bạn không yêu cầu việc này, vui lòng liên hệ quản trị viên ngay lập tức.')
            ->action('Đăng nhập', route('auth.loginpage'));
    }
}
