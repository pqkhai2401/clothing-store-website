<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * =============================================================================
 *  HỆ GỢI Ý LAI GIỮA NỘI DUNG VÀ LUẬT TRI THỨC TÍCH HỢP CLOUD AI
 *  (Hybrid Content-Based & Knowledge/Rule-Based Recommendation driven by Cloud AI)
 * =============================================================================
 *
 * Service này đóng vai trò "Stylist ảo": nó KHÔNG tự tính điểm bằng thuật toán
 * cứng, mà DỰNG PROMPT ràng buộc chặt (Prompt Engineering) rồi nhờ Google Gemini
 * suy luận phối đồ / cá nhân hóa, cuối cùng chỉ nhận về một MẢNG ID sản phẩm.
 *
 * Bốn kịch bản được giải quyết:
 *   1. Mix & Match  : phối sản phẩm A thành một bộ hoàn chỉnh (Rule + Content).
 *   2. Style Conflict: loại trừ item xung đột phong cách / trùng công năng.
 *   3. Seasonal Sync : ưu tiên item cùng Bộ sưu tập (mùa) với sản phẩm neo.
 *   4. Cold Start    : user mới -> ép theo mùa của tháng hiện tại; user cũ ->
 *                      đọc lịch sử (views / wishlist / order) để cá nhân hóa.
 *
 * NGUYÊN TẮC AN TOÀN: Mọi phương thức public đều được BỌC fallback. Nếu Cloud AI
 * lỗi / hết quota / trả JSON rác, hệ thống tự động rơi về `RecommendationService`
 * (thuật toán SQL thuần đã có sẵn) để giao diện KHÔNG BAO GIỜ trắng trang.
 */
class AiStylistService
{
    /** Endpoint gốc của Gemini (chưa gồm ?key=). */
    protected string $endpoint;

    /** API key đọc từ config/services.php -> .env (GEMINI_API_KEY). */
    protected ?string $apiKey;

    /** Số ứng viên tối đa nạp vào prompt (giữ prompt gọn + ép AI chỉ chọn trong tập này). */
    protected int $candidatePoolSize = 50;

    public function __construct()
    {
        // Model mặc định 'gemini-flash-lite-latest': alias ổn định, còn hạn ngạch
        // free tier (model cũ 'gemini-2.0-flash' đã bị Google đặt free-tier = 0 -> 429).
        $model          = config('services.gemini.model', 'gemini-flash-lite-latest');
        $this->apiKey   = config('services.gemini.key');
        // Dùng API v1beta vì các model mới (flash-lite-latest, gemini-3.x...) chỉ
        // phục vụ trên v1beta; endpoint v1 sẽ trả 404 với các model này.
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    /* =========================================================================
     |  KỊCH BẢN 1 + 2 + 3: MIX & MATCH (phối đồ) theo sản phẩm neo
     * ========================================================================= */

    /**
     * Gợi ý các sản phẩm PHỐI CÙNG một sản phẩm neo (khi khách xem / thêm vào giỏ).
     *
     * @param  Product  $anchor  Sản phẩm khách đang xem hoặc vừa thêm vào giỏ.
     * @param  int      $limit   Số sản phẩm muốn gợi ý.
     * @return Collection<int, Product>
     */
    public function getMixAndMatch(Product $anchor, int $limit = 4): Collection
    {
        // 0) Cache: kết quả phối đồ cho 1 sản phẩm ít thay đổi -> lưu 6 giờ để
        //    đỡ gọi Gemini mỗi lần tải trang (tránh chạm trần quota/phút).
        $cacheKey = "ai_match_{$anchor->id}_{$limit}";
        if ($cached = $this->fromCache($cacheKey, $limit)) {
            return $cached;
        }

        // 1) Lấy tập ứng viên: cùng giới tính (hoặc unisex), khác chính nó, và thuộc
        //    NHÓM TRANG PHỤC (outfit_type) tương thích với sản phẩm neo (VD: áo ->
        //    quần/chân váy/áo khoác/phụ kiện, KHÔNG gợi đầm/túi/mũ tùy tiện chỉ vì
        //    "khác category_id" — xem Category::outfitCompatibilityMap()).
        $anchorOutfitType = $anchor->category?->outfit_type;
        $compatibleTypes  = $anchor->category?->compatibleOutfitTypes() ?? [];

        $candidates = Product::query()
            ->where('status', true)
            ->where('id', '!=', $anchor->id)
            ->where('category_id', '!=', $anchor->category_id)
            ->when($anchorOutfitType && !empty($compatibleTypes), function ($q) use ($compatibleTypes) {
                $q->whereHas('category', fn ($cq) => $cq->whereIn('outfit_type', $compatibleTypes));
            })
            ->when($anchor->gender && $anchor->gender !== 'unisex', function ($q) use ($anchor) {
                $q->whereIn('gender', [$anchor->gender, 'unisex']);
            })
            ->with(['category', 'brand', 'collections'])
            ->orderByDesc('views_count')
            ->limit($this->candidatePoolSize)
            ->get();

        // 1b) LOẠI TRỪ áo khoác SAI MÙA/độ dày (VD: áo lụa mỏng mùa Xuân không nên
        //    ghép với áo phao lông vũ dày mùa Đông — đúng "loại" nhưng sai "mùa" vẫn
        //    là phối đồ vô lý). Chỉ áp dụng cho outerwear vì đây là slot nhạy cảm với
        //    thời tiết nhất; top/bottom/accessory linh hoạt hơn nên không siết ở đây.
        //    Nếu sản phẩm neo không gắn Bộ sưu tập/mùa nào thì bỏ qua bước lọc này.
        $anchorCollectionIds = $anchor->collections->pluck('id')->all();
        if (!empty($anchorCollectionIds)) {
            $candidates = $candidates->reject(function (Product $c) use ($anchorCollectionIds) {
                if (($c->category->outfit_type ?? null) !== 'outerwear') {
                    return false;
                }
                return empty(array_intersect($anchorCollectionIds, $c->collections->pluck('id')->all()));
            })->values();
        }

        // 1c) LOẠI TRỪ ứng viên XUNG ĐỘT PHONG CÁCH (VD: áo sơ mi "smart_casual"
        //    không nên ghép với quần jogger/short "sporty" — đúng loại quần, đúng
        //    mùa, nhưng sai độ trang trọng, phối lên vẫn kỳ cục). Xem
        //    Category::styleConflictMap(). Bỏ qua nếu neo không gắn style_group.
        $conflictingStyleGroups = $anchor->category?->conflictingStyleGroups() ?? [];
        if (!empty($conflictingStyleGroups)) {
            $candidates = $candidates->reject(
                fn (Product $c) => in_array($c->category->style_group ?? null, $conflictingStyleGroups, true)
            )->values();
        }

        // 1d) SẮP XẾP LẠI theo ĐỘ GẦN PHONG CÁCH với sản phẩm neo (formal gần formal/
        //    smart_casual hơn casual). Chỉ loại xung đột (bước 1c) là chưa đủ: quần
        //    tây "formal" tuy không xung đột với áo len/hoodie "casual", nhưng về gu
        //    thời trang thì áo sơ mi/polo "smart_casual" mới là lựa chọn tự nhiên hơn.
        //    Không sắp lại thì views_count thô sẽ lấn át, y hệt bug đã gặp với mùa/
        //    loại trang phục trước đó. Ổn định (PHP >=8 usort stable) nên vẫn giữ
        //    views_count làm tiêu chí phụ cho các món đồng hạng phong cách.
        $anchorStyleGroup = $anchor->category?->style_group;
        if ($anchorStyleGroup) {
            $candidates = $candidates
                ->sortBy(fn (Product $c) => $this->styleDistance($anchorStyleGroup, $c->category->style_group ?? null))
                ->values();
        }

        if ($candidates->isEmpty()) {
            return $this->fallbackSimilar($anchor, $limit);
        }

        // Nhóm trang phục "chủ lực" phải phối cùng nếu có hàng (áo <-> quần/chân váy).
        // LLM đôi khi không tuân thủ 100% chỉ dẫn trong prompt (đặc biệt model free-tier
        // nhỏ), nên luật này được ÉP BẰNG CODE ở bước hậu xử lý, không chỉ dựa vào prompt.
        $primaryTypes = $this->primaryComplementTypes($anchorOutfitType);

        // 2) Dựng prompt Stylist và gọi AI.
        $prompt   = $this->buildMixMatchPrompt($anchor, $candidates, $limit);
        $chosenIds = $this->askGeminiForIds($prompt);

        // 3) AI lỗi -> bù bằng chính pool phối đồ (ĐÃ lọc khác loại), KHÔNG dùng
        //    "sản phẩm tương tự" vì nó cùng loại -> sẽ phá Kịch bản 2 (loại trừ trùng công năng).
        if ($chosenIds === null) {
            $result = $this->rebalanceOutfitSlots($candidates->take($limit)->values(), $candidates, $primaryTypes, $limit);
            $result = $this->applyStylePreference($result, $candidates, $anchorStyleGroup);
            return $result;
        }

        // 4) Chỉ giữ ID hợp lệ nằm trong tập ứng viên (chống AI "bịa" ID).
        $products = $this->hydrateOrderedProducts($chosenIds, $candidates->pluck('id')->all(), $limit);

        // 5) AI trả thiếu -> bù cho đủ limit BẰNG POOL PHỐI ĐỒ (khác loại), giữ
        //    đúng luật loại trừ. (Trước đây bù bằng fallbackSimilar -> lòi ra đồ cùng loại.)
        $result = $this->topUp($products, fn () => $candidates, $limit);

        // 5b) Cân bằng lại nhóm: đảm bảo có nhóm chủ lực (áo <-> quần/chân váy) và
        //     GIỚI HẠN số phụ kiện — phụ kiện là "điểm nhấn thêm" chứ không phải
        //     trọng tâm bộ đồ. Cả AI lẫn topUp() (sắp theo views_count) đều có thể
        //     vô tình dồn quá nhiều phụ kiện (túi/mũ) và bỏ sót áo/quần còn sẵn hàng.
        $result = $this->rebalanceOutfitSlots($result, $candidates, $primaryTypes, $limit);

        // 5c) Ưu tiên phong cách khớp gu (không chỉ né xung đột — xem 1c/1d): AI đôi
        //    khi vẫn chọn món "không xung đột nhưng lệch gu" (VD quần tây formal lại
        //    chọn áo len casual dù có áo sơ mi smart_casual sẵn có, do model chọn
        //    theo views_count/độ phổ biến chứ không theo độ trang trọng). Ép lại
        //    bằng code để đảm bảo kết quả luôn ưu tiên món khớp gu nhất hiện có.
        $result = $this->applyStylePreference($result, $candidates, $anchorStyleGroup);

        // 6) Chỉ cache khi AI thật sự chạy (không cache kết quả fallback, để lần
        //    sau còn thử gọi lại Gemini khi quota hồi phục).
        $this->putCache($cacheKey, $result, now()->addHours(6));

        return $result;
    }

    /**
     * "Khoảng cách" phong cách giữa 2 style_group — dùng để SẮP XẾP (không phải loại
     * trừ, xem 1c) ứng viên theo mức độ trang trọng gần với sản phẩm neo nhất.
     * Thang trang trọng: formal - smart_casual - casual (0,1,2). 'neutral' (phụ
     * kiện) và style rỗng luôn coi là khoảng cách trung bình vì đi được với mọi gu.
     */
    protected function styleDistance(?string $anchorStyle, ?string $candidateStyle): int
    {
        $scale = ['formal' => 0, 'smart_casual' => 1, 'casual' => 2];

        if ($candidateStyle === 'neutral' || $candidateStyle === null) {
            return 1;
        }
        if (!isset($scale[$anchorStyle]) || !isset($scale[$candidateStyle])) {
            return 1;
        }

        return abs($scale[$anchorStyle] - $scale[$candidateStyle]);
    }

    /**
     * Với MỖI outfit_type có mặt trong $result, nếu tồn tại ứng viên CHƯA được chọn
     * cùng outfit_type nhưng có khoảng cách phong cách (styleDistance) TỐT HƠN món
     * tệ nhất hiện tại thuộc type đó, thay thế nó. Đây là bước ÉP CỨNG bằng code vì
     * AI chỉ tránh xung đột rõ ràng (1c) chứ không tự tối ưu độ khớp gu — dễ chọn
     * theo views_count/phổ biến hơn là theo độ trang trọng tương đồng.
     *
     * @param  Collection<int, Product>  $result
     * @param  Collection<int, Product>  $candidates  Đã được sắp theo styleDistance tăng dần (xem 1d).
     * @return Collection<int, Product>
     */
    protected function applyStylePreference(Collection $result, Collection $candidates, ?string $anchorStyleGroup): Collection
    {
        if (!$anchorStyleGroup || $result->isEmpty()) {
            return $result;
        }

        $distanceOf = fn (Product $p) => $this->styleDistance($anchorStyleGroup, $p->category->style_group ?? null);
        $typeOf = fn (Product $p) => $p->category->outfit_type ?? null;

        $items = $result->values()->all();
        $existingIds = $result->pluck('id')->all();

        foreach ($candidates as $candidate) {
            if (in_array($candidate->id, $existingIds, true)) {
                continue;
            }
            $candidateType = $typeOf($candidate);
            $candidateDist = $distanceOf($candidate);

            // Tìm món hiện tại CÙNG outfit_type nhưng khoảng cách phong cách TỆ HƠN.
            $worstIndex = null;
            $worstDist = -1;
            foreach ($items as $i => $item) {
                if ($typeOf($item) !== $candidateType) {
                    continue;
                }
                $dist = $distanceOf($item);
                if ($dist > $candidateDist && $dist > $worstDist) {
                    $worstDist = $dist;
                    $worstIndex = $i;
                }
            }

            if ($worstIndex !== null) {
                $existingIds = array_values(array_diff($existingIds, [$items[$worstIndex]->id]));
                $items[$worstIndex] = $candidate;
                $existingIds[] = $candidate->id;
            }
        }

        return collect($items)->values();
    }

    /**
     * Nhóm trang phục "chủ lực" — nhóm được BÙ ĐẦY ĐỦ số lượng, không bị giới hạn
     * "tối đa 1 món/nhóm" như nhóm phụ (xem rebalanceOutfitSlots()). VD: xem áo thì
     * chủ lực là quần/chân váy; xem đầm thì chủ lực là ÁO KHOÁC (đầm đã là 1 bộ
     * hoàn chỉnh nên không cần thêm áo/quần, nhưng áo khoác vẫn là món "phối cùng"
     * chính — không nên bị giới hạn ngang hàng với phụ kiện chỉ là điểm nhấn thêm).
     */
    protected function primaryComplementTypes(?string $anchorOutfitType): array
    {
        return match ($anchorOutfitType) {
            'top'             => ['bottom', 'skirt'],
            'bottom', 'skirt' => ['top'],
            'dress'           => ['outerwear'],
            default           => [],
        };
    }

    /**
     * Cân bằng lại danh sách gợi ý cuối cùng theo 2 luật:
     *  a) NHÓM PHỤ (mọi outfit_type KHÔNG thuộc $primaryTypes — VD áo khoác, phụ
     *     kiện khi neo là quần/váy) CHỈ được xuất hiện khi nhóm chủ lực KHÔNG ĐỦ
     *     hàng để lấp đầy toàn bộ $limit — và khi đó mỗi nhóm phụ tối đa 1 món để
     *     làm điểm nhấn. Nếu kho còn đủ áo (top) để lấp đầy hết slot thì KHÔNG ép
     *     thêm áo khoác/phụ kiện nào cả — dù nó đúng loại/đúng mùa, khách xem quần
     *     kỳ vọng thấy nhiều LỰA CHỌN ÁO đa dạng hơn là 1 áo khoác lấp chỗ trống.
     *     (Bug đã gặp 2 lần trước: dồn toàn phụ kiện / dồn toàn áo khoác; lần này
     *     là hệ quả còn sót: vẫn CHO PHÉP 1 món phụ dù nhóm chủ lực dư sức lấp đầy.)
     *  b) Đảm bảo có ít nhất 1 món thuộc nhóm chủ lực ($primaryTypes) nếu tập ứng
     *     viên có hàng.
     * Áp dụng SAU CÙNG (sau cả AI lẫn topUp) vì cả hai đều có thể vô tình dồn lệch
     * nhóm — AI không tuân thủ tuyệt đối prompt, còn topUp() chỉ sắp theo views_count
     * nên không phân biệt loại.
     *
     * @param  Collection<int, Product>  $result
     * @param  Collection<int, Product>  $candidates
     * @param  string[]                  $primaryTypes
     * @return Collection<int, Product>
     */
    protected function rebalanceOutfitSlots(Collection $result, Collection $candidates, array $primaryTypes, int $limit): Collection
    {
        if ($result->isEmpty() || $limit <= 0) {
            return $result;
        }

        $typeOf = fn (Product $p) => $p->category->outfit_type ?? null;
        $isPrimary = fn (Product $p) => in_array($typeOf($p), $primaryTypes, true);

        // Kho còn đủ (>= $limit) sản phẩm thuộc nhóm chủ lực -> không cần "điểm nhấn"
        // phụ nào cả, dồn hết slot cho nhóm chủ lực. Ngược lại, mỗi nhóm phụ được
        // phép góp tối đa 1 món để bù chỗ trống khi nhóm chủ lực không đủ hàng.
        $primaryCandidateCount = $candidates->filter($isPrimary)->count();
        $maxPerSecondaryType = empty($primaryTypes)
            ? 1
            : ($primaryCandidateCount >= $limit ? 0 : 1);

        // a) Bỏ bớt món dư của TỪNG nhóm phụ (giữ tối đa 1 món/nhóm), giữ nguyên
        //    thứ tự ưu tiên ban đầu. Nhóm chủ lực không bị giới hạn ở bước này.
        $kept = collect();
        $secondaryCounts = [];
        foreach ($result as $item) {
            $type = $typeOf($item);
            if (!$isPrimary($item) && $type !== null) {
                $count = $secondaryCounts[$type] ?? 0;
                if ($count >= $maxPerSecondaryType) {
                    continue;
                }
                $secondaryCounts[$type] = $count + 1;
            }
            $kept->push($item);
        }

        // Bù chỗ trống do vừa bỏ bớt: ưu tiên ứng viên thuộc nhóm chủ lực trước, các
        // nhóm phụ khác vẫn phải tôn trọng giới hạn 1 món/nhóm ở trên.
        if ($kept->count() < $limit) {
            $existingIds = $kept->pluck('id')->all();
            $fillers = $candidates
                ->reject(fn (Product $p) => in_array($p->id, $existingIds, true))
                ->sortByDesc($isPrimary)
                ->values();

            foreach ($fillers as $f) {
                if ($kept->count() >= $limit) {
                    break;
                }
                $type = $typeOf($f);
                if (!$isPrimary($f) && $type !== null) {
                    $count = $secondaryCounts[$type] ?? 0;
                    if ($count >= $maxPerSecondaryType) {
                        continue;
                    }
                    $secondaryCounts[$type] = $count + 1;
                }
                $kept->push($f);
            }
        }

        // Vẫn còn thiếu (không đủ hàng chủ lực + các nhóm phụ đã đạt giới hạn) ->
        // đành nới giới hạn, lấy thêm bất kỳ ứng viên còn lại cho đủ số lượng.
        if ($kept->count() < $limit) {
            $existingIds = $kept->pluck('id')->all();
            $more = $candidates->reject(fn (Product $p) => in_array($p->id, $existingIds, true));
            foreach ($more as $f) {
                if ($kept->count() >= $limit) {
                    break;
                }
                $kept->push($f);
            }
        }

        // b) Đảm bảo có ít nhất 1 món thuộc nhóm chủ lực, nếu tập ứng viên có hàng.
        if (!empty($primaryTypes) && !$kept->contains($isPrimary)) {
            $existingIds = $kept->pluck('id')->all();
            $primaryCandidate = $candidates->first(
                fn (Product $p) => $isPrimary($p) && !in_array($p->id, $existingIds, true)
            );
            if ($primaryCandidate) {
                $kept = $kept->count() >= $limit ? $kept->slice(0, $limit - 1) : $kept;
                $kept->push($primaryCandidate);
            }
        }

        return $kept->values();
    }

    /* =========================================================================
     |  KỊCH BẢN 4: TRANG CHỦ — Cold Start (user mới) hoặc cá nhân hóa (user cũ)
     * ========================================================================= */

    /**
     * Gợi ý cho trang chủ.
     *  - Guest / user chưa có lịch sử  -> ép theo MÙA của tháng hiện tại.
     *  - User có lịch sử               -> cho AI đọc hồ sơ hành vi để cá nhân hóa.
     *
     * @param  User|null  $user
     * @param  int        $limit
     * @return Collection<int, Product>
     */
    public function getHomepageRecommendations(?User $user, int $limit = 8): Collection
    {
        $history = $user ? $this->collectUserHistory($user) : [];
        $hasHistory = !empty($history['product_ids']);

        // --- COLD START: không có user hoặc user chưa từng tương tác ---
        if (!$hasHistory) {
            $season = $this->seasonForMonth((int) now()->month);

            // Cache theo mùa: mọi khách mới trong cùng 1 giờ dùng chung 1 kết quả.
            $cacheKey = "ai_home_cold_{$season}_{$limit}";
            if ($cached = $this->fromCache($cacheKey, $limit)) {
                return $cached;
            }

            $candidates = $this->seasonalCandidatePool($season);
            // Không có sản phẩm nào thuộc mùa hiện tại -> đành dùng phổ biến chung.
            if ($candidates->isEmpty()) {
                return $this->fallbackPopular($limit);
            }

            $prompt    = $this->buildColdStartPrompt($season, $candidates, $limit);
            $chosenIds = $this->askGeminiForIds($prompt);

            // AI lỗi/không cấu hình -> VẪN trả đúng đồ theo mùa (pool đã lọc sẵn),
            // KHÔNG rơi về popular chung chung để giữ đúng tinh thần "Cold Start theo mùa".
            // KHÔNG cache trường hợp này để lần sau còn thử gọi lại Gemini.
            if ($chosenIds === null) {
                return $candidates->take($limit)->values();
            }

            $products = $this->hydrateOrderedProducts($chosenIds, $candidates->pluck('id')->all(), $limit);
            // Thiếu thì bù bằng chính pool theo mùa (không phải popular chung).
            $result = $this->topUp($products, fn () => $candidates, $limit);
            $this->putCache($cacheKey, $result, now()->addHour());

            return $result;
        }

        // --- USER CŨ: cá nhân hóa dựa trên lịch sử ---
        // Cache riêng theo user + "vân tay" lịch sử: khi user tương tác thêm
        // (mua/thích sản phẩm mới) thì md5 đổi -> cache tự làm mới ngay.
        $cacheKey = 'ai_home_user_' . $user->id
            . '_' . md5(implode(',', $history['product_ids']))
            . "_{$limit}";
        if ($cached = $this->fromCache($cacheKey, $limit)) {
            return $cached;
        }

        // Loại các sản phẩm đã mua/đã có trong wishlist ra khỏi tập ứng viên.
        $candidates = Product::query()
            ->where('status', true)
            ->whereNotIn('id', $history['exclude_ids'])
            ->with(['category', 'brand', 'collections'])
            ->orderByDesc('views_count')
            ->limit($this->candidatePoolSize)
            ->get();

        if ($candidates->isEmpty()) {
            return $this->fallbackPersonalized($user, $limit);
        }

        $prompt    = $this->buildPersonalizedPrompt($history, $candidates, $limit);
        $chosenIds = $this->askGeminiForIds($prompt);

        if ($chosenIds === null) {
            return $this->fallbackPersonalized($user, $limit);
        }

        $products = $this->hydrateOrderedProducts($chosenIds, $candidates->pluck('id')->all(), $limit);
        $result = $this->topUp($products, fn () => $this->fallbackPersonalized($user, $limit), $limit);
        $this->putCache($cacheKey, $result, now()->addMinutes(30));

        return $result;
    }

    /* =========================================================================
     |  PROMPT ENGINEERING
     * ========================================================================= */

    /**
     * Prompt cho Mix & Match — gộp cả 3 luật: bổ trợ, loại trừ, đồng bộ mùa.
     */
    protected function buildMixMatchPrompt(Product $anchor, Collection $candidates, int $limit): string
    {
        $anchorSeasons = $anchor->collections->pluck('name')->implode(', ') ?: 'Không rõ';
        $anchorInfo = sprintf(
            "SẢN PHẨM KHÁCH ĐANG CHỌN:\n- ID: %d\n- Tên: %s\n- Danh mục: %s\n- Giới tính: %s\n- Giá: %s\n- Bộ sưu tập/Mùa: %s\n- Mô tả: %s",
            $anchor->id,
            $anchor->name,
            $anchor->category->name ?? 'N/A',
            $anchor->gender,
            number_format((float) $anchor->price, 0, ',', '.') . 'đ',
            $anchorSeasons,
            $this->shorten($anchor->description)
        );

        return <<<PROMPT
        Bạn là một STYLIST (chuyên gia phối đồ) thời trang chuyên nghiệp cho một shop quần áo tại Việt Nam.
        Nhiệm vụ: từ sản phẩm khách đang chọn, hãy chọn ra tối đa {$limit} sản phẩm trong DANH SÁCH ỨNG VIÊN
        để phối cùng tạo thành MỘT BỘ TRANG PHỤC HOÀN CHỈNH, hài hòa, có gu.

        {$anchorInfo}

        BẠN PHẢI TUÂN THỦ NGHIÊM NGẶT CÁC LUẬT SAU:
        1. LUẬT BỔ TRỢ (Mix & Match): Chọn các món KHÁC CÔNG NĂNG để ghép thành bộ.
           Ví dụ: áo thun -> phối quần short / quần jeans / áo khoác / phụ kiện.
           ƯU TIÊN BẮT BUỘC: nếu danh sách ứng viên có sản phẩm QUẦN hoặc CHÂN VÁY
           (khi sản phẩm khách chọn là áo), hoặc có sản phẩm ÁO (khi sản phẩm khách
           chọn là quần/chân váy), PHẢI chọn ít nhất 1 món thuộc nhóm đó trước, rồi
           mới bổ sung áo khoác/phụ kiện cho đủ số lượng.
        2. LUẬT LOẠI TRỪ (Style Conflict):
           - TUYỆT ĐỐI KHÔNG chọn món trùng công năng với sản phẩm khách đã chọn
             (khách đã có quần dài thì KHÔNG gợi thêm một quần dài khác).
           - KHÔNG trộn lẫn phong cách xung đột (đồ casual/năng động KHÔNG ghép với
             đồ công sở đứng dáng, và ngược lại).
        3. LUẬT ĐỒNG BỘ MÙA (Seasonal Sync): Ưu tiên tối đa các sản phẩm cùng Bộ sưu tập/Mùa
           với sản phẩm khách chọn ("{$anchorSeasons}"). Ví dụ đồ Mùa hè phải phối với item mát mẻ cùng Mùa hè.
        4. Ưu tiên các món có mức giá tương đồng để bộ đồ cân đối.

        DANH SÁCH ỨNG VIÊN (chỉ được chọn ID trong danh sách này):
        {$this->serializeCandidates($candidates)}

        ĐỊNH DẠNG ĐẦU RA — BẮT BUỘC TUYỆT ĐỐI:
        - CHỈ trả về DUY NHẤT một mảng JSON gồm các số ID đã chọn, ví dụ: [12, 4, 27].
        - KHÔNG kèm chữ giải thích, KHÔNG markdown, KHÔNG dấu backtick.
        - Nếu không có món nào phù hợp, trả về [].
        PROMPT;
    }

    /**
     * Prompt cho Cold Start theo mùa (user mới / guest).
     */
    protected function buildColdStartPrompt(string $season, Collection $candidates, int $limit): string
    {
        return <<<PROMPT
        Bạn là chuyên gia merchandising của một shop thời trang tại Việt Nam.
        Hiện đang là tháng {$this->currentMonth()} và cửa hàng muốn đẩy mạnh BỘ SƯU TẬP mùa {$season}.
        Khách hàng này là KHÁCH MỚI, chưa có lịch sử mua/xem, nên chưa biết sở thích.

        Nhiệm vụ: chọn ra tối đa {$limit} sản phẩm HẤP DẪN NHẤT, ĐA DẠNG danh mục
        (đủ áo/quần/phụ kiện...) và PHÙ HỢP với mùa "{$season}" để trưng ở trang chủ,
        tạo ấn tượng đầu tiên tốt và có khả năng chốt đơn cao.

        DANH SÁCH ỨNG VIÊN (chỉ được chọn ID trong danh sách này):
        {$this->serializeCandidates($candidates)}

        ĐỊNH DẠNG ĐẦU RA — BẮT BUỘC: CHỈ trả về một mảng JSON các số ID, ví dụ [3, 9, 15, 21].
        KHÔNG kèm giải thích, KHÔNG markdown. Nếu không có, trả về [].
        PROMPT;
    }

    /**
     * Prompt cá nhân hóa cho user cũ dựa trên hồ sơ hành vi.
     */
    protected function buildPersonalizedPrompt(array $history, Collection $candidates, int $limit): string
    {
        $profile = sprintf(
            "HỒ SƠ SỞ THÍCH CỦA KHÁCH:\n- Danh mục hay xem/mua: %s\n- Thương hiệu ưa thích: %s\n- Giới tính thường mua: %s\n- Khoảng giá thường chi: %s",
            implode(', ', $history['categories']) ?: 'Chưa rõ',
            implode(', ', $history['brands']) ?: 'Chưa rõ',
            implode(', ', $history['genders']) ?: 'Chưa rõ',
            $history['price_hint'] ?? 'Chưa rõ'
        );

        return <<<PROMPT
        Bạn là hệ thống gợi ý cá nhân hóa của một shop thời trang tại Việt Nam.
        Dưới đây là hồ sơ hành vi (lịch sử xem, yêu thích, đã mua) của một khách hàng cũ.

        {$profile}

        Nhiệm vụ: chọn tối đa {$limit} sản phẩm trong DANH SÁCH ỨNG VIÊN mà khách này
        NHIỀU KHẢ NĂNG THÍCH NHẤT, bám sát hồ sơ trên. Ưu tiên đúng gu (danh mục, thương hiệu,
        giới tính, tầm giá), đồng thời tránh gợi lại đúng thứ khách đã có.

        DANH SÁCH ỨNG VIÊN (chỉ được chọn ID trong danh sách này):
        {$this->serializeCandidates($candidates)}

        ĐỊNH DẠNG ĐẦU RA — BẮT BUỘC: CHỈ trả về một mảng JSON các số ID, ví dụ [5, 8, 1, 12].
        KHÔNG kèm giải thích, KHÔNG markdown. Nếu không có, trả về [].
        PROMPT;
    }

    /**
     * Chuyển tập ứng viên thành các dòng text ngắn gọn để nhét vào prompt.
     */
    protected function serializeCandidates(Collection $candidates): string
    {
        return $candidates->map(function (Product $p) {
            return sprintf(
                'ID:%d | %s | Danh mục:%s | Giới tính:%s | Giá:%sđ | Mùa:%s',
                $p->id,
                $p->name,
                $p->category->name ?? 'N/A',
                $p->gender,
                number_format((float) $p->price, 0, ',', '.'),
                $p->collections->pluck('name')->implode('/') ?: 'N/A'
            );
        })->implode("\n");
    }

    /* =========================================================================
     |  GỌI GEMINI + PARSE JSON AN TOÀN
     * ========================================================================= */

    /**
     * Gửi prompt lên Gemini và trả về MẢNG ID (int) mà AI chọn.
     * Trả về null nếu bất kỳ khâu nào lỗi -> tín hiệu để caller fallback.
     *
     * @return int[]|null
     */
    protected function askGeminiForIds(string $prompt): ?array
    {
        // Không cấu hình key -> coi như AI không khả dụng, để caller fallback.
        if (empty($this->apiKey)) {
            Log::warning('[AI-RECO] ⚠️ Thiếu GEMINI_API_KEY -> DÙNG FALLBACK SQL.');
            return null;
        }

        try {
            $http = Http::timeout(20)->retry(2, 500);

            // Local (Windows/Laragon) thường thiếu CA -> tắt verify SSL cho đỡ vướng.
            // Production giữ nguyên xác thực SSL để chống man-in-the-middle.
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->endpoint . '?key=' . $this->apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

            if ($response->failed()) {
                Log::warning('[AI-RECO] ⚠️ Gemini lỗi HTTP -> DÙNG FALLBACK SQL', [
                    'status' => $response->status(), // 429 = hết quota, 400/403 = sai key/model
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $rawText = $response->json('candidates.0.content.parts.0.text');
            if (empty($rawText)) {
                return null;
            }

            $ids = $this->parseIdArray($rawText);

            // ĐÈN BÁO: ghi log khi Gemini phản hồi hợp lệ -> chứng minh AI thật sự chạy
            // (khác với nhánh fallback SQL). Xem trong storage/logs/laravel.log.
            Log::info('[AI-RECO]  GEMINI CHẠY THẬT — AI đã trả về danh sách ID', [
                'raw_text'   => trim($rawText),
                'parsed_ids' => $ids,
            ]);

            return $ids;
        } catch (\Throwable $e) {
            // Bất kỳ lỗi mạng/timeout/exception nào cũng nuốt lại và fallback.
            Log::error('[AI-RECO]  Ngoại lệ khi gọi Gemini -> DÙNG FALLBACK SQL', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Bóc mảng ID từ chuỗi text AI trả về, xử lý an toàn mọi trường hợp bẩn:
     * dính ```json, kèm chữ giải thích, xuống dòng thừa...
     *
     * @return int[]|null
     */
    protected function parseIdArray(string $raw): ?array
    {
        $clean = str_replace(['```json', '```JSON', '```'], '', trim($raw));

        // Cắt lấy đúng đoạn từ '[' đầu tiên đến ']' cuối cùng.
        $start = strpos($clean, '[');
        $end   = strrpos($clean, ']');
        if ($start === false || $end === false || $end < $start) {
            return null;
        }
        $clean = substr($clean, $start, $end - $start + 1);

        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            return null;
        }

        // Chỉ giữ số nguyên dương, bỏ trùng, giữ nguyên thứ tự AI xếp.
        $ids = [];
        foreach ($decoded as $value) {
            if (is_numeric($value) && (int) $value > 0) {
                $ids[(int) $value] = true; // key ép unique
            }
        }

        return array_keys($ids);
    }

    /* =========================================================================
     |  ELOQUENT: lấy Product thật theo mảng ID, GIỮ ĐÚNG THỨ TỰ ưu tiên của AI
     * ========================================================================= */

    /**
     * Nhận mảng ID do AI chọn, lọc theo tập ID hợp lệ, rồi lấy Model Product
     * thực tế từ DB đúng theo thứ tự AI đã xếp (dùng ORDER BY FIELD).
     *
     * @param  int[]  $aiIds       ID theo thứ tự AI chọn.
     * @param  int[]  $validIds    Tập ID ứng viên hợp lệ (chống AI bịa ID).
     * @return Collection<int, Product>
     */
    protected function hydrateOrderedProducts(array $aiIds, array $validIds, int $limit): Collection
    {
        $validSet = array_flip($validIds);
        $filtered = array_values(array_filter($aiIds, fn ($id) => isset($validSet[$id])));
        $filtered = array_slice($filtered, 0, $limit);

        if (empty($filtered)) {
            return collect();
        }

        $orderExpression = 'FIELD(id, ' . implode(',', $filtered) . ')';

        return Product::whereIn('id', $filtered)
            ->where('status', true)
            ->with(['category', 'brand', 'collections'])
            ->orderByRaw($orderExpression)
            ->get();
    }

    /**
     * Bù cho đủ $limit sản phẩm bằng nguồn fallback nếu AI trả về thiếu.
     *
     * @param  Collection<int, Product>  $current
     * @param  callable                  $fallbackFactory  () => Collection
     * @return Collection<int, Product>
     */
    protected function topUp(Collection $current, callable $fallbackFactory, int $limit): Collection
    {
        if ($current->count() >= $limit) {
            return $current->take($limit)->values();
        }

        $existingIds = $current->pluck('id')->all();
        $extra = $fallbackFactory()
            ->reject(fn (Product $p) => in_array($p->id, $existingIds))
            ->take($limit - $current->count());

        return $current->concat($extra)->take($limit)->values();
    }

    /* =========================================================================
     |  CACHE — lưu MẢNG ID (nhẹ), hydrate Product tươi mỗi request
     * ========================================================================= */

    /**
     * Đọc cache: lấy mảng ID đã lưu rồi nạp lại Product THẬT từ DB (để giá/tồn kho
     * luôn mới). Trả null nếu cache trống hoặc sản phẩm đã bị ẩn/xóa hết.
     */
    protected function fromCache(string $key, int $limit): ?Collection
    {
        $ids = Cache::get($key);
        if (!is_array($ids) || $ids === []) {
            return null;
        }

        $products = $this->hydrateOrderedProducts($ids, $ids, $limit);
        return $products->isNotEmpty() ? $products : null;
    }

    /**
     * Ghi cache: chỉ lưu danh sách ID (không lưu cả Model cho nhẹ và không bị cũ).
     */
    protected function putCache(string $key, Collection $products, \DateTimeInterface $ttl): void
    {
        Cache::put($key, $products->pluck('id')->all(), $ttl);
    }

    /* =========================================================================
     |  THU THẬP LỊCH SỬ HÀNH VI (cho nhánh cá nhân hóa)
     * ========================================================================= */

    /**
     * Tổng hợp hồ sơ hành vi của user từ product_views, wishlists, order_items.
     *
     * @return array{product_ids:int[], exclude_ids:int[], categories:string[], brands:string[], genders:string[], price_hint:?string}
     */
    protected function collectUserHistory(User $user): array
    {
        $productIds = collect();
        $excludeIds = collect();
        $categories = collect();
        $brands     = collect();
        $genders    = collect();
        $prices     = collect();

        $absorb = function (?Product $product, bool $exclude = false)
            use ($productIds, $excludeIds, $categories, $brands, $genders, $prices) {
            if (!$product) {
                return;
            }
            $productIds->push($product->id);
            if ($exclude) {
                $excludeIds->push($product->id);
            }
            $categories->push($product->category->name ?? null);
            $brands->push($product->brand->name ?? null);
            $genders->push($product->gender);
            $prices->push((float) $product->price);
        };

        // 1) Sản phẩm đã mua (đơn đã hoàn thành) -> tín hiệu mạnh nhất, và LOẠI TRỪ khỏi gợi ý.
        Order::where('user_id', $user->id)
            ->where('status', OrderStatus::COMPLETED->value)
            ->with('orderItems.productVariant.product.category', 'orderItems.productVariant.product.brand')
            ->get()
            ->each(function (Order $order) use ($absorb) {
                foreach ($order->orderItems as $item) {
                    $absorb($item->productVariant->product ?? null, true);
                }
            });

        // 2) Wishlist -> tín hiệu sở thích, cũng loại khỏi gợi ý (khách đã lưu rồi).
        Wishlist::where('user_id', $user->id)
            ->with('product.category', 'product.brand')
            ->get()
            ->each(fn (Wishlist $w) => $absorb($w->product ?? null, true));

        // 3) Lịch sử xem 60 ngày gần nhất -> tín hiệu quan tâm (KHÔNG loại trừ).
        ProductView::where('user_id', $user->id)
            ->where('viewed_at', '>=', Carbon::now()->subDays(60))
            ->with('product.category', 'product.brand')
            ->latest('viewed_at')
            ->limit(50)
            ->get()
            ->each(fn (ProductView $v) => $absorb($v->product ?? null, false));

        return [
            'product_ids' => $productIds->unique()->values()->all(),
            'exclude_ids' => $excludeIds->unique()->values()->all(),
            'categories'  => $categories->filter()->countBy()->sortDesc()->keys()->take(5)->all(),
            'brands'      => $brands->filter()->countBy()->sortDesc()->keys()->take(5)->all(),
            'genders'     => $genders->filter()->unique()->values()->all(),
            'price_hint'  => $prices->isNotEmpty()
                ? number_format($prices->avg(), 0, ',', '.') . 'đ (trung bình)'
                : null,
        ];
    }

    /* =========================================================================
     |  TIỆN ÍCH MÙA / BỘ SƯU TẬP
     * ========================================================================= */

    /**
     * Tập ứng viên thuộc một mùa (Bộ sưu tập). Nếu mùa đó chưa có sản phẩm,
     * nới lỏng ra toàn bộ sản phẩm hot để không bị rỗng.
     */
    protected function seasonalCandidatePool(string $season): Collection
    {
        // Khớp MỀM bằng LIKE để bền với cách đặt tên Bộ sưu tập trong DB.
        // VD từ khóa "Thu" khớp cả "Bộ sưu tập Thu", "BST Thu Đông", "Thu 2026"...
        $inSeason = Product::query()
            ->where('status', true)
            ->whereHas('collections', fn ($q) => $q->where('name', 'like', "%{$season}%"))
            ->with(['category', 'brand', 'collections'])
            ->orderByDesc('views_count')
            ->limit($this->candidatePoolSize)
            ->get();

        if ($inSeason->isNotEmpty()) {
            return $inSeason;
        }

        return Product::query()
            ->where('status', true)
            ->with(['category', 'brand', 'collections'])
            ->orderByDesc('views_count')
            ->limit($this->candidatePoolSize)
            ->get();
    }

    /**
     * Ánh xạ THÁNG hiện tại của máy chủ -> tên Bộ sưu tập (mùa) cần đẩy bán.
     *
     * Theo yêu cầu nghiệp vụ: "bán trước mùa" (ví dụ tháng 7 đã đẩy đồ Thu,
     * tháng 12 đẩy đồ Đông). Bảng ánh xạ dưới đây có thể chỉnh tùy chiến lược shop.
     *
     * TRẢ VỀ TỪ KHÓA khớp tên Bộ sưu tập trong DB (Xuân/Hạ/Thu/Đông), dùng cho
     * so khớp LIKE ở seasonalCandidatePool() — không phải chuỗi "Mùa ..." cứng.
     */
    protected function seasonForMonth(int $month): string
    {
        return match ($month) {
            1, 2       => 'Đông',
            3, 4       => 'Xuân',
            5, 6       => 'Hạ',
            7, 8, 9    => 'Thu',
            10, 11, 12 => 'Đông',
            default    => 'Xuân',
        };
    }

    protected function currentMonth(): int
    {
        return (int) now()->month;
    }

    /* =========================================================================
     |  FALLBACK — tái sử dụng RecommendationService (thuật toán SQL thuần đã có)
     * ========================================================================= */

    protected function fallbackSimilar(Product $anchor, int $limit): Collection
    {
        return RecommendationService::getSimilarProducts($anchor, $limit);
    }

    protected function fallbackPersonalized(?User $user, int $limit): Collection
    {
        return RecommendationService::getPersonalizedRecommendations($user, $limit);
    }

    protected function fallbackPopular(int $limit): Collection
    {
        return RecommendationService::getPopularProducts($limit);
    }

    /* =========================================================================
     |  HELPER NHỎ
     * ========================================================================= */

    /** Rút gọn mô tả để prompt không phình to. */
    protected function shorten(?string $text, int $length = 120): string
    {
        $text = trim(strip_tags((string) $text));
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : ($text ?: 'N/A');
    }
}
