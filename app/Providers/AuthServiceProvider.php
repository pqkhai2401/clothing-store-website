<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\Bridge\PersonalAccessGrant;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\ClientRepository;
use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Passport::tokensExpireIn(now()->addDays(value: 1));
        Passport::refreshTokensExpireIn(now()->addDays(value: 30));

        Passport::tokensCan([
            'place-orders' => 'Place orders',
            'check-status' => 'Check order status',
        ]);

        // Cấu hình Passport để lưu organizationId vào token
        Passport::withCookieSerialization();
        
        // Đăng ký Event Listener để lưu thông tin organizationId khi tạo token
        $this->app['events']->listen(\Laravel\Passport\Events\AccessTokenCreated::class, 
            function ($event) {
                try {
                    // Lấy organizationId từ request, kiểm tra cả hai định dạng
                    $organizationId = request('organizationId') ?? request('organization_id');
                    
                    if ($organizationId) {
                        Log::info("Token created with organizationId: {$organizationId}");
                        
                        // Cập nhật trường organizationId trong bảng oauth_access_tokens
                        DB::table('oauth_access_tokens')
                            ->where('id', $event->tokenId)
                            ->update(['organizationId' => $organizationId]);
                            
                        // Debug: Kiểm tra xem token đã được cập nhật chưa
                        $token = DB::table('oauth_access_tokens')
                            ->where('id', $event->tokenId)
                            ->first();
                            
                        Log::info("Token after update", [
                            'token_id' => $event->tokenId,
                            'organizationId' => $token->organizationId ?? 'not set'
                        ]);
                    } else {
                        Log::warning("No organizationId found in request when creating token");
                    }
                } catch (\Exception $e) {
                    Log::error("Error updating token with organizationId: {$e->getMessage()}");
                }
            }
        );
    }
}
