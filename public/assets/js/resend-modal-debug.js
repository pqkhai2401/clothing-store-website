/**
 * Debug utility for resend modal functionality
 * This file helps troubleshoot resend modal issues
 */

// Debug function to test resend modal
window.debugResendModal = function() {
    console.log('=== Resend Modal Debug ===');
    
    // Check if elements exist
    const resendBtn = document.getElementById('resendBtn');
    const resendModal = document.getElementById('resendToApproverModal');
    const confirmBtn = document.getElementById('confirmResendBtn');
    
    console.log('Resend Button:', resendBtn);
    console.log('Resend Modal:', resendModal);
    console.log('Confirm Button:', confirmBtn);
    
    // Check Bootstrap availability
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    console.log('jQuery available:', typeof $ !== 'undefined');
    
    // Check if modal has proper attributes
    if (resendBtn) {
        console.log('Button data-bs-toggle:', resendBtn.getAttribute('data-bs-toggle'));
        console.log('Button data-bs-target:', resendBtn.getAttribute('data-bs-target'));
        console.log('Button data-form-id:', resendBtn.getAttribute('data-form-id'));
    }
    
    // Check modal structure
    if (resendModal) {
        console.log('Modal classes:', resendModal.className);
        console.log('Modal style display:', resendModal.style.display);
        console.log('Modal tabindex:', resendModal.getAttribute('tabindex'));
    }
    
    console.log('=== End Debug ===');
};

// Function to manually test modal opening
window.testResendModal = function() {
    console.log('Testing resend modal...');
    
    const resendModal = document.getElementById('resendToApproverModal');
    if (!resendModal) {
        console.error('Resend modal not found!');
        return;
    }
    
    try {
        // Try Bootstrap 5 method
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            console.log('Using Bootstrap 5 method');
            const modalInstance = new bootstrap.Modal(resendModal);
            modalInstance.show();
            return;
        }
        
        // Try jQuery method
        if (typeof $ !== 'undefined' && $.fn.modal) {
            console.log('Using jQuery method');
            $(resendModal).modal('show');
            return;
        }
        
        // Manual method
        console.log('Using manual method');
        resendModal.style.display = 'block';
        resendModal.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'test-modal-backdrop';
        document.body.appendChild(backdrop);
        
        console.log('Modal opened manually');
        
    } catch (error) {
        console.error('Error testing modal:', error);
    }
};

// Function to manually close modal
window.closeTestModal = function() {
    const resendModal = document.getElementById('resendToApproverModal');
    const backdrop = document.getElementById('test-modal-backdrop');

    if (resendModal) {
        resendModal.style.display = 'none';
        resendModal.classList.remove('show');
        document.body.classList.remove('modal-open');

        if (backdrop) {
            backdrop.remove();
        }

        console.log('Modal closed manually');
    }
};

// Function to force modal visible with extreme CSS
window.forceModalVisible = function() {
    const modal = document.getElementById('resendToApproverModal');
    if (!modal) {
        console.error('Modal not found');
        return;
    }

    console.log('Forcing modal visible with extreme CSS...');

    // Apply extreme CSS to make modal visible
    modal.style.cssText = `
        display: block !important;
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        z-index: 999999 !important;
        opacity: 1 !important;
        visibility: visible !important;
        background: rgba(0,0,0,0.5) !important;
        width: 100vw !important;
        height: 100vh !important;
        padding: 0 !important;
        margin: 0 !important;
    `;

    const dialog = modal.querySelector('.modal-dialog');
    if (dialog) {
        dialog.style.cssText = `
            position: relative !important;
            z-index: 1000000 !important;
            margin: auto !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            background: white !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3) !important;
            max-width: 500px !important;
            width: 90% !important;
        `;
    }

    modal.classList.add('show');
    document.body.classList.add('modal-open');

    console.log('Modal forced visible');
};

// Function to check modal visibility issues
window.checkModalVisibility = function() {
    const modal = document.getElementById('resendToApproverModal');
    const backdrop = document.querySelector('.modal-backdrop');

    console.log('=== Modal Visibility Check ===');
    console.log('Modal element:', modal);
    console.log('Modal display:', modal ? modal.style.display : 'N/A');
    console.log('Modal classes:', modal ? modal.className : 'N/A');
    console.log('Modal z-index:', modal ? getComputedStyle(modal).zIndex : 'N/A');

    if (modal) {
        const dialog = modal.querySelector('.modal-dialog');
        console.log('Dialog element:', dialog);
        console.log('Dialog z-index:', dialog ? getComputedStyle(dialog).zIndex : 'N/A');
        console.log('Dialog position:', dialog ? getComputedStyle(dialog).position : 'N/A');

        // Check modal position and dimensions
        const rect = modal.getBoundingClientRect();
        console.log('Modal position:', {
            top: rect.top,
            left: rect.left,
            width: rect.width,
            height: rect.height,
            bottom: rect.bottom,
            right: rect.right
        });

        // Check if modal is in viewport
        const inViewport = rect.top >= 0 && rect.left >= 0 &&
                          rect.bottom <= window.innerHeight &&
                          rect.right <= window.innerWidth;
        console.log('Modal in viewport:', inViewport);

        // Check computed styles
        const styles = getComputedStyle(modal);
        console.log('Modal computed styles:', {
            position: styles.position,
            top: styles.top,
            left: styles.left,
            transform: styles.transform,
            opacity: styles.opacity,
            visibility: styles.visibility
        });

        if (dialog) {
            const dialogRect = dialog.getBoundingClientRect();
            const dialogStyles = getComputedStyle(dialog);
            console.log('Dialog position:', {
                top: dialogRect.top,
                left: dialogRect.left,
                width: dialogRect.width,
                height: dialogRect.height
            });
            console.log('Dialog computed styles:', {
                position: dialogStyles.position,
                top: dialogStyles.top,
                left: dialogStyles.left,
                transform: dialogStyles.transform,
                margin: dialogStyles.margin
            });
        }
    }

    console.log('Backdrop element:', backdrop);
    console.log('Backdrop z-index:', backdrop ? getComputedStyle(backdrop).zIndex : 'N/A');
    console.log('Body classes:', document.body.className);

    // Check for elements with higher z-index
    const allElements = document.querySelectorAll('*');
    const highZIndexElements = [];
    allElements.forEach(el => {
        const zIndex = parseInt(getComputedStyle(el).zIndex);
        if (zIndex > 9999) {
            highZIndexElements.push({
                element: el,
                zIndex: zIndex,
                tagName: el.tagName,
                id: el.id,
                className: el.className
            });
        }
    });
    console.log('Elements with z-index > 9999:', highZIndexElements);

    console.log('=== End Visibility Check ===');
};

// Auto-run debug when page loads - TEMPORARILY DISABLED
// document.addEventListener('DOMContentLoaded', function() {
//     setTimeout(function() {
//         if (document.getElementById('resendBtn')) {
//             console.log('Resend modal debug utility loaded. Use window.debugResendModal() to debug.');
//             console.log('Use window.checkModalVisibility() to check visibility issues.');

//             // Add click listener to resend button for debugging
//             const resendBtn = document.getElementById('resendBtn');
//             if (resendBtn) {
//                 resendBtn.addEventListener('click', function() {
//                     console.log('Resend button clicked!');
//                     setTimeout(function() {
//                         window.checkModalVisibility();
//                     }, 500);
//                 });
//             }
//         }
//     }, 1000);
// });

console.log('Resend modal debug utility loaded but auto-initialization disabled. Use window.debugResendModal() to debug manually.');
