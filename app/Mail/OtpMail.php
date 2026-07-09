<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    // Mã OTP sẽ gửi cho khách hàng
    public string $otp;

    // Số phút hiệu lực của mã OTP (hiển thị trong nội dung email)
    public int $expireMinutes;

    /**
     * Khởi tạo Mailable với mã OTP và thời gian hết hạn.
     */
    public function __construct(string $otp, int $expireMinutes = 5)
    {
        $this->otp = $otp;
        $this->expireMinutes = $expireMinutes;
    }

    /**
     * Cấu hình tiêu đề (subject) và người gửi của email.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'HK STORE - Mã xác thực OTP đặt lại mật khẩu',
        );
    }

    /**
     * Cấu hình giao diện (view) nội dung email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'expireMinutes' => $this->expireMinutes,
            ],
        );
    }
}
