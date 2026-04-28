# Global URL Validation System

## Overview

A comprehensive URL validation system has been implemented to prevent errors when users manually edit URL parameters in the browser. This system automatically validates both URL path parameters (like `/detail/123`) and query string parameters (like `?id=123&page=1`) across the entire application and redirects users to the dashboard if invalid parameters are detected.

## ✅ Issues Resolved

1. **URL validation now works application-wide** - No longer limited to notification pages
2. **Proper architectural separation** - URL validation is now a standalone utility, not embedded in notifications.js
3. **Path parameter validation** - Validates IDs in URL paths like `/organization/detail/1ahfhaowhf`
4. **Automatic redirect functionality** - Invalid URLs automatically redirect to appropriate dashboard

## Features

### 1. Automatic URL Validation
- Validates URL parameters when the notification manager initializes
- Checks for valid format of common parameters: `id`, `page`, `perPage`, `search`, `type`, `isRead`, `status`
- Automatically redirects to dashboard if invalid parameters are found

### 2. Parameter Validation Rules

#### ID Parameters
- Must be positive integers
- Cannot be zero or negative
- Cannot contain non-numeric characters
- Example: `?id=123` ✅ | `?id=abc` ❌ | `?id=0` ❌

#### Page Numbers
- Must be positive integers
- Range: 1 to 10,000
- Example: `?page=1` ✅ | `?page=0` ❌ | `?page=abc` ❌

#### Per Page Values
- Must be one of: 10, 25, 50, 100
- Example: `?perPage=25` ✅ | `?perPage=15` ❌

#### Search Terms
- Maximum length: 255 characters
- Allows alphanumeric characters, spaces, and common punctuation
- Example: `?search=test` ✅ | `?search=<script>` ❌

#### Notification Types
- Must be one of: `form_submitted`, `form_rejected`, `form_approved`, `user_note`, `system_notification`
- Note: `tax_rejected` has been consolidated into `form_rejected`
- Example: `?type=form_submitted` ✅ | `?type=invalid_type` ❌

#### Boolean/Status Values
- Allowed values: `0`, `1`, `true`, `false`, `yes`, `no`, `read`, `unread`, `pending`, `approved`, `rejected`
- Example: `?isRead=true` ✅ | `?isRead=maybe` ❌

### 3. Global Functions

#### `window.validateUrl(url)`
Validates a URL and returns true/false
```javascript
// Validate current URL
if (window.validateUrl()) {
    console.log('Current URL is valid');
}

// Validate specific URL
if (window.validateUrl('?id=123&page=1')) {
    console.log('URL is valid');
}
```

#### `window.sanitizeUrl(url)`
Removes invalid parameters and returns clean URL
```javascript
// Sanitize current URL
const cleanUrl = window.sanitizeUrl();

// Sanitize specific URL
const cleanUrl = window.sanitizeUrl('?id=123&invalid=abc&page=1');
// Returns: ?id=123&page=1
```

#### `window.safeNavigate(url, useAjax)`
Safely navigate with URL validation
```javascript
// Navigate with validation
window.safeNavigate('/accountant/form/detail/123');

// Navigate without AJAX
window.safeNavigate('/admin/dashboard', false);
```

### 4. Testing

#### Browser Console Testing
Run the test function in browser console:
```javascript
testUrlValidation()
```

This will run comprehensive tests on various URL parameter combinations and show results.

### 5. Configuration

#### Disable URL Validation (if needed)
```javascript
// Disable validation for specific instance
window.notificationManager.urlValidation.enabled = false;
```

#### Custom Dashboard URL
The system automatically detects the appropriate dashboard URL based on current context:
- `/accountant/dashboard` for accountant users
- `/admin/dashboard` for admin users
- `/dashboard` as fallback

### 6. Error Handling

When invalid parameters are detected:
1. Warning message is logged to console
2. User-friendly toast notification is shown
3. Automatic redirect to dashboard after 1.5 seconds
4. Navigation uses AJAX if available, otherwise regular page load

### 7. Security Benefits

- Prevents injection attacks through URL parameters
- Validates data types and ranges
- Provides safe fallback navigation
- Logs security events for monitoring
- Sanitizes URLs before processing

### 8. Integration with Existing Code

The URL validation system integrates seamlessly with existing notification functionality:
- Form navigation validation
- Modal parameter validation
- AJAX request parameter validation
- Permission check validation

## Usage Examples

### Example 1: Valid URLs
```
/accountant/dashboard?page=1&perPage=25
/admin/form/detail/123
/accountant/user-notes?search=test&isRead=false
```

### Example 2: Invalid URLs (will redirect to dashboard)
```
/accountant/dashboard?id=abc
/admin/form/detail/0
/accountant/user-notes?page=-1&perPage=15
```

### Example 3: Using in Custom Code
```javascript
// Before navigating, validate the URL
function navigateToForm(formId) {
    const url = `/accountant/form/detail/${formId}`;
    
    if (window.validateUrl(url)) {
        window.safeNavigate(url);
    } else {
        console.error('Invalid form ID:', formId);
        // Handle error appropriately
    }
}
```

## Troubleshooting

### Common Issues

1. **Validation too strict**: Adjust validation rules in `isValidUrlParameter()` method
2. **False positives**: Check parameter names match expected cases
3. **Dashboard redirect loop**: Ensure dashboard URLs themselves are valid

### Debug Mode
Enable detailed logging by setting:
```javascript
window.notificationManager.urlValidation.debug = true;
```

## 🧪 Testing the Implementation

### Test the Specific Issue
To test the original issue mentioned:

1. **Navigate to:** `http://127.0.0.1:8000/accountant/organization/detail/1ahfhaowhf`
2. **Expected behavior:** Page should redirect to `/accountant/dashboard` with a warning message
3. **Console output:** Should show validation warnings and redirect information

### Interactive Test Page
Visit: `http://127.0.0.1:8000/test-url-validation.html`

This page provides:
- System status check
- Automated test suite
- Manual URL testing
- Live navigation testing
- Console output monitoring

### Browser Console Testing
Run these commands in any page's browser console:

```javascript
// Check if system is loaded
checkUrlValidationSystem()

// Test the specific problematic URL
testSpecificUrl('/accountant/organization/detail/1ahfhaowhf')

// Run full test suite
testUrlValidation()

// Test current page
testCurrentUrl()
```

### Manual Testing Examples

**Valid URLs (should work normally):**
```
http://127.0.0.1:8000/accountant/organization/detail/123
http://127.0.0.1:8000/admin/form/detail-form/456
http://127.0.0.1:8000/accountant/dashboard?page=1&perPage=25
```

**Invalid URLs (should redirect to dashboard):**
```
http://127.0.0.1:8000/accountant/organization/detail/1ahfhaowhf
http://127.0.0.1:8000/admin/form/detail-form/abc123
http://127.0.0.1:8000/accountant/form/detail/0
http://127.0.0.1:8000/accountant/dashboard?id=-5
```

## 📁 File Structure

```
public/assets/js/
├── url-validator.js           # Main URL validation system (NEW)
├── test-url-validation.js     # Test functions (NEW)
├── notifications.js           # Cleaned up, URL validation removed
└── url-validation-readme.md   # This documentation

public/
└── test-url-validation.html   # Interactive test page (NEW)

resources/views/dashboard/layout/components/
└── script.blade.php           # Updated to include url-validator.js
```

## 🔧 Implementation Details

### Core Files Added/Modified

1. **`url-validator.js`** - Standalone URL validation system
2. **`script.blade.php`** - Added URL validator to global includes
3. **`notifications.js`** - Removed URL validation, kept only notification logic
4. **Test files** - Added comprehensive testing capabilities

### Validation Patterns

The system validates these URL patterns:
- `/detail/{id}` - Organization, form, user note details
- `/edit/{id}` - Edit pages
- `/show/{id}` - Show pages
- `/form/detail/{id}` - Form detail pages
- `/form/detail-form/{id}` - Admin form detail pages
- `/user-notes/detail/{id}` - User note details
- `/organization/detail/{id}` - Organization details

### Security Features

- **Input sanitization** - Removes malicious parameters
- **Type validation** - Ensures IDs are positive integers
- **Range validation** - Prevents extremely large numbers
- **Pattern matching** - Validates URL structure
- **Automatic fallback** - Safe redirect to dashboard

This system provides comprehensive protection against URL manipulation while maintaining a smooth user experience.
