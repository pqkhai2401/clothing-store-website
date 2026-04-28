/**
 * Global URL Validation Utility
 * Validates URL parameters across the entire application
 * Prevents errors when users manually edit URLs with invalid parameters
 */

class UrlValidator {
  constructor() {
    this.config = {
      enabled: true, // Re-enabled với cải thiện
      autoRedirect: true,
      showToast: false, // Tắt toast để redirect mượt mà hơn
      logWarnings: true,
      checkRouteExistence: false, // DISABLE route existence check để tránh loop
      redirectDelay: 0, // Redirect ngay lập tức
    };

    this.dashboardUrls = {
      accountant: "/accountant",
      admin: "/admin/dashboard",
      default: "/dashboard",
    };

    // Định nghĩa các route pattern hợp lệ
    this.validRoutePatterns = [
      // Auth routes
      /^\/$/, // Root
      /^\/login$/,
      /^\/logout$/,
      /^\/forgot-password$/,
      /^\/reset-password$/,
      /^\/verify-code$/,
      /^\/language\/[a-z]{2}$/, // Language switching

      // Admin routes
      /^\/admin\/dashboard$/,
      /^\/admin\/organization$/,
      /^\/admin\/organization\/detail\/\d+$/,
      /^\/admin\/organization\/create$/,
      /^\/admin\/organization\/edit\/\d+$/,
      /^\/admin\/user$/,
      /^\/admin\/user\/create$/,
      /^\/admin\/user\/edit\/\d+$/,
      /^\/admin\/user\/detail\/\d+$/,
      /^\/admin\/form$/,
      /^\/admin\/form\/create$/,
      /^\/admin\/form\/detail-form\/\d+$/,
      /^\/admin\/form\/edit\/\d+$/,
      /^\/admin\/form-category$/,
      /^\/admin\/form-category\/create$/,
      /^\/admin\/form-category\/edit\/\d+$/,
      /^\/admin\/tax-type$/,
      /^\/admin\/tax-type\/create$/,
      /^\/admin\/tax-type\/edit\/\d+$/,
      /^\/admin\/user-notes$/,
      /^\/admin\/user-notes\/create$/,
      /^\/admin\/user-notes\/detail\/\d+$/,
      /^\/admin\/user-notes\/edit\/\d+$/,
      /^\/admin\/audit$/,

      // Accountant routes
      /^\/accountant$/,
      /^\/accountant\/dashboard-details$/,
      /^\/accountant\/form$/,
      /^\/accountant\/form\/create$/,
      /^\/accountant\/form\/detail\/\d+$/,
      /^\/accountant\/form\/edit\/\d+$/,
      /^\/accountant\/form\/invoice$/,
      /^\/accountant\/form\/payment$/,
      /^\/accountant\/form\/tax$/,
      /^\/accountant\/organization$/,
      /^\/accountant\/organization\/detail\/\d+$/,
      /^\/accountant\/user-notes$/,
      /^\/accountant\/user-notes\/create$/,
      /^\/accountant\/user-notes\/detail\/\d+$/,
      /^\/accountant\/user-notes\/edit\/\d+$/,
      /^\/accountant\/notifications$/,

      // API routes (cho AJAX calls)
      /^\/api\//,

      // Profile và debug routes
      /^\/profile\/me$/,
      /^\/debug\/profile$/,

      // Dashboard routes với query parameters
      /^\/dashboard$/,
      /^\/accountant\/dashboard\/user-notes$/,
      /^\/admin\/dashboard\/organizations$/,
      /^\/admin\/dashboard\/users$/,
    ];

    this.init();
  }

  init() {
    // Only run validation if enabled
    if (!this.config.enabled) {
      return;
    }

    // Đợi một chút để page load hoàn toàn trước khi validate
    // Điều này tránh conflict với page loading state
    setTimeout(() => {
      this.validateCurrentUrl();
    }, 100); // Delay ngắn để đảm bảo page đã load
  }

  /**
   * Get the appropriate dashboard URL based on current context
   */
  getDashboardUrl() {
    const currentPath = window.location.pathname;

    // IMPORTANT: Nếu đã ở dashboard rồi thì không redirect nữa
    if (
      currentPath === "/accountant" ||
      currentPath === "/admin/dashboard" ||
      currentPath === "/dashboard"
    ) {
      return currentPath; // Trả về chính URL hiện tại
    }

    if (currentPath.includes("/accountant")) {
      return this.dashboardUrls.accountant;
    } else if (currentPath.includes("/admin")) {
      return this.dashboardUrls.admin;
    } else {
      return this.dashboardUrls.default;
    }
  }

  /**
   * Validate the current URL and redirect if invalid
   */
  validateCurrentUrl() {
    try {
      const currentUrl = window.location.href;
      const pathname = window.location.pathname;

      // Kiểm tra xem trang có đang trong quá trình redirect không
      if (window.isRedirecting) {
        return;
      }

      // Kiểm tra xem có AJAX requests đang chạy không
      if (typeof $ !== "undefined" && $.active > 0) {
        // Thử lại sau khi AJAX requests hoàn thành
        setTimeout(() => this.validateCurrentUrl(), 500);
        return;
      }

      // Bước 1: Kiểm tra route có tồn tại không
      if (this.config.checkRouteExistence && !this.isValidRoute(pathname)) {
        this.handleInvalidUrl(
          "Route không tồn tại trong hệ thống",
          "ROUTE_NOT_FOUND"
        );
        return;
      }

      // Bước 2: Kiểm tra URL path parameters (như /detail/123)
      if (!this.validateUrlPath(pathname)) {
        this.handleInvalidUrl(
          "Tham số URL path không hợp lệ",
          "INVALID_PATH_PARAMS"
        );
        return;
      }

      // Bước 3: Kiểm tra query string parameters (như ?id=123&page=1)
      if (!this.validateQueryParameters(window.location.search)) {
        this.handleInvalidUrl(
          "Tham số URL query không hợp lệ",
          "INVALID_QUERY_PARAMS"
        );
        return;
      }
    } catch (error) {
      console.error("Error during URL validation:", error);
      this.handleInvalidUrl("Lỗi xác thực URL", "VALIDATION_ERROR");
    }
  }

  /**
   * Kiểm tra xem route có hợp lệ không
   */
  isValidRoute(pathname) {
    // Loại bỏ trailing slash (trừ root path)
    const normalizedPath =
      pathname === "/" ? pathname : pathname.replace(/\/$/, "");

    // Kiểm tra với các pattern đã định nghĩa
    for (const pattern of this.validRoutePatterns) {
      if (pattern.test(normalizedPath)) {
        return true;
      }
    }

    // Kiểm tra các route động với query parameters
    if (this.isValidDynamicRoute(normalizedPath)) {
      return true;
    }

    return false;
  }

  /**
   * Kiểm tra các route động (có thể có query parameters)
   */
  isValidDynamicRoute(pathname) {
    // Các route có thể có query parameters nhưng vẫn hợp lệ
    const dynamicRoutes = [
      "/dashboard",
      "/accountant",
      "/admin/dashboard",
      "/admin/form",
      "/admin/user",
      "/admin/organization",
      "/accountant/form",
      "/accountant/organization",
      "/accountant/user-notes",
      "/admin/user-notes",
    ];

    return dynamicRoutes.includes(pathname);
  }

  /**
   * Validate URL path parameters (e.g., /detail/123, /edit/456)
   */
  validateUrlPath(pathname) {
    // Extract ID from common URL patterns
    const pathPatterns = [
      /\/detail\/([^\/\?]+)/, // /detail/ID
      /\/edit\/([^\/\?]+)/, // /edit/ID
      /\/show\/([^\/\?]+)/, // /show/ID
      /\/view\/([^\/\?]+)/, // /view/ID
      /\/form\/detail\/([^\/\?]+)/, // /form/detail/ID
      /\/form\/detail-form\/([^\/\?]+)/, // /form/detail-form/ID
      /\/user-notes\/detail\/([^\/\?]+)/, // /user-notes/detail/ID
      /\/organization\/detail\/([^\/\?]+)/, // /organization/detail/ID
    ];

    for (const pattern of pathPatterns) {
      const match = pathname.match(pattern);
      if (match) {
        const id = match[1];
        if (!this.isValidId(id)) {
          if (this.config.logWarnings) {
            console.warn(`Invalid ID in URL path: ${id}`);
          }
          return false;
        }
      }
    }

    return true;
  }

  /**
   * Validate query string parameters
   */
  validateQueryParameters(queryString) {
    if (!queryString || queryString === "?") {
      return true; // No parameters to validate
    }

    try {
      const urlParams = new URLSearchParams(queryString);

      for (const [key, value] of urlParams.entries()) {
        if (!this.isValidUrlParameter(key, value)) {
          if (this.config.logWarnings) {
            console.warn(`Invalid URL parameter: ${key}=${value}`);
          }
          return false;
        }
      }

      return true;
    } catch (error) {
      console.error("Error parsing query parameters:", error);
      return false;
    }
  }

  /**
   * Validate a single URL parameter
   */
  isValidUrlParameter(key, value) {
    // Skip validation for empty values
    if (!value || value.trim() === "") {
      return true;
    }

    switch (key.toLowerCase()) {
      case "id":
        return this.isValidId(value);

      case "page":
        return this.isValidPageNumber(value);

      case "perpage":
        return this.isValidPerPage(value);

      case "search":
        return this.isValidSearchTerm(value);

      case "type":
        return this.isValidNotificationType(value);

      case "isread":
      case "status":
        return this.isValidBooleanOrStatus(value);

      default:
        // For unknown parameters, allow them but log a warning
        if (this.config.logWarnings) {
          console.warn(`Unknown URL parameter: ${key}`);
        }
        return true;
    }
  }

  /**
   * Validate ID parameter (should be positive integer)
   */
  isValidId(value) {
    // Must be a positive integer
    const id = parseInt(value, 10);
    return (
      !isNaN(id) &&
      id > 0 &&
      id.toString() === value.trim() &&
      /^\d+$/.test(value.trim())
    );
  }

  /**
   * Validate page number parameter
   */
  isValidPageNumber(value) {
    const page = parseInt(value, 10);
    return (
      !isNaN(page) &&
      page > 0 &&
      page <= 10000 &&
      page.toString() === value.trim()
    );
  }

  /**
   * Validate perPage parameter
   */
  isValidPerPage(value) {
    const perPage = parseInt(value, 10);
    const allowedValues = [10, 25, 50, 100];
    return (
      allowedValues.includes(perPage) && perPage.toString() === value.trim()
    );
  }

  /**
   * Validate search term parameter
   */
  isValidSearchTerm(value) {
    // Allow reasonable search terms (alphanumeric, spaces, common punctuation)
    const searchPattern =
      /^[a-zA-Z0-9\s\-_.,!?@#$%&*()+=\[\]{}|\\:";'<>?/~`]*$/;
    return value.length <= 255 && searchPattern.test(value);
  }

  /**
   * Validate notification type parameter
   */
  isValidNotificationType(value) {
    const allowedTypes = [
      "form_submitted",
      "form_rejected",
      // 'tax_rejected', // Consolidated into form_rejected
      "form_approved",
      "user_note",
      "system_notification",
    ];
    return allowedTypes.includes(value.toLowerCase());
  }

  /**
   * Validate boolean or status parameter
   */
  isValidBooleanOrStatus(value) {
    const allowedValues = [
      "0",
      "1",
      "true",
      "false",
      "yes",
      "no",
      "read",
      "unread",
      "pending",
      "approved",
      "rejected",
    ];
    return allowedValues.includes(value.toLowerCase());
  }

  /**
   * Handle invalid URL detection với redirect tự động
   */
  handleInvalidUrl(reason = "URL không hợp lệ", errorType = "GENERAL_ERROR") {
    const currentPath = window.location.pathname;

    // CRITICAL: Tránh redirect loop - nếu đã ở dashboard thì không redirect nữa
    if (
      currentPath === "/accountant" ||
      currentPath === "/admin/dashboard" ||
      currentPath === "/dashboard"
    ) {
      return;
    }

    if (this.config.logWarnings) {
      console.warn(
        `🚨 ${reason}. Current URL: ${window.location.href}. Error Type: ${errorType}. Redirecting immediately...`
      );
    }

    // Hiển thị toast ngắn gọn nếu được bật (optional)
    if (this.config.showToast) {
      this.showToast("Đang chuyển hướng...", "info");
    }

    if (this.config.autoRedirect) {
      // Redirect ngay lập tức hoặc với delay tối thiểu
      this.redirectToDashboard(this.config.redirectDelay);
    }
  }

  /**
   * Redirect to appropriate dashboard ngay lập tức
   */
  redirectToDashboard(delay = 0) {
    const currentPath = window.location.pathname;
    const dashboardUrl = this.getDashboardUrl();

    // CRITICAL: Tránh redirect loop
    if (currentPath === dashboardUrl) {
      return;
    }

    // Kiểm tra nếu đã ở một dashboard hợp lệ
    if (
      currentPath === "/accountant" ||
      currentPath === "/admin/dashboard" ||
      currentPath === "/dashboard"
    ) {
      return;
    }

    // Đảm bảo không có AJAX requests đang chạy trước khi redirect
    if (typeof $ !== "undefined" && $.active > 0) {
      // Đợi AJAX hoàn thành
      setTimeout(() => this.redirectToDashboard(delay), 100);
      return;
    }

    // Cleanup loading state trước khi redirect
    if (typeof window.cleanupBeforeRedirect === "function") {
      window.cleanupBeforeRedirect();
    }

    // Set flag để tránh validation conflicts
    window.isRedirecting = true;

    // Redirect ngay lập tức hoặc với delay tối thiểu
    if (delay === 0) {
      window.location.href = dashboardUrl;
    } else {
      setTimeout(() => {
        window.location.href = dashboardUrl;
      }, delay);
    }
  }

  /**
   * Show toast notification
   */
  showToast(message, type = "info") {
    // Try to use existing toast system if available
    if (
      typeof window.notificationManager !== "undefined" &&
      typeof window.notificationManager.showToast === "function"
    ) {
      window.notificationManager.showToast(message, type);
      return;
    }

    // Fallback: create simple toast
    const alertClass =
      type === "success"
        ? "alert-success"
        : type === "error"
        ? "alert-danger"
        : type === "warning"
        ? "alert-warning"
        : "alert-info";

    const toast = document.createElement("div");
    toast.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    toast.style.cssText =
      "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
    toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;

    document.body.appendChild(toast);

    // Auto-remove after 3 seconds
    setTimeout(() => {
      if (toast.parentElement) {
        toast.remove();
      }
    }, 3000);
  }

  /**
   * Validate any URL (static method)
   */
  static validateUrl(url = null) {
    const targetUrl = url || window.location.href;

    try {
      const urlObj = new URL(targetUrl, window.location.origin);
      const validator = new UrlValidator();
      validator.config.enabled = false; // Prevent auto-validation

      // Kiểm tra route có tồn tại không
      if (!validator.isValidRoute(urlObj.pathname)) {
        return false;
      }

      return (
        validator.validateUrlPath(urlObj.pathname) &&
        validator.validateQueryParameters(urlObj.search)
      );
    } catch (error) {
      console.error("Error validating URL:", error);
      return false;
    }
  }

  /**
   * Kiểm tra xem URL có phải là route hợp lệ không (static method)
   */
  static isValidRoute(url = null) {
    const targetUrl = url || window.location.href;

    try {
      const urlObj = new URL(targetUrl, window.location.origin);
      const validator = new UrlValidator();
      validator.config.enabled = false;

      return validator.isValidRoute(urlObj.pathname);
    } catch (error) {
      console.error("Error checking route validity:", error);
      return false;
    }
  }

  /**
   * Sanitize URL by removing invalid parameters (static method)
   */
  static sanitizeUrl(url = null) {
    const targetUrl = url || window.location.href;

    try {
      const urlObj = new URL(targetUrl, window.location.origin);
      const params = new URLSearchParams(urlObj.search);
      const cleanParams = new URLSearchParams();

      const validator = new UrlValidator();
      validator.config.enabled = false; // Prevent auto-validation

      for (const [key, value] of params.entries()) {
        if (validator.isValidUrlParameter(key, value)) {
          cleanParams.append(key, value);
        }
      }

      urlObj.search = cleanParams.toString();
      return urlObj.toString();
    } catch (error) {
      console.error("Error sanitizing URL:", error);
      return window.location.origin + window.location.pathname;
    }
  }
}

// Initialize URL validator when page is fully loaded
// Sử dụng 'load' event thay vì 'DOMContentLoaded' để tránh conflict với page loading
window.addEventListener("load", function () {
  // Đợi thêm một chút để đảm bảo tất cả scripts đã chạy
  setTimeout(() => {
    window.urlValidator = new UrlValidator();
  }, 50);
});

// Export global functions for backward compatibility
window.validateUrl = UrlValidator.validateUrl;
window.sanitizeUrl = UrlValidator.sanitizeUrl;
window.isValidRoute = UrlValidator.isValidRoute;

/**
 * Cleanup loading state trước khi redirect
 */
window.cleanupBeforeRedirect = function () {
  // Hide loading indicators
  if (typeof hideLoadingIndicator === "function") {
    hideLoadingIndicator();
  }

  // Remove AJAX loaders
  if (typeof $ !== "undefined") {
    $("#ajax-loader").remove();
    $(".spinner-border").remove();
  }

  // Clear any pending timeouts
  if (window.redirectTimer) {
    clearTimeout(window.redirectTimer);
  }
};

window.safeNavigate = function (url, useAjax = true) {
  // Kiểm tra URL có hợp lệ không
  if (!UrlValidator.validateUrl(url)) {
    console.warn(
      "Invalid URL detected, redirecting immediately to dashboard:",
      url
    );

    // Cleanup trước khi redirect
    window.cleanupBeforeRedirect();

    // Xác định dashboard URL phù hợp
    const dashboardUrl = window.location.pathname.includes("/accountant")
      ? "/accountant"
      : window.location.pathname.includes("/admin")
      ? "/admin/dashboard"
      : "/dashboard";

    // Redirect ngay lập tức
    window.location.href = dashboardUrl;
    return;
  }

  // URL hợp lệ, tiến hành navigation
  if (useAjax && typeof window.navigateAjax === "function") {
    window.navigateAjax(url);
  } else {
    window.location.href = url;
  }
};

/**
 * Kiểm tra và xử lý URL trước khi navigation
 * @param {string} url - URL cần kiểm tra
 * @returns {object} - Kết quả kiểm tra
 */
window.checkUrlBeforeNavigation = function (url) {
  try {
    // Kiểm tra route có tồn tại không
    if (!UrlValidator.isValidRoute(url)) {
      return {
        isValid: false,
        errorType: "ROUTE_NOT_FOUND",
        message: "Trang không tồn tại",
        suggestedAction: "redirect_to_dashboard",
      };
    }

    // Kiểm tra URL parameters
    if (!UrlValidator.validateUrl(url)) {
      return {
        isValid: false,
        errorType: "INVALID_PARAMETERS",
        message: "Tham số URL không hợp lệ",
        suggestedAction: "redirect_to_dashboard",
      };
    }

    return {
      isValid: true,
      message: "URL hợp lệ",
    };
  } catch (error) {
    return {
      isValid: false,
      errorType: "MALFORMED_URL",
      message: "URL không đúng định dạng",
      suggestedAction: "redirect_to_dashboard",
    };
  }
};
