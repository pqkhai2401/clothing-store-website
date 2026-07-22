<?php

namespace App\Services;

/**
 * =============================================================================
 *  BỘ LỌC TỪ NGỮ TỤC TĨU / CHỬI THỀ (đồng bộ, chạy tại chỗ)
 * =============================================================================
 *
 * Chính sách kiểm duyệt đánh giá:
 *  - DUYỆT mọi bình luận, bất kể khách chấm bao nhiêu sao (khen hay chê đều là
 *    quyền của người mua).
 *  - CHỈ TỪ CHỐI khi bình luận có chứa từ ngữ chửi thề / tục tĩu.
 *
 * Bộ lọc dùng danh sách từ khóa nội bộ nên cho kết quả tức thì và ổn định
 * (không phụ thuộc mạng/AI), đủ để phản hồi bằng toast ngay khi khách gửi.
 */
class ProfanityFilter
{
    /**
     * Danh sách từ tục tĩu / chửi thề cần chặn.
     * Bao gồm cả biến thể viết tắt, không dấu, teencode thường gặp.
     */
    protected array $badWords = [
        // Tục tĩu / chửi thề
        'đm', 'dm', 'đmm', 'dmm', 'đéo', 'deo', 'vl', 'vcl', 'vkl', 'clm', 'cmm',
        'cc', 'cứt', 'cut', 'lồn', 'lon', 'buồi', 'buoi', 'địt', 'dit', 'đụ', 'du má',
        'đù', 'đĩ', 'điếm', 'phò', 'cặc', 'cak',
        // Chửi rủa / xúc phạm bằng lời tục
        'óc chó', 'oc cho', 'thằng chó', 'con chó', 'chó chết',
        'rác rưởi', 'khốn nạn', 'khon nan', 'súc vật', 'suc vat', 'mẹ mày',
        'me may', 'cút', 'mất dạy', 'mat day',
    ];

    /**
     * Kiểm tra bình luận có chứa từ ngữ vi phạm hay không.
     *
     * So khớp theo "ranh giới từ" để tránh chặn nhầm từ hợp lệ có chứa chuỗi con
     * (ví dụ: "dm" không được chặn "admin", "cc" không chặn "success").
     */
    public function containsProfanity(string $comment): bool
    {
        // Chuẩn hóa: hạ chữ thường + gộp mọi khoảng trắng thành 1 dấu cách.
        $text = mb_strtolower(trim($comment));
        $text = preg_replace('/\s+/u', ' ', $text);

        // Bọc 2 đầu bằng dấu cách để so khớp cả từ ở đầu/cuối câu.
        $padded = ' ' . $text . ' ';

        foreach ($this->badWords as $word) {
            $word = mb_strtolower(trim($word));
            if ($word === '') {
                continue;
            }

            // Cụm nhiều từ (có dấu cách) -> tìm trực tiếp.
            if (str_contains($word, ' ')) {
                if (str_contains($text, $word)) {
                    return true;
                }
                continue;
            }

            // Từ đơn -> yêu cầu đứng tách biệt bởi khoảng trắng hoặc dấu câu,
            // tránh chặn nhầm chuỗi con nằm trong từ hợp lệ.
            $pattern = '/(?:^|[\s\p{P}])' . preg_quote($word, '/') . '(?:$|[\s\p{P}])/u';
            if (preg_match($pattern, $padded)) {
                return true;
            }
        }

        return false;
    }
}
