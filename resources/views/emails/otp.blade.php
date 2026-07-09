<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Mã xác thực OTP - HK STORE</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f5; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5; padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#000000; padding:20px 32px;">
                            <span style="color:#ffffff; font-size:20px; font-weight:700; letter-spacing:.08em;">HK STORE</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 16px; color:#111111; font-size:20px;">Yêu cầu đặt lại mật khẩu</h2>
                            <p style="margin:0 0 16px; color:#444444; font-size:15px; line-height:1.6;">
                                Xin chào,<br>
                                Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại HK STORE.
                                Vui lòng sử dụng mã xác thực (OTP) bên dưới để tiếp tục:
                            </p>
                            <div style="margin:24px 0; text-align:center;">
                                <span style="display:inline-block; padding:14px 28px; background-color:#f4f4f5; border:1px dashed #111111; border-radius:6px; font-size:32px; font-weight:800; letter-spacing:.3em; color:#111111;">
                                    {{ $otp }}
                                </span>
                            </div>
                            <p style="margin:0 0 8px; color:#444444; font-size:14px; line-height:1.6;">
                                Mã có hiệu lực trong <strong>{{ $expireMinutes }} phút</strong> kể từ thời điểm nhận được email này.
                            </p>
                            <p style="margin:0; color:#888888; font-size:13px; line-height:1.6;">
                                Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email và không chia sẻ mã OTP cho bất kỳ ai để bảo vệ tài khoản của bạn.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; background-color:#fafafa; border-top:1px solid #e5e7eb;">
                            <p style="margin:0; color:#999999; font-size:12px;">© {{ date('Y') }} HK STORE. Email được gửi tự động, vui lòng không phản hồi.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
