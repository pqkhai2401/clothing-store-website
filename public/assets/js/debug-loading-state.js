/**
 * Debug Loading State
 * Kiểm tra và debug các vấn đề liên quan đến loading state
 */

// Monitor loading state
window.debugLoadingState = function() {
    console.log('🔍 DEBUG LOADING STATE:');
    console.log('   Document ready state:', document.readyState);
    console.log('   Window loaded:', document.readyState === 'complete');
    
    // Check AJAX activity
    if (typeof $ !== 'undefined') {
        console.log('   Active AJAX requests:', $.active);
    }
    
    // Check loading indicators
    const ajaxLoader = document.getElementById('ajax-loader');
    console.log('   AJAX loader present:', !!ajaxLoader);
    if (ajaxLoader) {
        console.log('   AJAX loader visible:', ajaxLoader.style.display !== 'none');
    }
    
    // Check spinner elements
    const spinners = document.querySelectorAll('.spinner-border');
    console.log('   Spinner elements:', spinners.length);
    
    // Check redirect flag
    console.log('   Is redirecting:', !!window.isRedirecting);
    
    // Check URL validator
    console.log('   URL validator loaded:', !!window.urlValidator);
    
    // Check current URL validity
    if (window.UrlValidator) {
        const isValid = window.UrlValidator.validateUrl();
        console.log('   Current URL valid:', isValid);
    }
};

// Monitor page load events
window.monitorPageLoadEvents = function() {
    console.log('📊 MONITORING PAGE LOAD EVENTS...');
    
    // DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ DOMContentLoaded fired');
            window.debugLoadingState();
        });
    } else {
        console.log('✅ DOM already loaded');
    }
    
    // Window load
    if (document.readyState !== 'complete') {
        window.addEventListener('load', function() {
            console.log('✅ Window load fired');
            window.debugLoadingState();
        });
    } else {
        console.log('✅ Window already loaded');
    }
    
    // Monitor AJAX activity
    if (typeof $ !== 'undefined') {
        $(document).ajaxStart(function() {
            console.log('🔄 AJAX request started');
        });
        
        $(document).ajaxStop(function() {
            console.log('✅ All AJAX requests completed');
            window.debugLoadingState();
        });
    }
};

// Check for loading issues
window.checkLoadingIssues = function() {
    console.log('🚨 CHECKING FOR LOADING ISSUES:');
    
    const issues = [];
    
    // Check if page is stuck in loading state
    if (document.readyState !== 'complete') {
        issues.push('Page not fully loaded (readyState: ' + document.readyState + ')');
    }
    
    // Check for active AJAX requests
    if (typeof $ !== 'undefined' && $.active > 0) {
        issues.push('Active AJAX requests: ' + $.active);
    }
    
    // Check for visible loading indicators
    const ajaxLoader = document.getElementById('ajax-loader');
    if (ajaxLoader && ajaxLoader.style.display !== 'none') {
        issues.push('AJAX loader still visible');
    }
    
    const spinners = document.querySelectorAll('.spinner-border:not([style*="display: none"])');
    if (spinners.length > 0) {
        issues.push('Visible spinner elements: ' + spinners.length);
    }
    
    // Check for JavaScript errors
    if (window.jsErrors && window.jsErrors.length > 0) {
        issues.push('JavaScript errors detected: ' + window.jsErrors.length);
    }
    
    // Check for redirect loops
    if (window.isRedirecting) {
        issues.push('Page is in redirecting state');
    }
    
    if (issues.length === 0) {
        console.log('✅ No loading issues detected');
    } else {
        console.log('❌ Loading issues found:');
        issues.forEach((issue, index) => {
            console.log(`   ${index + 1}. ${issue}`);
        });
    }
    
    return issues;
};

// Force cleanup loading state
window.forceCleanupLoadingState = function() {
    console.log('🧹 FORCE CLEANUP LOADING STATE...');
    
    // Remove loading indicators
    const ajaxLoader = document.getElementById('ajax-loader');
    if (ajaxLoader) {
        ajaxLoader.remove();
        console.log('   ✅ Removed AJAX loader');
    }
    
    // Remove spinners
    const spinners = document.querySelectorAll('.spinner-border');
    spinners.forEach(spinner => spinner.remove());
    if (spinners.length > 0) {
        console.log(`   ✅ Removed ${spinners.length} spinner elements`);
    }
    
    // Clear redirect flag
    if (window.isRedirecting) {
        window.isRedirecting = false;
        console.log('   ✅ Cleared redirect flag');
    }
    
    // Abort pending AJAX requests
    if (typeof $ !== 'undefined' && $.active > 0) {
        // Note: This is aggressive and might break functionality
        console.log(`   ⚠️  ${$.active} AJAX requests still active`);
    }
    
    console.log('✅ Cleanup completed');
};

// Auto-monitor on load
document.addEventListener('DOMContentLoaded', function() {
    // Start monitoring after a short delay
    setTimeout(() => {
        window.monitorPageLoadEvents();
        
        // Check for issues after 5 seconds
        setTimeout(() => {
            const issues = window.checkLoadingIssues();
            if (issues.length > 0) {
                console.warn('⚠️  Loading issues detected after 5 seconds. Run window.forceCleanupLoadingState() if needed.');
            }
        }, 5000);
    }, 100);
});

// Track JavaScript errors
window.jsErrors = [];
window.addEventListener('error', function(event) {
    window.jsErrors.push({
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        timestamp: new Date()
    });
    console.error('JavaScript Error:', event.message, 'at', event.filename + ':' + event.lineno);
});

// Expose debug functions globally
console.log('🔧 Loading State Debug Tools loaded:');
console.log('   - window.debugLoadingState(): Check current loading state');
console.log('   - window.monitorPageLoadEvents(): Monitor page load events');
console.log('   - window.checkLoadingIssues(): Check for loading issues');
console.log('   - window.forceCleanupLoadingState(): Force cleanup loading state');
