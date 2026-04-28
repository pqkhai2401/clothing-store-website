/**
 * Test script for URL validation
 * This can be included on test pages to verify URL validation is working
 */

// Test function that can be called from browser console
window.testUrlValidation = function() {
    console.log('=== Testing URL Validation System ===');
    
    const testCases = [
        // Valid URLs
        { url: '/accountant/organization/detail/123', expected: true, description: 'Valid organization detail URL' },
        { url: '/admin/form/detail-form/456', expected: true, description: 'Valid admin form detail URL' },
        { url: '/accountant/form/detail/789', expected: true, description: 'Valid accountant form detail URL' },
        { url: '/accountant/dashboard?page=1&perPage=25', expected: true, description: 'Valid dashboard with query params' },
        
        // Invalid URLs - should trigger redirect
        { url: '/accountant/organization/detail/1ahfhaowhf', expected: false, description: 'Invalid organization ID (alphanumeric)' },
        { url: '/admin/form/detail-form/abc123', expected: false, description: 'Invalid form ID (mixed alphanumeric)' },
        { url: '/accountant/form/detail/0', expected: false, description: 'Invalid form ID (zero)' },
        { url: '/accountant/dashboard?id=-5', expected: false, description: 'Invalid negative ID' },
        { url: '/admin/form/detail-form/999999999999999999999', expected: false, description: 'Invalid extremely large ID' },
        { url: '/accountant/organization/detail/', expected: false, description: 'Missing ID in URL' },
        
        // Query parameter tests
        { url: '/accountant/dashboard?page=abc', expected: false, description: 'Invalid page parameter' },
        { url: '/accountant/dashboard?perPage=15', expected: false, description: 'Invalid perPage value' },
        { url: '/accountant/dashboard?search=' + 'x'.repeat(300), expected: false, description: 'Search term too long' }
    ];
    
    let passed = 0;
    let failed = 0;
    
    console.log('Running tests...\n');
    
    testCases.forEach((testCase, index) => {
        const fullUrl = window.location.origin + testCase.url;
        const result = window.validateUrl ? window.validateUrl(fullUrl) : false;
        const success = result === testCase.expected;
        
        console.log(`Test ${index + 1}: ${testCase.description}`);
        console.log(`  URL: ${testCase.url}`);
        console.log(`  Expected: ${testCase.expected}, Got: ${result}`);
        console.log(`  Result: ${success ? '✅ PASS' : '❌ FAIL'}`);
        console.log('');
        
        if (success) {
            passed++;
        } else {
            failed++;
        }
    });
    
    console.log(`=== Test Results ===`);
    console.log(`Passed: ${passed}`);
    console.log(`Failed: ${failed}`);
    console.log(`Total: ${testCases.length}`);
    
    if (failed === 0) {
        console.log('🎉 All tests passed!');
    } else {
        console.log('⚠️ Some tests failed. Check the implementation.');
    }
    
    return { passed, failed, total: testCases.length };
};

// Test specific URL patterns
window.testSpecificUrl = function(url) {
    console.log(`Testing URL: ${url}`);
    
    if (!window.validateUrl) {
        console.error('❌ URL validation function not available');
        return false;
    }
    
    const isValid = window.validateUrl(url);
    console.log(`Result: ${isValid ? '✅ Valid' : '❌ Invalid'}`);
    
    if (!isValid) {
        console.log('This URL would trigger a redirect to dashboard');
    }
    
    return isValid;
};

// Test the current page URL
window.testCurrentUrl = function() {
    const currentUrl = window.location.href;
    console.log(`Testing current URL: ${currentUrl}`);
    
    return window.testSpecificUrl(currentUrl);
};

// Simulate navigation to test URL
window.simulateNavigation = function(url) {
    console.log(`Simulating navigation to: ${url}`);
    
    if (!window.safeNavigate) {
        console.error('❌ Safe navigation function not available');
        return;
    }
    
    // This will either navigate to the URL or redirect to dashboard if invalid
    window.safeNavigate(url, false); // Use regular navigation for testing
};

// Check if URL validation system is properly loaded
window.checkUrlValidationSystem = function() {
    console.log('=== URL Validation System Check ===');
    
    const checks = [
        { name: 'window.validateUrl', available: typeof window.validateUrl === 'function' },
        { name: 'window.sanitizeUrl', available: typeof window.sanitizeUrl === 'function' },
        { name: 'window.safeNavigate', available: typeof window.safeNavigate === 'function' },
        { name: 'window.urlValidator', available: typeof window.urlValidator === 'object' }
    ];
    
    let allAvailable = true;
    
    checks.forEach(check => {
        const status = check.available ? '✅' : '❌';
        console.log(`${status} ${check.name}: ${check.available ? 'Available' : 'Not Available'}`);
        if (!check.available) allAvailable = false;
    });
    
    console.log(`\nSystem Status: ${allAvailable ? '✅ Ready' : '❌ Not Ready'}`);
    
    if (allAvailable) {
        console.log('\n🎯 You can now run:');
        console.log('- testUrlValidation() - Run full test suite');
        console.log('- testCurrentUrl() - Test current page URL');
        console.log('- testSpecificUrl("your-url-here") - Test specific URL');
        console.log('- simulateNavigation("your-url-here") - Test navigation');
    }
    
    return allAvailable;
};

// Auto-run system check when this script loads
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure other scripts have loaded
    setTimeout(() => {
        console.log('🔍 URL Validation Test Script Loaded');
        console.log('Run checkUrlValidationSystem() to verify the system is working');
    }, 1000);
});

// Export for manual testing
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        testUrlValidation: window.testUrlValidation,
        testSpecificUrl: window.testSpecificUrl,
        testCurrentUrl: window.testCurrentUrl,
        simulateNavigation: window.simulateNavigation,
        checkUrlValidationSystem: window.checkUrlValidationSystem
    };
}
