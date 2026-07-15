<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Đăng ký tài khoản thành công - HK STORE</title>
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
                            <h2 style="margin:0 0 16px; color:#111111; font-size:20px;">Đăng ký tài khoản thành công</h2>
                            <p style="margin:0 0 16px; color:#444444; font-size:15px; line-height:1.6;">
                                Xin chào <strong>{{ $user->username }}</strong>,<br>
                                Tài khoản của bạn tại HK STORE đã được tạo thành công với email <strong>{{ $user->email }}</strong>.
                                Bạn có thể đăng nhập ngay để bắt đầu mua sắm.  Đường dẫn tới trang đăng nhập: https://cloth-app-5cmsj.ondigitalocean.app/login
                               
                            </p>
                            <p style="margin:0; color:#888888; font-size:13px; line-height:1.6;">
                                Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi để được hỗ trợ.
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
