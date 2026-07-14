<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Controller đóng vai trò "proxy" gọi tới API địa giới hành chính Việt Nam
 * (https://provinces.open-api.vn/). Toàn bộ request từ trình duyệt sẽ được
 * gửi tới đây thay vì gọi trực tiếp ra domain bên thứ ba, giúp:
 * - Tránh lỗi CORS khi frontend gọi thẳng sang domain khác.
 * - Ẩn domain bên thứ ba khỏi frontend, dễ đổi provider sau này.
 * - Cache lại kết quả để giảm số lần gọi ra ngoài, web chạy nhanh và ổn định hơn.
 */
class LocationController extends Controller
{
    /**
     * Thời gian cache (giây). Dữ liệu địa giới hành chính rất ít thay đổi
     * nên có thể cache dài (ở đây là 1 ngày).
     */
    private const CACHE_TTL = 86400;

    /**
     * Lấy danh sách toàn bộ Tỉnh/Thành phố.
     * GET /api/location/provinces
     *
     * Dùng API v2 — dữ liệu địa giới 2 cấp (Tỉnh → Phường/Xã) theo cơ cấu hành
     * chính mới sau sáp nhập 2025, không còn cấp Quận/Huyện. Nhờ vậy có thể nạp
     * Phường/Xã trực tiếp theo Tỉnh (xem wards()).
     */
    public function provinces(): JsonResponse
    {
        $data = $this->rememberIfNotEmpty('location.provinces.v2', function () {
            return $this->fetch('https://provinces.open-api.vn/api/v2/p/');
        });

        return response()->json($data);
    }

    /**
     * Lấy danh sách Phường/Xã thuộc một Tỉnh/Thành phố.
     * GET /api/location/provinces/{code}/wards
     */
    public function wards(int $code): JsonResponse
    {
        $data = $this->rememberIfNotEmpty("location.wards.v2.{$code}", function () use ($code) {
            $province = $this->fetch("https://provinces.open-api.vn/api/v2/p/{$code}", ['depth' => 2]);

            return $province['wards'] ?? [];
        });

        return response()->json($data);
    }


    /**
     * Giống Cache::remember, nhưng KHÔNG lưu vào cache nếu kết quả rỗng.
     * Lý do: nếu API bên thứ ba tạm thời lỗi (mất mạng, timeout, SSL...), hàm fetch()
     * trả về mảng rỗng — nếu cache luôn mảng rỗng đó thì lỗi tạm thời sẽ bị "đóng băng"
     * và toàn bộ người dùng sau đó cũng nhận mảng rỗng dù API đã hoạt động lại bình thường.
     */
    private function rememberIfNotEmpty(string $key, callable $callback): array
    {
        $cached = Cache::get($key);
        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        $data = $callback();

        if (! empty($data)) {
            Cache::put($key, $data, self::CACHE_TTL);
        }

        return $data;
    }

    /**
     * Gọi API bên thứ ba và trả về dữ liệu dạng mảng, không để lỗi mạng/SSL/timeout
     * làm sập request của frontend — nếu lỗi thì trả về mảng rỗng để frontend tự xử lý.
     *
     * API provinces.open-api.vn đôi khi bị lỗi SSL/kết nối chập chờn theo từng request
     * (do phía server luân chuyển qua nhiều node khác nhau), nên tự động thử lại tối đa
     * 3 lần, mỗi lần cách nhau 200ms trước khi thực sự coi là thất bại.
     *
     * @param  array<string, mixed>  $query
     */
    private function fetch(string $url, array $query = [], ?string $key = null): array
    {
        try {
            $http = Http::timeout(10)->retry(3, 200);

            $caBundle = storage_path('certs/cacert.pem');
            if (is_file($caBundle)) {
                $http = $http->withOptions(['verify' => $caBundle]);
            }

            $response = $http->get($url, $query);

            if (! $response->successful()) {
                return [];
            }

            return $key ? $response->json($key, []) : $response->json();
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }
}
