/**
 * Notification System JavaScript
 * Handles notification dropdown, AJAX loading, and real-time updates
 */

class NotificationManager {
  constructor() {
    this.apiBaseUrl = "/notifications";
    this.isLoading = false;
    this.notifications = [];
    this.unreadCount = 0;

    // Modal-related properties
    this.modalIsLoading = false;
    this.modalNotifications = [];
    this.modalPagination = {};
    this.modalFilters = {
      isRead: "",
      type: "",
      search: "",
      perPage: 10,
      page: 1,
    };

    this.init();
  }

  init() {
    this.bindEvents();
    this.loadUnreadCount();

    // Auto-refresh every 30 seconds
    setInterval(() => {
      this.loadUnreadCount();
    }, 30000);
  }

  bindEvents() {
    // Load notifications when dropdown is opened
    $("#notification-bell").on("click", (e) => {
      e.preventDefault();
      this.loadNotifications();
    });

    // Handle notification item clicks for navigation
    $(document).on("click", ".notification-item", (e) => {
      e.preventDefault();
      const $item = $(e.currentTarget);
      const notificationId = $item.data("notification-id");
      const refId = $item.data("ref-id");

      console.log("Notification item clicked:", { notificationId, refId });

      // Navigate to form detail if refId exists and is valid (not 0)
      if (refId && refId !== 0) {
        this.navigateToFormDetail(refId);
      }

      // Mark as read if unread
      if (notificationId && !$item.hasClass("read")) {
        this.markAsRead(notificationId);
      }
    });

    // Handle modal notification item clicks for navigation
    $(document).on("click", ".notification-modal-item", (e) => {
      // Don't navigate if clicking on action buttons
      if ($(e.target).closest(".notification-modal-actions").length > 0) {
        return;
      }

      e.preventDefault();
      const $item = $(e.currentTarget);
      const notificationId = $item.data("notification-id");
      const refId = $item.data("ref-id");

      console.log("Modal notification item clicked:", {
        notificationId,
        refId,
      });

      // Mark as read if unread (before navigation) and reload modal
      if (notificationId && !$item.hasClass("read")) {
        this.markAsReadAndReloadModal(notificationId);
      }

      // Navigate to form detail if refId exists and is valid (not 0)
      if (refId && refId !== 0) {
        this.navigateToFormDetail(refId);
      }
    });

    // View all notifications modal
    $(document).on("click", "#view-all-notifications-btn", (e) => {
      e.preventDefault();
      this.openAllNotificationsModal();
    });

    // Modal event handlers
    this.bindModalEvents();
  }

  async loadUnreadCount() {
    try {
      const response = await $.ajax({
        url: `${this.apiBaseUrl}/unread-count`,
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (response.success) {
        this.unreadCount = response.data.unread_count;
        this.updateUnreadCountDisplay();
      }
    } catch (error) {
      console.error("Error loading unread count:", error);
    }
  }

  async loadNotifications() {
    if (this.isLoading) return;

    this.isLoading = true;
    this.showLoadingState();

    try {
      // Load recent notifications (limited to 3 for dropdown)
      const notificationsResponse = await $.ajax({
        url: `${this.apiBaseUrl}/recent?limit=3`,
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      // Load total unread count separately to ensure accuracy
      const countResponse = await $.ajax({
        url: `${this.apiBaseUrl}/unread-count`,
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (notificationsResponse.success && countResponse.success) {
        this.notifications = notificationsResponse.data.notifications;
        this.unreadCount = countResponse.data.unread_count; // Use the accurate total count
        this.renderNotifications();
        this.updateUnreadCountDisplay();
      } else {
        this.showErrorState(__("notification.failed_to_load_notifications"));
      }
    } catch (error) {
      console.error("Error loading notifications:", error);
      this.showErrorState(__("notification.failed_to_load_notifications"));
    } finally {
      this.isLoading = false;
    }
  }

  async markAsRead(notificationId) {
    try {
      const response = await $.ajax({
        url: `${this.apiBaseUrl}/mark-as-read/${notificationId}`,
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (response.success) {
        this.unreadCount = response.data.unread_count;
        this.updateUnreadCountDisplay();

        // Update the notification item visually
        $(`.notification-item[data-notification-id="${notificationId}"]`)
          .addClass("read")
          .removeClass("unread");
      }
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  }

  /**
   * Mark notification as read and reload modal to reflect changes
   * Used when clicking on unread notifications in modal for navigation
   */
  async markAsReadAndReloadModal(notificationId) {
    try {
      const response = await $.ajax({
        url: `${this.apiBaseUrl}/mark-as-read/${notificationId}`,
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (response.success) {
        this.unreadCount = response.data.unread_count;
        this.updateUnreadCountDisplay();

        // Update the notification item visually in dropdown
        $(`.notification-item[data-notification-id="${notificationId}"]`)
          .addClass("read")
          .removeClass("unread");

        // Reload modal notifications to reflect the status change
        this.loadModalNotifications();
      }
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  }

  async markAllAsRead() {
    try {
      const response = await $.ajax({
        url: `${this.apiBaseUrl}/mark-all-as-read`,
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (response.success) {
        this.unreadCount = 0;
        this.updateUnreadCountDisplay();

        // Update all notification items visually
        $(".notification-item.unread").addClass("read").removeClass("unread");

        // Show success message
        this.showToast(__("notification.mark_all_read"), "success");
      }
    } catch (error) {
      console.error("Error marking all notifications as read:", error);
      this.showToast(__("notification.error_marking_note_as_read"), "error");
    }
  }

  renderNotifications() {
    const $header = $("#notification-header");
    const $list = $("#notification-list");

    // Update header
    $header.html(
      `${this.unreadCount} ${__("notification.unread_notifications")}`
    );

    // Clear existing notifications
    $list.empty();

    if (this.notifications.length === 0) {
      $list.html(`
                <div class="dropdown-item text-center text-muted py-3">
                    <i class="fas fa-bell-slash me-2"></i>
                    ${__("notification.no_notification_found")}
                </div>
            `);
      return;
    }

    // Render notifications
    this.notifications.forEach((notification) => {
      const $item = this.createNotificationItem(notification);
      $list.append($item);

      // Add divider except for last item
      if (notification !== this.notifications[this.notifications.length - 1]) {
        $list.append('<div class="dropdown-divider"></div>');
      }
    });
  }

  createNotificationItem(notification) {
    const isRead = notification.isRead;
    const readClass = isRead ? "read" : "unread";
    const bgClass = isRead ? "" : 'style="background-color: #EBF8A4;"';
    const timeAgo = this.formatTimeAgo(notification.created_at);
    const icon = this.getNotificationIcon(notification.type);

    // Truncate title and body text
    const truncatedTitle = this.truncateText(notification.title, 35);
    const truncatedBody = this.truncateText(notification.body, 50);

    // Add clickable indicator if refId exists and is valid (not 0)
    const clickableIndicator =
      notification.refId && notification.refId !== 0
        ? `<i class="fas fa-external-link-alt ms-1" style="font-size: 10px; opacity: 0.6;" title="${__(
            "notification.click_to_view_details_title"
          )}"></i>`
        : "";

    return $(`
            <a href="#" class="dropdown-item notification-item ${readClass}  d-flex align-items-start py-2 px-3" ${bgClass}
               data-notification-id="${notification.id}" data-ref-id="${
      notification.refId || ""
    }" style="max-width: 350px;">
                <div class="d-flex align-items-start w-100">
                    <i class="${icon} me-3 mt-2 flex-shrink-0"
                        style="font-size: 14px; width: 16px;"></i>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold notification-title text-truncate" style="font-size: 13px; line-height: 1.3;">
                            ${this.escapeHtml(
                              truncatedTitle
                            )}${clickableIndicator}
                        </div>
                        <div class="text-muted notification-body text-truncate" style="font-size: 12px; line-height: 1.2;">${this.escapeHtml(
                          truncatedBody
                        )}</div>
                        <small class="text-secondary" style="font-size: 11px;">${timeAgo}</small>
                    </div>
                </div>
            </a>
        `);
  }

  truncateText(text, maxLength) {
    if (!text) return "";
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + "...";
  }

  getNotificationIcon(type) {
    const iconMap = {
      form_submitted_by_submitter: "fa-solid fa-bell text-info",
      form_rejected_by_approver: "fa-solid fa-times-circle text-danger",
      form_approved_by_approver: "fa-solid fa-circle-check text-success",
    };

    return iconMap[type] || "fa-solid fa-bell text-info";
  }

  updateUnreadCountDisplay() {
    const $badge = $("#notification-count");

    if (this.unreadCount > 0) {
      $badge.text(this.unreadCount).show();
    } else {
      $badge.hide();
    }
  }

  showLoadingState() {
    $("#notification-header").html(
      `<i class="fas fa-spinner fa-spin"></i> ${__(
        "notification.loading_notifications"
      )}`
    );
    $("#notification-list").html(`
            <div class="dropdown-item text-center py-3">
                <i class="fas fa-spinner fa-spin me-2"></i>
                ${__("notification.loading_notifications")}
            </div>
        `);
  }

  showErrorState(message) {
    $("#notification-header").html(
      __("notification.failed_to_load_notifications")
    );
    $("#notification-list").html(`
            <div class="dropdown-item text-center text-danger py-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `);
  }

  formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) {
      return __("notification.just_now");
    } else if (diffInSeconds < 3600) {
      const minutes = Math.floor(diffInSeconds / 60);
      return `${minutes} ${__("notification.minutes_ago")}`;
    } else if (diffInSeconds < 86400) {
      const hours = Math.floor(diffInSeconds / 3600);
      return `${hours} ${__("notification.hours_ago")}`;
    } else {
      const days = Math.floor(diffInSeconds / 86400);
      return `${days} ${__("notification.days_ago")}`;
    }
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Navigate to form detail page based on refId
   * @param {string|number} refId - The form ID to navigate to
   */
  navigateToFormDetail(refId) {
    if (!refId || refId === 0) {
      console.warn("No valid refId provided for navigation");
      return;
    }

    // Validate the refId using the global URL validator
    if (
      typeof window.urlValidator !== "undefined" &&
      !window.urlValidator.isValidId(refId.toString())
    ) {
      console.error("Invalid refId format for navigation:", refId);
      // Redirect ngay lập tức về dashboard
      const dashboardUrl = window.location.pathname.includes("/accountant")
        ? "/accountant"
        : "/admin/dashboard";
      window.location.href = dashboardUrl;
      return;
    }

    try {
      // First, check if the user has permission to view this form
      this.checkFormAccessPermission(refId)
        .then((permissionResult) => {
          if (permissionResult.hasAccess) {
            this.performNavigation(refId, permissionResult.route);
          } else {
            this.handleAccessDenied(permissionResult.message, refId);
          }
        })
        .catch((error) => {
          console.error("Error checking form access permission:", error);
          // Fallback to direct navigation attempt
          this.performNavigation(refId);
        });
    } catch (error) {
      console.error("Error navigating to form detail:", error);
      // Fallback to regular navigation
      this.performNavigation(refId);
    }
  }

  /**
   * Check if user has permission to access the form
   * @param {string|number} refId - The form ID to check
   * @returns {Promise} Promise resolving to permission result
   */
  async checkFormAccessPermission(refId) {
    try {
      const response = await $.ajax({
        url: `/api/form/${refId}/check-access`,
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (response.success) {
        return {
          hasAccess: response.data.hasAccess,
          route: response.data.route || null,
          message: response.message || "Access granted",
        };
      } else {
        // If API call fails, determine route based on context
        const isAccountantContext =
          window.location.pathname.includes("/accountant");
        const route = isAccountantContext
          ? `/accountant/form/detail/${refId}`
          : `/admin/form/detail-form/${refId}`;

        return {
          hasAccess: true, // Assume access if API fails
          route: route,
          message: "Permission check failed, attempting navigation",
        };
      }
    } catch (error) {
      console.warn("Permission check API failed:", error);
      // Fallback to context-based routing
      const isAccountantContext =
        window.location.pathname.includes("/accountant");
      const route = isAccountantContext
        ? `/accountant/form/detail/${refId}`
        : `/admin/form/detail-form/${refId}`;

      return {
        hasAccess: true,
        route: route,
        message: "Permission check unavailable, attempting navigation",
      };
    }
  }

  /**
   * Perform the actual navigation
   * @param {string|number} refId - The form ID
   * @param {string} route - The route to navigate to (optional)
   */
  performNavigation(refId, route = null) {
    let detailUrl = route;

    if (!detailUrl) {
      // Determine the correct route based on current user role and context
      const isAccountantContext =
        window.location.pathname.includes("/accountant");

      if (isAccountantContext) {
        // Accountant form detail route - use general detail route since we don't have form type context
        detailUrl = `/accountant/form/detail/${refId}`;
      } else {
        // Regular admin/CRM form detail route
        detailUrl = `/admin/form/detail-form/${refId}`;
      }
    }

    console.log("Navigating to form detail:", detailUrl);

    // Use safe navigation if available, otherwise fallback to regular navigation
    if (typeof window.safeNavigate === "function") {
      window.safeNavigate(detailUrl);
    } else {
      // Check if AJAX navigation is available and use it, otherwise use regular navigation
      if (typeof window.navigateAjax === "function") {
        // Use AJAX navigation if available
        window.navigateAjax(detailUrl);
      } else {
        // Fallback to regular navigation
        window.location.href = detailUrl;
      }
    }
  }

  /**
   * Handle access denied scenarios
   * @param {string} message - The access denied message
   * @param {string|number} refId - The form ID that was denied
   */
  handleAccessDenied(message, refId) {
    console.warn("Access denied for form:", refId, message);

    // Show user-friendly message
    this.showToast(__("notification.no_permission_form"), "warning");
  }

  showToast(message, type = "info") {
    // Simple toast notification (you can enhance this with a proper toast library)
    const alertClass =
      type === "success"
        ? "alert-success"
        : type === "error"
        ? "alert-danger"
        : "alert-info";

    const $toast = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

    $("body").append($toast);

    // Auto-remove after 3 seconds
    setTimeout(() => {
      $toast.alert("close");
    }, 3000);
  }

  // Modal-related methods
  bindModalEvents() {
    // Filter change events
    $("#notificationStatusFilter, #notificationTypeFilter").on("change", () => {
      this.updateModalFilters();
      this.loadModalNotifications();
    });

    // Search input with debounce
    let searchTimeout;
    $("#notificationSearchFilter").on("input", () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        this.updateModalFilters();
        this.loadModalNotifications();
      }, 500);
    });

    // Clear search button
    $("#clearSearchBtn").on("click", () => {
      $("#notificationSearchFilter").val("");
      this.updateModalFilters();
      this.loadModalNotifications();
    });

    // Per page change - use event delegation to ensure it works even if modal is dynamically loaded
    $(document).on("change", "#notificationsPerPage", () => {
      const newPerPage = parseInt($("#notificationsPerPage").val());

      this.modalFilters.perPage = newPerPage;
      this.modalFilters.page = 1; // Reset to first page
      this.loadModalNotifications();
    });

    // Mark all as read
    $("#markAllReadBtn").on("click", () => {
      this.markAllAsReadModal();
    });

    // Refresh notifications
    $("#refreshNotificationsBtn").on("click", () => {
      this.loadModalNotifications();
    });

    // Pagination clicks
    $(document).on("click", ".notification-pagination-link", (e) => {
      e.preventDefault();
      const page = $(e.currentTarget).data("page");
      if (page && page !== this.modalFilters.page) {
        this.modalFilters.page = page;
        this.loadModalNotifications();
      }
    });

    // Individual notification actions
    $(document).on("click", ".toggle-read-btn", (e) => {
      e.stopPropagation();
      const notificationId = $(e.currentTarget).data("notification-id");
      this.toggleNotificationReadStatus(notificationId);
    });
  }

  openAllNotificationsModal() {
    // Reset filters to defaults
    this.modalFilters = {
      isRead: "",
      type: "",
      search: "",
      perPage: 10,
      page: 1,
    };

    // Reset form values
    $("#notificationStatusFilter").val("");
    $("#notificationTypeFilter").val("");
    $("#notificationSearchFilter").val("");
    $("#notificationsPerPage").val("10");

    // Show modal
    const modal = new bootstrap.Modal(
      document.getElementById("allNotificationsModal")
    );
    modal.show();

    // Ensure dropdown value is synced after modal is shown
    setTimeout(() => {
      const currentDropdownValue = parseInt($("#notificationsPerPage").val());
      if (currentDropdownValue !== this.modalFilters.perPage) {
        this.modalFilters.perPage = currentDropdownValue;
      }
    }, 100);

    // Load notifications
    this.loadModalNotifications();
  }

  updateModalFilters() {
    this.modalFilters.isRead = $("#notificationStatusFilter").val();
    this.modalFilters.type = $("#notificationTypeFilter").val();
    this.modalFilters.search = $("#notificationSearchFilter").val();
    this.modalFilters.page = 1; // Reset to first page when filters change
  }

  async loadModalNotifications() {
    if (this.modalIsLoading) return;

    this.modalIsLoading = true;
    this.showModalLoadingState();

    try {
      const params = new URLSearchParams({
        page: this.modalFilters.page,
        per_page: this.modalFilters.perPage,
      });

      if (this.modalFilters.isRead !== "") {
        params.append("is_read", this.modalFilters.isRead);
      }
      if (this.modalFilters.type !== "") {
        params.append("type", this.modalFilters.type);
      }
      if (this.modalFilters.search !== "") {
        params.append("search", this.modalFilters.search);
      }

      const response = await $.ajax({
        url: `${this.apiBaseUrl}/paginated?${params.toString()}`,
        method: "GET",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (response.success) {
        console.log("API Response received:", {
          total: response.data.pagination.total,
          perPage: response.data.pagination.per_page,
          lastPage: response.data.pagination.last_page,
          requestedPerPage: this.modalFilters.perPage,
        });

        this.modalNotifications = response.data.notifications;
        this.modalPagination = response.data.pagination;
        this.unreadCount = response.data.unread_count;

        this.renderModalNotifications();
        this.renderModalPagination();
        this.updateUnreadCountDisplay();
        this.updateModalCountInfo();
      } else {
        this.showModalErrorState(
          __("notification.failed_to_load_notifications")
        );
      }
    } catch (error) {
      console.error("Error loading modal notifications:", error);
      this.showModalErrorState(__("notification.failed_to_load_notifications"));
    } finally {
      this.modalIsLoading = false;
    }
  }

  renderModalNotifications() {
    const $container = $("#notificationsListContainer");

    if (this.modalNotifications.length === 0) {
      $container.html(`
                <div class="notification-empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h5>${__("notification.no_notification_found")}</h5>
                    <p class="text-muted">${__(
                      "notification.not_found_warning"
                    )}</p>
                </div>
            `);
      return;
    }

    let html = "";
    this.modalNotifications.forEach((notification) => {
      const isRead = notification.isRead;
      const readClass = isRead ? "read" : "unread";
      const timeAgo = this.formatTimeAgo(notification.created_at);
      const icon = this.getNotificationIcon(notification.type);

      // Check if notification is overdue (more than 7 days old and unread)
      const isOverdue =
        !isRead && this.isNotificationOverdue(notification.created_at);
      const overrideClass = isOverdue ? "overdue" : "";

      // Add clickable indicator if refId exists and is valid (not 0)
      const clickableIndicator =
        notification.refId && notification.refId !== 0
          ? `<i class="fas fa-external-link-alt ms-2" style="font-size: 12px; opacity: 0.6;" title="${__(
              "notification.click_to_view_details_title"
            )}"></i>`
          : "";

      // Wrap each notification in its own container div for better structure
      html += `
                <div class="notification-container mb-2 border-bottom" style="cursor: pointer;">
                    <div class="notification-modal-item notification-item-divider ${readClass} ${overrideClass} py-3 px-4" ${
        isRead ? "" : 'style="background-color: #EBF8A4;"'
      } data-notification-id="${notification.id}" data-ref-id="${
        notification.refId || ""
      }">
                        <div class="d-flex align-items-start w-100">
                            <i class="${icon} me-3 flex-shrink-0" style="padding-top: 9px;"></i>
                            <div class="flex-grow-1 min-width-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="notification-modal-title" style="font-size: 18px; line-height: 1.4;">${this.escapeHtml(
                                      notification.title
                                    )}${clickableIndicator}</div>
                                    <div class="notification-modal-actions ms-auto">
                                        <button type="button" class="btn btn-sm ${
                                          isRead
                                            ? "btn-outline-warning"
                                            : "btn-outline-success"
                                        } toggle-read-btn"
                                                data-notification-id="${
                                                  notification.id
                                                }" title="${
        isRead ? "Mark as unread" : "Mark as read"
      }">
                                            <i class="fas ${
                                              isRead
                                                ? "fa-eye-slash"
                                                : "fa-check"
                                            }"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="notification-modal-body" style="font-size: 14px;color: #6c757d; line-height: 1.0;">${this.escapeHtml(
                                  notification.body
                                )}</div>
                                <div class="notification-modal-meta mt-1">
                                    <span>
                                        <i class="fas fa-clock me-1"></i>${timeAgo}
                                        ${
                                          isOverdue
                                            ? `<span class="badge bg-danger ms-2">${__(
                                                "notification.overdue"
                                              )}</span>`
                                            : ""
                                        }
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    });

    $container.html(html);
  }

  renderModalPagination() {
    const $pagination = $("#notificationsPagination");
    const $paginationList = $("#notificationsPaginationList");

    // Hide pagination if there's only one page OR if total records fit within per_page limit
    // Ensure both values are numbers for proper comparison
    const totalRecords = parseInt(this.modalPagination.total) || 0;
    const perPageLimit = parseInt(this.modalFilters.perPage) || 10;
    const totalPages = parseInt(this.modalPagination.last_page) || 1;

    const shouldHide = totalPages <= 1 || totalRecords <= perPageLimit;

    // Debug logging to verify the logic
    console.log("Notification Pagination Debug:", {
      totalRecords,
      perPageLimit,
      totalPages,
      shouldHide,
      condition1: totalPages <= 1,
      condition2: totalRecords <= perPageLimit,
      paginationElement: $pagination.length,
      currentDisplay: $pagination.css("display"),
    });

    if (shouldHide) {
      console.log("Hiding pagination - conditions met");
      $pagination.css("display", "none");
      console.log("After hide - display:", $pagination.css("display"));

      // Add visual indicator
      this.updatePaginationStatus("HIDDEN", "red");
      return;
    }

    console.log("Showing pagination - conditions not met");
    $pagination.css("display", "block");
    console.log("After show - display:", $pagination.css("display"));

    // Add visual indicator
    this.updatePaginationStatus("SHOWN", "green");

    let html = "";
    const currentPage = this.modalPagination.current_page;
    const lastPage = this.modalPagination.last_page;

    // Previous button
    html += `
            <li class="page-item ${currentPage === 1 ? "disabled" : ""}">
                <a class="page-link notification-pagination-link" href="#" data-page="${
                  currentPage - 1
                }">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(lastPage, currentPage + 2);

    if (startPage > 1) {
      html += `<li class="page-item"><a class="page-link notification-pagination-link" href="#" data-page="1">1</a></li>`;
      if (startPage > 2) {
        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      html += `
                <li class="page-item ${i === currentPage ? "active" : ""}">
                    <a class="page-link notification-pagination-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
    }

    if (endPage < lastPage) {
      if (endPage < lastPage - 1) {
        html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
      html += `<li class="page-item"><a class="page-link notification-pagination-link" href="#" data-page="${lastPage}">${lastPage}</a></li>`;
    }

    // Next button
    html += `
            <li class="page-item ${currentPage === lastPage ? "disabled" : ""}">
                <a class="page-link notification-pagination-link" href="#" data-page="${
                  currentPage + 1
                }">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;

    $paginationList.html(html);
  }

  showModalLoadingState() {
    $("#notificationsListContainer").html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                <p class="mt-2 text-muted">${__(
                  "notification.loading_notifications"
                )}</p>
            </div>
        `);
    $("#notificationsPagination").hide();
    $("#notificationCountInfo").text(__("notification.loading_notifications"));
  }

  showModalErrorState(message) {
    $("#notificationsListContainer").html(`
            <div class="notification-empty-state">
                <i class="fas fa-exclamation-triangle text-danger"></i>
                <h5 class="text-danger">${__("notification.error_title")}</h5>
                <p class="text-muted">${message}</p>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.notificationManager.loadModalNotifications()">
                    <i class="fas fa-retry me-1"></i>${__("common.try_again")}
                </button>
            </div>
        `);
    $("#notificationsPagination").hide();
    $("#notificationCountInfo").text(
      __("notification.failed_to_load_notifications")
    );
  }

  updateModalCountInfo() {
    const { from, to, total } = this.modalPagination;
    const unreadText =
      this.unreadCount > 0 ? ` (${this.unreadCount} unread)` : "";
    $("#notificationCountInfo").html(`
            Showing ${from || 0} to ${to || 0} of ${
      total || 0
    } notifications${unreadText}
        `);
  }

  updatePaginationStatus(status, color) {
    // Remove existing status indicator
    $("#paginationStatus").remove();

    // Add new status indicator
    $("#notificationCountInfo").append(`
            <span id="paginationStatus" style="margin-left: 10px; color: ${color}; font-weight: bold;">
                [PAGINATION ${status}]
            </span>
        `);
  }

  isNotificationOverdue(createdAt) {
    const created = new Date(createdAt);
    const now = new Date();
    const diffInDays = Math.floor((now - created) / (1000 * 60 * 60 * 24));
    return diffInDays > 7;
  }

  async toggleNotificationReadStatus(notificationId) {
    try {
      const response = await $.ajax({
        url: `${this.apiBaseUrl}/toggle-read-status/${notificationId}`,
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (response.success) {
        this.unreadCount = response.data.unread_count;
        this.updateUnreadCountDisplay();

        // Update the notification in the modal
        const $notificationItem = $(
          `.notification-modal-item[data-notification-id="${notificationId}"]`
        );
        const $toggleBtn = $(
          `.toggle-read-btn[data-notification-id="${notificationId}"]`
        );

        if (response.data.notification.isRead) {
          // Mark as read - remove yellow background and update classes
          $notificationItem.removeClass("unread").addClass("read");
          $notificationItem.css("background-color", ""); // Remove yellow background
          $toggleBtn
            .removeClass("btn-outline-success")
            .addClass("btn-outline-warning")
            .html('<i class="fas fa-eye-slash"></i>')
            .attr("title", "Mark as unread");
        } else {
          // Mark as unread - add yellow background and update classes
          $notificationItem.removeClass("read").addClass("unread");
          $notificationItem.css("background-color", "#EBF8A4"); // Add yellow background
          $toggleBtn
            .removeClass("btn-outline-warning")
            .addClass("btn-outline-success")
            .html('<i class="fas fa-check"></i>')
            .attr("title", "Mark as read");
        }

        this.updateModalCountInfo();
        this.showToast(
          __("notification.note_marked_as_read_success"),
          "success"
        );
      }
    } catch (error) {
      console.error("Error toggling notification status:", error);
      this.showToast(__("notification.error_marking_note_as_read"), "error");
    }
  }

  async markAllAsReadModal() {
    if (this.unreadCount === 0) {
      this.showToast(__("notification.unread_notifications"), "info");
      return;
    }

    try {
      const response = await $.ajax({
        url: `${this.apiBaseUrl}/mark-all-as-read`,
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });

      if (response.success) {
        this.unreadCount = 0;
        this.updateUnreadCountDisplay();

        // Update all notification items in modal
        $(".notification-modal-item.unread")
          .removeClass("unread")
          .addClass("read")
          .css("background-color", ""); // Remove yellow background from all unread items
        $(".toggle-read-btn")
          .removeClass("btn-outline-success")
          .addClass("btn-outline-warning")
          .html('<i class="fas fa-eye-slash"></i>')
          .attr("title", __("notification.mark_as_unread_title"));

        this.updateModalCountInfo();
        this.showToast(__("notification.mark_all_read"), "success");
      }
    } catch (error) {
      console.error("Error marking all notifications as read:", error);
      this.showToast(__("notification.error_marking_note_as_read"), "error");
    }
  }
}

// Test function for pagination logic
function testNotificationPagination() {
  if (!window.notificationManager) {
    console.log("Notification manager not initialized");
    return;
  }

  console.log("Testing pagination scenarios...");

  // Test scenario 1: 37 total, 50 per page (should hide)
  window.notificationManager.modalPagination = {
    total: 37,
    last_page: 1,
    current_page: 1,
  };
  window.notificationManager.modalFilters.perPage = 50;
  console.log("Test 1: 37 total, 50 per page (should hide)");
  window.notificationManager.renderModalPagination();

  setTimeout(() => {
    // Test scenario 2: 37 total, 25 per page (should show)
    window.notificationManager.modalPagination = {
      total: 37,
      last_page: 2,
      current_page: 1,
    };
    window.notificationManager.modalFilters.perPage = 25;
    console.log("Test 2: 37 total, 25 per page (should show)");
    window.notificationManager.renderModalPagination();
  }, 2000);
}

// Global test function that can be called from browser console
window.testPaginationHiding = function () {
  console.log("=== Testing Pagination Hiding Logic ===");

  if (!window.notificationManager) {
    console.log("❌ Notification manager not initialized");
    return;
  }

  // Test 1: 37 total, 50 per page (should hide)
  console.log("\n🧪 Test 1: 37 total, 50 per page (should HIDE)");
  window.notificationManager.modalPagination = {
    total: 37,
    last_page: 1,
    current_page: 1,
  };
  window.notificationManager.modalFilters.perPage = 50;
  window.notificationManager.renderModalPagination();

  setTimeout(() => {
    // Test 2: 37 total, 25 per page (should show)
    console.log("\n🧪 Test 2: 37 total, 25 per page (should SHOW)");
    window.notificationManager.modalPagination = {
      total: 37,
      last_page: 2,
      current_page: 1,
    };
    window.notificationManager.modalFilters.perPage = 25;
    window.notificationManager.renderModalPagination();

    setTimeout(() => {
      // Test 3: 37 total, 100 per page (should hide)
      console.log("\n🧪 Test 3: 37 total, 100 per page (should HIDE)");
      window.notificationManager.modalPagination = {
        total: 37,
        last_page: 1,
        current_page: 1,
      };
      window.notificationManager.modalFilters.perPage = 100;
      window.notificationManager.renderModalPagination();

      console.log(
        "\n✅ All tests completed. Check the visual indicators and console logs above."
      );
    }, 2000);
  }, 2000);
};

// Initialize notification manager when document is ready
$(document).ready(function () {
  window.notificationManager = new NotificationManager();

  // Add test button to page for debugging
  setTimeout(() => {
    if ($("#testPaginationBtn").length === 0) {
      $("#testPaginationBtn").on("click", window.testPaginationHiding);
    }
  }, 1000);
});
