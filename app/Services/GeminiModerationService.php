<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * =============================================================================
 *  LỚP DỊCH VỤ GỌI GOOGLE GEMINI API ĐỂ KIỂM DUYỆT NỘI DUNG ĐÁNH GIÁ
 * =============================================================================
 *
 * Nhiệm vụ: Nhận vào nội dung bình luận (tiếng Việt) của khách hàng, gửi lên
 * Google Gemini để phân tích ngôn ngữ tự nhiên, sau đó trả về một mảng kết quả
 * đã được chuẩn hóa gồm:
 *      [
 *          'status'           => 'approved' | 'rejected' | 'flagged',
 *          'confidence_score' => 0..100,
 *          'reason'           => 'Lý do bằng tiếng Việt'
 *      ]
 *
 * Lưu ý kiến trúc: Service này CHỈ chịu trách nhiệm giao tiếp với API và trả về
 * dữ liệu sạch. Việc cập nhật vào Database do lớp Job (ProcessReviewModeration)
 * đảm nhiệm -> tuân theo nguyên tắc "Separation of Concerns".
 */
class GeminiModerationService
{
    /**
     * Endpoint gốc của Gemini API (chưa gồm API key).
     * Model được lấy động từ config để dễ thay đổi (mặc định gemini-2.0-flash).
     */
    protected string $endpoint;

    /**
     * API key đọc từ file cấu hình services.php -> .env (GEMINI_API_KEY).
     */
    protected ?string $apiKey;

    /**
     * Cờ bật CHẾ ĐỘ DEMO (Fake AI).
     * true  -> chạy bộ lọc từ khóa nội bộ tại local (không gọi Google).
     * false -> gọi Gemini API thật như thiết kế.
     */
    protected bool $demoMode;

    public function __construct()
    {
        // Mặc định 'gemini-2.0-flash': model flash mới, nhanh & rẻ, đang được
        // phục vụ trên endpoint v1 (model cũ 'gemini-1.5-flash' đã bị gỡ -> 404).
        $model          = config('services.gemini.model', 'gemini-flash-lite-latest');
        $this->apiKey   = config('services.gemini.key');
        // Ép về bool an toàn: các giá trị "true"/"1"/1/true đều thành true.
        $this->demoMode = filter_var(config('services.gemini.demo_mode', false), FILTER_VALIDATE_BOOLEAN);
        // Dùng v1beta: các model còn hạn ngạch free tier (flash-lite-latest, gemini-3.x)
        // chỉ phục vụ trên v1beta. Model cũ 'gemini-2.0-flash' đã bị free-tier = 0 -> 429.
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    /**
     * Hàm chính: Gửi bình luận lên Gemini và nhận về quyết định kiểm duyệt.
     *
     * @param  string  $comment  Nội dung bình luận cần kiểm duyệt.
     * @return array{status:string, confidence_score:int, reason:string}
     *
     * @throws RuntimeException Khi thiếu API key hoặc API trả về lỗi/không hợp lệ.
     */
    public function moderate(string $comment): array
    {
        // 0. NẾU BẬT CHẾ ĐỘ DEMO -> chạy bộ lọc từ khóa nội bộ (Fake AI) và dừng.
        //    Không gọi Google, không cần API key/quota -> test luồng cực nhanh.
        if ($this->demoMode) {
            return $this->moderateViaKeywords($comment);
        }

        // 0b. CACHE: cùng một nội dung bình luận thì quyết định kiểm duyệt luôn
        //     giống hệt -> lưu lại 7 ngày để không gọi Gemini lại (tiết kiệm quota,
        //     tránh 429, và xử lý tức thì khi gặp comment trùng / spam lặp lại).
        //     Khóa cache = md5 nội dung đã chuẩn hóa (bỏ khoảng trắng, hạ chữ thường).
        $cacheKey = 'ai_moderation_' . md5(mb_strtolower(trim($comment)));
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        // 1. Kiểm tra API key: nếu chưa cấu hình thì ném lỗi để Job bắt và
        //    chuyển review sang trạng thái 'flagged' (Admin duyệt tay).
        if (empty($this->apiKey)) {
            throw new RuntimeException('Chưa cấu hình GEMINI_API_KEY trong file .env');
        }

        // 2. Khởi tạo HTTP client.
        //    - timeout(20): tối đa chờ 20 giây để tránh treo hàng đợi.
        //    - retry(2, 500): thử lại tối đa 2 lần, mỗi lần cách nhau 500ms
        //      để phòng lỗi mạng chập chờn.
        $http = Http::timeout(20)->retry(2, 500);

        // 2b. CHỈ tắt kiểm tra chứng chỉ SSL khi chạy ở môi trường LOCAL.
        //     Lý do: local trên Windows (XAMPP/Laragon) thường thiếu bộ chứng chỉ
        //     CA nên gặp lỗi "SSL certificate problem". Trên production, server đã
        //     có CA hợp lệ -> BẮT BUỘC giữ xác thực SSL để chống tấn công
        //     man-in-the-middle (nếu tắt sẽ là lỗ hổng bảo mật nghiêm trọng).
        if (app()->environment('local')) {
            $http = $http->withoutVerifying();
        }

        // 3. Gửi request POST lên Gemini (API key truyền qua ?key=... theo chuẩn Google).
        $response = $http->post($this->endpoint . '?key=' . $this->apiKey, $this->buildPayload($comment));

        // 4. Nếu HTTP status không thành công (4xx/5xx) -> ném lỗi kèm nội dung
        //    để tiện ghi log và debug.
        if ($response->failed()) {
            Log::warning('Gemini API trả về lỗi HTTP', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new RuntimeException('Gemini API lỗi HTTP ' . $response->status());
        }

        // 5. Trích xuất chuỗi văn bản JSON mà Gemini sinh ra.
        //    Cấu trúc trả về của Gemini: candidates[0].content.parts[0].text
        $rawJson = $response->json('candidates.0.content.parts.0.text');

        if (empty($rawJson)) {
            throw new RuntimeException('Gemini không trả về nội dung (candidates rỗng).');
        }

        // 6. Parse chuỗi JSON đó thành mảng PHP và chuẩn hóa dữ liệu.
        $result = $this->normalizeResult($rawJson);

        // 7. Chỉ cache khi ĐÃ có kết quả hợp lệ. Các trường hợp lỗi ở trên đều
        //    ném exception nên không bao giờ bị cache -> lần sau vẫn gọi lại Gemini.
        Cache::put($cacheKey, $result, now()->addDays(7));

        return $result;
    }

    /**
     * =========================================================================
     *  CHẾ ĐỘ DEMO (FAKE AI) — bộ lọc từ khóa nội bộ chạy tại local.
     * =========================================================================
     *
     * Mô phỏng lại quyết định của AI mà KHÔNG gọi Google (không tốn quota),
     * phục vụ test nhanh và quay demo đồ án. Logic phân loại:
     *  1. rejected : bình luận chứa từ tục tĩu / công kích / spam quảng cáo / link.
     *  2. flagged  : bình luận quá ngắn hoặc mập mờ (độ tin cậy thấp).
     *  3. approved : còn lại (bình luận đánh giá bình thường).
     *
     * @return array{status:string, confidence_score:int, reason:string}
     */
    protected function moderateViaKeywords(string $comment): array
    {
        // Đưa về chữ thường để so khớp không phân biệt hoa/thường.
        $text = mb_strtolower(trim($comment));

        // ---- (1) Danh sách từ khóa VI PHẠM -> rejected ----
        // Gồm: chửi thề/tục tĩu, công kích, và dấu hiệu spam quảng cáo.
        $badWords = [
            // Tục tĩu / chửi thề (viết tránh, minh họa cho đồ án)
            'đm', 'dm', 'đmm', 'vl', 'vcl', 'cc', 'lồn', 'buồi', 'địt', 'đụ',
            'đéo', 'deo', 'ngu', 'óc chó', 'oc cho', 'thằng chó', 'con chó',
            'rác rưởi', 'khốn nạn', 'súc vật', 'mẹ mày', 'cút',
            // Công kích / xúc phạm
            'lừa đảo', 'lừa đảo', 'scam', 'đồ lừa',
        ];

        // Dấu hiệu SPAM quảng cáo: link, số điện thoại, mời chào mua bán nơi khác.
        $spamSignals = [
            'http://', 'https://', 'www.', '.com', '.net', 'zalo', 'shopee.vn',
            'liên hệ', 'inbox', 'sđt', 'sdt', 'giảm giá sốc', 'khuyến mãi',
            'truy cập', 'link', 'đặt hàng tại',
        ];

        // Nếu chứa từ vi phạm hoặc dấu hiệu spam -> TỪ CHỐI.
        foreach (array_merge($badWords, $spamSignals) as $keyword) {
            if ($keyword !== '' && str_contains($text, $keyword)) {
                return [
                    'status'           => 'rejected',
                    'confidence_score' => 95,
                    'reason'           => "[DEMO] Bình luận chứa nội dung vi phạm (từ khóa: \"{$keyword}\").",
                ];
            }
        }

        // ---- (2) Bình luận quá ngắn / mập mờ -> flagged ----
        // Dưới 5 ký tự thì AI (giả lập) không đủ căn cứ -> nhờ Admin duyệt tay.
        if (mb_strlen($text) < 5) {
            return [
                'status'           => 'flagged',
                'confidence_score' => 40,
                'reason'           => '[DEMO] Bình luận quá ngắn, độ tin cậy thấp, cần Admin duyệt lại.',
            ];
        }

        // ---- (3) Phát hiện nội dung LẠC ĐỀ -> flagged ----
        // Các từ khóa gợi ý bình luận có liên quan tới sản phẩm/thời trang/shop.
        // Một câu sạch nhưng KHÔNG chứa bất kỳ từ nào trong đây (ví dụ "Hôm nay
        // trời nắng quá") bị coi là lạc đề -> chuyển Admin duyệt tay thay vì tự duyệt.
        $contextWords = [
            'áo', 'quần', 'váy', 'vải', 'thun', 'mặc', 'size', 'ship', 'shop',
            'đồ', 'mua', 'đẹp', 'chất',
        ];

        $onTopic = false;
        foreach ($contextWords as $keyword) {
            if (str_contains($text, $keyword)) {
                $onTopic = true;
                break;
            }
        }

        if (!$onTopic) {
            return [
                'status'           => 'flagged',
                'confidence_score' => 50,
                'reason'           => '[DEMO] Nội dung có dấu hiệu lạc đề, cần Admin duyệt tay.',
            ];
        }

        // ---- (4) Còn lại -> DUYỆT ----
        return [
            'status'           => 'approved',
            'confidence_score' => 90,
            'reason'           => '[DEMO] Bình luận hợp lệ, không phát hiện nội dung vi phạm.',
        ];
    }

    /**
     * Xây dựng phần thân (payload) gửi lên Gemini API — PHƯƠNG ÁN TỐI GIẢN & AN TOÀN NHẤT.
     *
     * LÝ DO: Các trường nâng cao như 'system_instruction' / 'generation_config'
     * (kể cả biến thể camelCase) liên tục gây lỗi 400 "Unknown name" do sự bất
     * đồng bộ phiên bản REST API của Google giữa các tài khoản/khu vực.
     *
     * GIẢI PHÁP: KHÔNG dùng bất kỳ trường nâng cao nào. Chỉ gửi DUY NHẤT mảng
     * 'contents' cơ bản (tương thích 100% mọi phiên bản v1). Toàn bộ luật kiểm
     * duyệt + yêu cầu ép JSON + nội dung bình luận được GỘP thành một chuỗi
     * văn bản duy nhất ($fullPrompt) rồi đưa vào 'contents' -> 'parts' -> 'text'.
     */
    protected function buildPayload(string $comment): array
    {
        // Gộp luật kiểm duyệt (kèm yêu cầu trả JSON) + bình luận thành 1 chuỗi.
        $fullPrompt = $this->systemPrompt()
            . "\n\n----------------------------------------\n"
            . "BÌNH LUẬN CẦN KIỂM DUYỆT:\n"
            . "\"{$comment}\"";

        return [
            // Chỉ gửi 'contents' cơ bản — KHÔNG kèm system_instruction / generation_config.
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt],
                    ],
                ],
            ],
        ];
    }

    /**
     * Prompt hệ thống (System Instruction) — trái tim của việc kiểm duyệt.
     * Viết bằng tiếng Việt, ràng buộc chặt chẽ vai trò và định dạng đầu ra JSON.
     */
    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
        Bạn là một KIỂM DUYỆT VIÊN nội dung chuyên nghiệp cho một website bán quần áo thời trang tại Việt Nam.
        Nhiệm vụ của bạn là phân tích ngôn ngữ tự nhiên (tiếng Việt, kể cả tiếng lóng, teencode, viết tắt, không dấu)
        của một bình luận đánh giá sản phẩm và đưa ra quyết định kiểm duyệt.

        QUY TẮC PHÂN LOẠI:
        1. "approved" (DUYỆT): Bình luận lịch sự, mang tính đánh giá sản phẩm/dịch vụ (khen, chê, góp ý)
        dù tích cực hay tiêu cực, MIỄN LÀ không vi phạm.
        2. "rejected" (TỪ CHỐI): Bình luận chứa BẤT KỲ yếu tố nào sau đây:
        - Từ ngữ tục tĩu, chửi thề, thô tục.
        - Lời lẽ công kích, xúc phạm, phân biệt, thù ghét cá nhân/tổ chức.
        - Quảng cáo spam, chèn link, số điện thoại, rao bán, lôi kéo sang nơi khác.
        - Nội dung hoàn toàn vô nghĩa, spam ký tự.
        3. "flagged" (GẮN CỜ NGHI VẤN): Khi bạn KHÔNG CHẮC CHẮN, nội dung mập mờ, nhạy cảm nhẹ,
        hoặc độ tin cậy của bạn thấp -> để con người duyệt lại.

        YÊU CẦU VỀ ĐẦU RA (BẮT BUỘC TUYỆT ĐỐI):
        - CHỈ trả về một đối tượng JSON THUẦN, KHÔNG kèm giải thích, KHÔNG markdown, KHÔNG dấu ```.
        - Cấu trúc JSON chính xác như sau:
        {
        "status": "approved" | "rejected" | "flagged",
        "confidence_score": 85,
        "reason": "Giải thích ngắn gọn bằng tiếng Việt vì sao đưa ra quyết định này"
        }
        - "confidence_score" là một SỐ NGUYÊN từ 0 đến 100 thể hiện mức độ tự tin của bạn.
        - Nếu "confidence_score" dưới 60, hãy đặt "status" là "flagged".
        PROMPT;
    }

    /**
     * Chuẩn hóa kết quả JSON thô từ Gemini thành mảng có cấu trúc, an toàn.
     *
     * - Bọc khối try-catch (thông qua json_decode + JSON_THROW_ON_ERROR)
     *   để phòng ngừa lỗi khi AI trả về chuỗi không phải JSON hợp lệ.
     * - Bảo đảm 'status' luôn là một trong các giá trị hợp lệ.
     * - Ép 'confidence_score' về số nguyên 0..100.
     * - Bảo đảm luôn có 'reason' dạng chuỗi.
     *
     * @throws RuntimeException Khi chuỗi trả về không phải JSON hợp lệ.
     */
    protected function normalizeResult(string $rawJson): array
    {
        try {
            $clean = trim($rawJson);

            // 1. Vì KHÔNG ép JSON qua config, Gemini rất hay bọc kết quả trong khối
            //    markdown dạng ```json ... ``` -> loại bỏ toàn bộ dấu backtick + chữ "json".
            $clean = str_replace(['```json', '```JSON', '```'], '', $clean);

            // 2. Phòng trường hợp AI kèm thêm chữ giải thích trước/sau JSON:
            //    cắt lấy đúng đoạn từ dấu '{' đầu tiên đến dấu '}' cuối cùng.
            $start = strpos($clean, '{');
            $end   = strrpos($clean, '}');
            if ($start !== false && $end !== false && $end >= $start) {
                $clean = substr($clean, $start, $end - $start + 1);
            }

            $clean = trim($clean);

            // 3. Parse chuỗi JSON đã làm sạch thành mảng kết hợp của PHP.
            $data = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // Nếu không decode được -> ném lỗi để Job xử lý (chuyển review sang flagged).
            throw new RuntimeException('Gemini trả về JSON không hợp lệ: ' . $rawJson, 0, $e);
        }

        if (!is_array($data)) {
            throw new RuntimeException('Gemini trả về dữ liệu không đúng định dạng: ' . $rawJson);
        }

        // Danh sách trạng thái hợp lệ; nếu Gemini trả về giá trị lạ -> mặc định 'flagged'.
        $allowed = ['approved', 'rejected', 'flagged'];
        $status  = strtolower((string) ($data['status'] ?? 'flagged'));
        if (!in_array($status, $allowed, true)) {
            $status = 'flagged';
        }

        // Ép độ tin cậy về khoảng 0..100.
        $score = (int) ($data['confidence_score'] ?? 0);
        $score = max(0, min(100, $score));

        // Trích xuất đủ 3 trường chuẩn: status, confidence_score, reason.
        return [
            'status'           => $status,
            'confidence_score' => $score,
            'reason'           => trim((string) ($data['reason'] ?? 'Không có lý do cụ thể.')),
        ];
    }
}