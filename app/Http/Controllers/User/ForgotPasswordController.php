<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\AppBaseController;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class ForgotPasswordController extends AppBaseController
{
    // Số phút hiệu lực của mã OTP
    private const OTP_EXPIRE_MINUTES = 5;

    /**
     * Bước 1: Hiển thị form nhập Email để nhận mã OTP.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Bước 1: Xử lý gửi mã OTP qua Email.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['bail', 'required', 'email', 'max:255'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email của bạn.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only('email'));
        }

        $email = mb_strtolower(trim((string) $request->input('email')));

        // Kiểm tra email có tồn tại trong bảng users hay không
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email này chưa được đăng ký tài khoản tại HK STORE.']);
        }

        // Sinh mã OTP ngẫu nhiên gồm 6 chữ số (000000 - 999999)
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            // Gửi mã OTP qua email cho khách hàng
            Mail::to($email)->send(new OtpMail($otp, self::OTP_EXPIRE_MINUTES));
        } catch (Throwable $e) {
            Log::error('Gửi mail OTP quên mật khẩu thất bại: ' . $e->getMessage());

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Không thể gửi mã xác thực lúc này. Vui lòng thử lại sau.']);
        }

        // Lưu thông tin OTP tạm thời vào Session để dùng cho các bước tiếp theo
        $request->session()->put('forgot_password', [
            'email' => $email,
            'otp' => $otp,
            'expired_at' => Carbon::now()->addMinutes(self::OTP_EXPIRE_MINUTES),
            'verified' => false,
        ]);

        return redirect()
            ->route('auth.password.verify')
            ->with('success', 'Mã xác thực đã được gửi tới email của bạn.');
    }

    /**
     * Bước 2: Hiển thị form nhập mã OTP.
     */
    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        $data = $request->session()->get('forgot_password');

        // Nếu chưa gửi OTP (chưa có session) thì bắt buộc quay lại bước nhập email
        if (! $data) {
            return redirect()
                ->route('auth.password.request')
                ->withErrors(['email' => 'Vui lòng nhập email để nhận mã xác thực trước.']);
        }

        return view('auth.verify-otp', ['email' => $data['email']]);
    }

    /**
     * Bước 2: Xử lý xác thực mã OTP người dùng nhập vào.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->session()->get('forgot_password');

        if (! $data) {
            return redirect()
                ->route('auth.password.request')
                ->withErrors(['email' => 'Phiên xác thực đã hết hạn. Vui lòng thực hiện lại.']);
        }

        $validator = Validator::make($request->all(), [
            'otp' => ['bail', 'required', 'digits:6'],
        ], [
            'otp.required' => 'Vui lòng nhập mã OTP.',
            'otp.digits' => 'Mã OTP phải gồm đúng 6 chữ số.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Kiểm tra mã OTP đã hết hạn (quá 5 phút) hay chưa
        if (Carbon::now()->greaterThan(Carbon::parse($data['expired_at']))) {
            $request->session()->forget('forgot_password');

            return redirect()
                ->route('auth.password.request')
                ->withErrors(['email' => 'Mã OTP đã hết hạn. Vui lòng yêu cầu gửi lại mã mới.']);
        }

        // So sánh mã OTP người dùng nhập với mã đã lưu trong Session
        if ($request->input('otp') !== $data['otp']) {
            return back()->withErrors(['otp' => 'Mã OTP không chính xác. Vui lòng kiểm tra lại.']);
        }

        // Xác thực thành công: đánh dấu session để cho phép truy cập bước đặt lại mật khẩu
        $data['verified'] = true;
        $request->session()->put('forgot_password', $data);

        return redirect()
            ->route('auth.password.reset')
            ->with('success', 'Xác thực OTP thành công. Vui lòng đặt lại mật khẩu mới.');
    }

    /**
     * Bước 3: Hiển thị form đặt mật khẩu mới.
     */
    public function showResetForm(Request $request): View|RedirectResponse
    {
        $data = $request->session()->get('forgot_password');

        // Chỉ cho phép truy cập trang này khi đã xác thực OTP thành công
        if (! $data || empty($data['verified'])) {
            return redirect()
                ->route('auth.password.request')
                ->withErrors(['email' => 'Vui lòng xác thực mã OTP trước khi đặt lại mật khẩu.']);
        }

        return view('auth.reset-password');
    }

    /**
     * Bước 3: Xử lý cập nhật mật khẩu mới vào Database.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->session()->get('forgot_password');

        if (! $data || empty($data['verified'])) {
            return redirect()
                ->route('auth.password.request')
                ->withErrors(['email' => 'Phiên xác thực đã hết hạn. Vui lòng thực hiện lại từ đầu.']);
        }

        $validator = Validator::make($request->all(), [
            'password' => ['bail', 'required', 'string', 'min:8', 'max:64', 'confirmed'],
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá 64 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $user = User::where('email', $data['email'])->first();

            if (! $user) {
                return redirect()
                    ->route('auth.password.request')
                    ->withErrors(['email' => 'Tài khoản không tồn tại. Vui lòng thực hiện lại từ đầu.']);
            }

            // Mã hóa mật khẩu mới và lưu vào bảng users
            $user->password = Hash::make($request->input('password'));
            $user->save();
        } catch (Throwable $e) {
            Log::error('Cập nhật mật khẩu mới thất bại: ' . $e->getMessage());

            return back()->withErrors(['password' => 'Có lỗi xảy ra, vui lòng thử lại sau.']);
        }

        // Xóa toàn bộ session tạm thời của quy trình quên mật khẩu
        $request->session()->forget('forgot_password');

        return redirect()
            ->route('auth.loginpage')
            ->with('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.');
    }
}
