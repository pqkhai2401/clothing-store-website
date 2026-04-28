/**
 * Fix Redirect Loop Emergency Script
 * Khắc phục redirect loop và loading indicator issues
 */

// Emergency stop redirect loop
window.stopRedirectLoop = function() {
    console.log('🛑 EMERGENCY: Stopping redirect loop...');
    
    // Clear redirect flag
    window.isRedirecting = false;
    
    // Disable URL validator
    if (window.urlValidator) {
        window.urlValidator.config.enabled = false;
        console.log('✅ Disabled URL validator');
    }
    
    // Clear any pending redirects
    const highestTimeoutId = setTimeout(function(){}, 0);
    for (let i = 0; i < highestTimeoutId; i++) {
        clearTimeout(i);
    }
    console.log('✅ Cleared all timeouts');
    
    // Force cleanup loading state
    if (typeof window.forceCleanupLoadingState === 'function') {
        window.forceCleanupLoadingState();
    }
    
    // Remove loading indicators
    const loadingElements = document.querySelectorAll('#ajax-loader, .spinner-border');
    loadingElements.forEach(el => el.remove());
    console.log(`✅ Removed ${loadingElements.length} loading elements`);
    
    // Stop any ongoing navigation
    if (window.stop) {
        window.stop();
    }
    
    console.log('✅ Redirect loop stopped');
};

// Check for redirect loop
window.checkRedirectLoop = function() {
    const currentUrl = window.location.href;
    const path = window.location.pathname;
    
    console.log('🔍 Checking for redirect loop...');
    console.log('   Current URL:', currentUrl);
    console.log('   Current path:', path);
    
    // Check if we're in a dashboard
    const isDashboard = path === '/accountant' || 
                       path === '/admin/dashboard' || 
                       path === '/dashboard';
    
    console.log('   Is dashboard:', isDashboard);
    
    // Check redirect flag
    console.log('   Is redirecting flag:', !!window.isRedirecting);
    
    // Check URL validator status
    if (window.urlValidator) {
        console.log('   URL validator enabled:', window.urlValidator.config.enabled);
    }
    
    // Check for too many redirects error
    const hasRedirectError = document.title.includes('redirected') || 
                            document.body.textContent.includes('ERR_TOO_MANY_REDIRECTS');
    
    console.log('   Has redirect error:', hasRedirectError);
    
    if (hasRedirectError || (isDashboard && window.isRedirecting)) {
        console.warn('🚨 REDIRECT LOOP DETECTED!');
        window.stopRedirectLoop();
        return true;
    }
    
    return false;
};

// Monitor for redirect loops
window.monitorRedirectLoop = function() {
    console.log('👁️  Starting redirect loop monitoring...');
    
    let redirectCount = 0;
    const maxRedirects = 3;
    const startTime = Date.now();
    
    const originalPushState = history.pushState;
    const originalReplaceState = history.replaceState;
    
    // Monitor history changes
    history.pushState = function() {
        redirectCount++;
        console.log(`📍 History push state #${redirectCount}:`, arguments[2]);
        
        if (redirectCount > maxRedirects) {
            console.error('🚨 Too many redirects detected via pushState!');
            window.stopRedirectLoop();
            return;
        }
        
        return originalPushState.apply(history, arguments);
    };
    
    history.replaceState = function() {
        redirectCount++;
        console.log(`📍 History replace state #${redirectCount}:`, arguments[2]);
        
        if (redirectCount > maxRedirects) {
            console.error('🚨 Too many redirects detected via replaceState!');
            window.stopRedirectLoop();
            return;
        }
        
        return originalReplaceState.apply(history, arguments);
    };
    
    // Monitor location changes
    let lastUrl = window.location.href;
    setInterval(() => {
        const currentUrl = window.location.href;
        if (currentUrl !== lastUrl) {
            redirectCount++;
            console.log(`🔄 URL changed #${redirectCount}: ${lastUrl} → ${currentUrl}`);
            
            if (redirectCount > maxRedirects) {
                console.error('🚨 Too many URL changes detected!');
                window.stopRedirectLoop();
                return;
            }
            
            lastUrl = currentUrl;
        }
    }, 100);
    
    // Auto-check after 5 seconds
    setTimeout(() => {
        window.checkRedirectLoop();
    }, 5000);
};

// Fix loading indicator
window.fixLoadingIndicator = function() {
    console.log('🔧 Fixing loading indicator...');
    
    // Force document ready state
    if (document.readyState !== 'complete') {
        console.log('⚠️  Document not complete, forcing completion...');
        
        // Dispatch load event
        window.dispatchEvent(new Event('load'));
        document.dispatchEvent(new Event('DOMContentLoaded'));
    }
    
    // Clear loading indicators
    window.forceCleanupLoadingState && window.forceCleanupLoadingState();
    
    // Stop any animations
    const animatedElements = document.querySelectorAll('[class*="spin"], [class*="loading"], [class*="rotate"]');
    animatedElements.forEach(el => {
        el.style.animation = 'none';
        el.style.transform = 'none';
    });
    
    console.log('✅ Loading indicator fixed');
};

// Auto-run on load
document.addEventListener('DOMContentLoaded', function() {
    // Check immediately
    setTimeout(() => {
        const hasLoop = window.checkRedirectLoop();
        if (!hasLoop) {
            window.monitorRedirectLoop();
        }
    }, 100);
    
    // Fix loading indicator after 3 seconds if still present
    setTimeout(() => {
        if (document.readyState !== 'complete') {
            console.warn('⚠️  Page still loading after 3 seconds, attempting fix...');
            window.fixLoadingIndicator();
        }
    }, 3000);
});

// Expose functions globally
console.log('🚑 Redirect Loop Fix Tools loaded:');
console.log('   - window.stopRedirectLoop(): Emergency stop redirect loop');
console.log('   - window.checkRedirectLoop(): Check for redirect loop');
console.log('   - window.monitorRedirectLoop(): Monitor for redirect loops');
console.log('   - window.fixLoadingIndicator(): Fix loading indicator issues');
