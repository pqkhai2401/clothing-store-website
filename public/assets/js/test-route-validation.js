/**
 * Test Route Validation System
 * Kiểm tra và test hệ thống validation cho các route không hợp lệ
 */

// Test cases cho route validation
const testCases = [
    // Valid routes
    { url: '/accountant', expected: true, description: 'Accountant dashboard' },
    { url: '/admin/dashboard', expected: true, description: 'Admin dashboard' },
    { url: '/accountant/form/detail/123', expected: true, description: 'Form detail with valid ID' },
    { url: '/admin/user/edit/456', expected: true, description: 'User edit with valid ID' },
    { url: '/login', expected: true, description: 'Login page' },
    
    // Invalid routes - không tồn tại
    { url: '/accountantohawohfi', expected: false, description: 'Invalid accountant route' },
    { url: '/admin/nonexistent', expected: false, description: 'Non-existent admin route' },
    { url: '/random/path/here', expected: false, description: 'Completely random path' },
    { url: '/accountant/invalid/section', expected: false, description: 'Invalid accountant section' },
    
    // Invalid parameters
    { url: '/accountant/form/detail/abc', expected: false, description: 'Form detail with invalid ID' },
    { url: '/admin/user/edit/-1', expected: false, description: 'User edit with negative ID' },
    { url: '/accountant/form/detail/0', expected: false, description: 'Form detail with zero ID' },
    
    // Valid routes with query parameters
    { url: '/accountant?page=1', expected: true, description: 'Accountant with valid page param' },
    { url: '/admin/dashboard?search=test', expected: true, description: 'Admin dashboard with search' },
    
    // Invalid query parameters
    { url: '/accountant?page=abc', expected: false, description: 'Invalid page parameter' },
    { url: '/admin/dashboard?page=-1', expected: false, description: 'Negative page parameter' }
];

/**
 * Chạy tất cả test cases
 */
window.runRouteValidationTests = function() {
    console.log('🧪 Bắt đầu test Route Validation System...\n');
    
    let passed = 0;
    let failed = 0;
    const results = [];
    
    testCases.forEach((testCase, index) => {
        const result = testSingleRoute(testCase, index + 1);
        results.push(result);
        
        if (result.passed) {
            passed++;
        } else {
            failed++;
        }
    });
    
    // Hiển thị kết quả tổng hợp
    console.log('\n📊 KẾT QUẢ TEST:');
    console.log(`✅ Passed: ${passed}`);
    console.log(`❌ Failed: ${failed}`);
    console.log(`📈 Success Rate: ${((passed / testCases.length) * 100).toFixed(1)}%`);
    
    if (failed > 0) {
        console.log('\n❌ FAILED TESTS:');
        results.filter(r => !r.passed).forEach(r => {
            console.log(`   ${r.testNumber}. ${r.description}: Expected ${r.expected}, got ${r.actual}`);
        });
    }
    
    return {
        total: testCases.length,
        passed: passed,
        failed: failed,
        successRate: (passed / testCases.length) * 100,
        results: results
    };
};

/**
 * Test một route cụ thể
 */
function testSingleRoute(testCase, testNumber) {
    const { url, expected, description } = testCase;
    
    try {
        // Sử dụng URL validator để kiểm tra
        const isValid = window.UrlValidator ? window.UrlValidator.validateUrl(url) : false;
        const passed = isValid === expected;
        
        const status = passed ? '✅' : '❌';
        console.log(`${status} Test ${testNumber}: ${description}`);
        console.log(`   URL: ${url}`);
        console.log(`   Expected: ${expected}, Got: ${isValid}`);
        
        if (!passed) {
            console.log(`   ⚠️  FAILED: Expected ${expected} but got ${isValid}`);
        }
        
        return {
            testNumber,
            description,
            url,
            expected,
            actual: isValid,
            passed
        };
    } catch (error) {
        console.error(`❌ Test ${testNumber} ERROR:`, error);
        return {
            testNumber,
            description,
            url,
            expected,
            actual: 'ERROR',
            passed: false,
            error: error.message
        };
    }
}

/**
 * Test route validation với URL cụ thể
 */
window.testSpecificRoute = function(url) {
    console.log(`🔍 Testing specific route: ${url}`);
    
    if (!window.UrlValidator) {
        console.error('❌ UrlValidator not available');
        return false;
    }
    
    try {
        const isValid = window.UrlValidator.validateUrl(url);
        const isValidRoute = window.UrlValidator.isValidRoute(url);
        
        console.log(`📋 Results for: ${url}`);
        console.log(`   Route exists: ${isValidRoute}`);
        console.log(`   Parameters valid: ${isValid}`);
        console.log(`   Overall valid: ${isValid && isValidRoute}`);
        
        if (!isValidRoute) {
            console.log('   ⚠️  Route không tồn tại trong hệ thống');
        }
        
        if (!isValid) {
            console.log('   ⚠️  Tham số URL không hợp lệ');
        }
        
        return isValid && isValidRoute;
    } catch (error) {
        console.error('❌ Error testing route:', error);
        return false;
    }
};

/**
 * Simulate navigation để test xử lý lỗi (sẽ redirect ngay lập tức)
 */
window.simulateInvalidNavigation = function(url) {
    console.log(`🚀 Simulating navigation to invalid URL: ${url}`);
    console.log(`⚠️  WARNING: This will redirect immediately if URL is invalid!`);

    if (!window.safeNavigate) {
        console.error('❌ safeNavigate function not available');
        return;
    }

    // Confirm trước khi redirect
    if (confirm(`Bạn có chắc muốn test navigation đến URL: ${url}?\nNếu URL không hợp lệ, bạn sẽ được redirect ngay lập tức về dashboard.`)) {
        window.safeNavigate(url, false);
    }
};

/**
 * Test các URL phổ biến bị lỗi
 */
window.testCommonInvalidUrls = function() {
    const commonInvalidUrls = [
        'http://127.0.0.1:8000/accountantohawohfi',
        'http://127.0.0.1:8000/admin/xyz',
        'http://127.0.0.1:8000/nonexistent',
        'http://127.0.0.1:8000/accountant/invalid',
        'http://127.0.0.1:8000/admin/user/edit/abc'
    ];
    
    console.log('🧪 Testing common invalid URLs...\n');
    
    commonInvalidUrls.forEach((url, index) => {
        console.log(`${index + 1}. Testing: ${url}`);
        const result = window.testSpecificRoute(url);
        console.log(`   Result: ${result ? '✅ Valid' : '❌ Invalid'}\n`);
    });
};

/**
 * Hiển thị thông tin về URL validator
 */
window.showUrlValidatorInfo = function() {
    if (!window.urlValidator) {
        console.log('❌ URL Validator chưa được khởi tạo');
        return;
    }
    
    console.log('📋 URL Validator Information:');
    console.log('   Config:', window.urlValidator.config);
    console.log('   Dashboard URLs:', window.urlValidator.dashboardUrls);
    console.log('   Valid Route Patterns:', window.urlValidator.validRoutePatterns.length + ' patterns');
    
    // Test current URL
    const currentUrl = window.location.href;
    const isCurrentValid = window.UrlValidator.validateUrl(currentUrl);
    console.log(`   Current URL (${currentUrl}): ${isCurrentValid ? '✅ Valid' : '❌ Invalid'}`);
};

// Auto-run basic tests khi file được load
document.addEventListener('DOMContentLoaded', function() {
    // Đợi URL validator được khởi tạo
    setTimeout(() => {
        if (window.UrlValidator) {
            console.log('🔧 Route Validation Test Suite loaded');
            console.log('📝 Available functions:');
            console.log('   - runRouteValidationTests(): Chạy tất cả test cases');
            console.log('   - testSpecificRoute(url): Test một URL cụ thể');
            console.log('   - simulateInvalidNavigation(url): Simulate navigation đến URL không hợp lệ');
            console.log('   - testCommonInvalidUrls(): Test các URL thường gặp lỗi');
            console.log('   - showUrlValidatorInfo(): Hiển thị thông tin URL validator');
        } else {
            console.warn('⚠️  URL Validator not found. Tests may not work properly.');
        }
    }, 1000);
});
