// Main content container selector
const contentSelector = "#content";
const defaultTitle = document.title;
/**
 * AJAX Navigation System
 * Loads page content without full page refresh
 */
$(document).ready(function () {
  // Store the initial page title

  // Initialize history state
  if (window.history && window.history.replaceState) {
    window.history.replaceState(
      { url: window.location.href, title: document.title },
      defaultTitle,
      window.location.href
    );
  }

  //chuẩn hóa url
  function normalizeUrl(u) {
    try {
      return new URL(u, window.location.origin).toString();
    } catch (e) {
      return u;
    }
  }

  //lấy test sạch
  function getLinkText($link) {
    const cloned = $link.clone();
    cloned.find("i, svg").remove();
    return cloned.text().trim();
  }

  /**
   * Handle AJAX links
   */
  $(document).on("click", "a.ajax-link", function (e) {
    e.preventDefault();
    console.log("ajax link click");
    const url = $(this).attr("href");
    const linkText = getLinkText($(this));

    // Don't reload if clicking the current page
    if (normalizeUrl(window.location.href) === normalizeUrl(url)) {
      return false;
    }

    // Load the page via AJAX
    loadPageContent(url, linkText, true);

    // Handle sidebar navigation highlighting
    updateSidebarActiveState($(this));

    return false;
  });

  /**
   * Handle browser back/forward buttons
   */
  // $(window).on("popstate", function (e) {
  //   if (e.originalEvent.state) {
  //     const state = e.originalEvent.state;
  //     loadPageContent(state.path, state.title, true);
  //   }
  // });

  /**
   * Load page content via AJAX
   * @param {string} url - The URL to load
   * @param {string} linkText - Text from the clicked link (for title)
   * @param {boolean} pushState - Whether to push to browser history
   */

  /**
   * Update the active state in the sidebar
   */
  function updateSidebarActiveState($clickedLink) {
    console.log("sidebar clicked");
    // Remove active class from all links
    $(".sidebar-menu .nav-link").removeClass("active");

    // Add active class to clicked link and its parents
    $clickedLink.addClass("active");

    // If this is a submenu item, expand the parent menu
    const $parentItem = $clickedLink.closest(".nav-item:has(.submenu)");
    if ($parentItem.length) {
      $parentItem.removeClass("menu-closed").addClass("menu-open");
      $parentItem.find(".submenu").addClass("show");
      $parentItem.find(".nav-arrow").addClass("rotate-90");
      $parentItem.find(".dropdown-toggle-btn").attr("aria-expanded", "true");
    }
  }
});

/**
 * Initialize scripts for newly loaded pages
 */
function initializePageScripts() {
  // Re-initialize tooltips
  $('[data-bs-toggle="tooltip"]').tooltip();

  // Re-initialize any common components
  if (typeof setupAjaxPagination === "function") {
    setupAjaxPagination();
  }

  if (typeof setupRowsPerPage === "function") {
    setupRowsPerPage();
  }

  // Run any custom page initialization scripts
  $(document).trigger("ajaxPageLoaded");
}

function loadPageContent(
  url,
  containerSelector,
  linkText,
  pushState = false,
  callback
) {
  // Show loading indicator
  window.AjaxPagination.showLoadingIndicator();
  $.ajax({
    url: url,
    type: "GET",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    success: function (response) {
      // console.log("load page với url này: ", url);
      // console.log("nội dung trả về từ hàm loadPageContent: ", response);
      // debugger;
      // Kiểm tra nếu server trả về JSON với redirect instruction
      if (typeof response === "object" && response.action === "redirect") {
        window.location.href = response.redirect_url;
        return;
      }
      // Extract the main content
      let $response = $($.parseHTML(response));
      let $newContent = $response
        .filter(containerSelector)
        .add($response.find(containerSelector));

      if ($newContent.length === 0) {
        // Fallback if content selector not found
        window.location.href = url;
        return;
      }

      // Extract page title
      // const newTitle =
      //   $response.find("title").first().text() ||
      //   linkText + " | " + defaultTitle;
      window.AjaxPagination.hideLoadingIndicator();
      // Update page content
      $(containerSelector).html($newContent);
      // Update page title
      // document.title = newTitle;

      // Update browser history
      if (pushState && window.history && window.history.pushState) {
        // console.log("thực hiện push state url mới vào historyAPI");
        window.history.pushState({ url: url }, "", url);
      }

      // Initialize any scripts that need to run on the new page
      initializePageScripts();

      // Hide loading indicator

      // Scroll to top of page
      window.scrollTo(0, 0);

      //gọi hàm callback load lại import count
      if (typeof callback === "function") {
        callback();
      }
    },
    error: function (xhr, status, error) {
      console.error("Error loading page:", error);

      // Handle 404 errors với redirect tự động
      if (xhr.status === 404) {
        console.warn("AJAX 404 Error - Redirecting to dashboard:", url);

        // Hide loading indicator trước khi redirect
        window.AjaxPagination.hideLoadingIndicator();

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
      // Handle authentication errors với redirect
      else if (xhr.status === 401) {
        console.warn("AJAX 401 Error - Redirecting to login");
        window.AjaxPagination.hideLoadingIndicator();
        window.location.href = "/login";
        return;
      }
      // Handle permission errors với redirect về dashboard
      else if (xhr.status === 403) {
        console.warn("AJAX 403 Error - Redirecting to dashboard");
        window.AjaxPagination.hideLoadingIndicator();
        const dashboardUrl = window.location.pathname.includes("/accountant")
          ? "/accountant"
          : window.location.pathname.includes("/admin")
          ? "/admin/dashboard"
          : "/dashboard";
        window.location.href = dashboardUrl;
        return;
      }
      // Handle server errors
      else if (xhr.status >= 500) {
        $(contentSelector).html(
          '<div class="alert alert-danger my-5 text-center">' +
            '<i class="fas fa-server fa-3x mb-3 text-danger"></i>' +
            "<h3>Lỗi máy chủ</h3>" +
            "<p>Có lỗi xảy ra trên máy chủ. Vui lòng thử lại sau.</p>" +
            '<button onclick="reloadPageContent()" class="btn btn-primary me-2">' +
            '<i class="fas fa-redo"></i> Thử lại</button>' +
            '<button onclick="history.back()" class="btn btn-secondary">' +
            '<i class="fas fa-arrow-left"></i> Quay lại</button>' +
            "</div>"
        );
      }
      // Handle other errors
      else {
        $(contentSelector).html(
          '<div class="alert alert-danger my-5 text-center">' +
            '<i class="fas fa-exclamation-circle fa-3x mb-3 text-danger"></i>' +
            "<h3>Lỗi tải trang</h3>" +
            "<p>Có lỗi xảy ra khi tải nội dung. Vui lòng thử lại.</p>" +
            '<button onclick="reloadPageContent()" class="btn btn-primary me-2">' +
            '<i class="fas fa-redo"></i> Thử lại</button>' +
            '<button onclick="history.back()" class="btn btn-secondary">' +
            '<i class="fas fa-arrow-left"></i> Quay lại</button>' +
            "</div>"
        );
      }

      window.AjaxPagination.hideLoadingIndicator();
    },
  });
}

// Function to reload bank accounts list
function loadListTab(config) {
  // Lấy thông tin pagination hiện tại, với fallback values
  let currentPage = 1;
  let perPage = 10;

  // Thử lấy từ pagination data element
  const paginationData = $(config.paginationData);
  if (paginationData.length > 0) {
    currentPage = paginationData.data("current-page") || 1;
  }

  // Thử lấy từ per-page selector
  const perPageSelect = $("#" + config.perPageSelect);
  if (perPageSelect.length > 0 && perPageSelect.val()) {
    perPage = perPageSelect.val();
  }

  // Use existing AJAX function if available, otherwise reload page
  if (typeof window.loadListTabPage === "function") {
    window.loadListTabPage(currentPage, perPage, config);
  } else {
    // Fallback: reload the page with correct tab
    const url = new URL(window.location);
    url.searchParams.set("active_tab", config.tabParam);
    window.location.href = url.toString();
  }
}

function loadListTabPage(page, perPage, config) {
  const listTabContainer = document.querySelector(config.containerSelector);

  // Show loading indicator
  if (listTabContainer) {
    listTabContainer.innerHTML = `<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i>${__(
      "common.loading"
    )}</div>`;
  }

  const baseUrl = listTabContainer?.dataset.baseUrl;

  const params = new URLSearchParams({
    active_tab: config.tabParam,
    [config.page]: String(page), //"bank-accounts-page"
    [config.perPage]: String(perPage), //"bank-accounts_per_page"
  });
  const requestUrl = `${baseUrl}?${params.toString()}`;
  // Make AJAX request with ONLY bank accounts parameters
  fetch(requestUrl, {
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
  })
    .then((response) => {
      //console.log('Response status:', response.status);
      return response.text();
    })
    .then((html) => {
      //console.log('Response received, parsing HTML...');
      // Parse HTML response
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, "text/html");

      // Update the bank accounts list container
      const newListTabContainer = doc.querySelector(config.containerSelector);
      //console.log('New bank accounts list found:', !!newBankAccountsList);

      if (newListTabContainer && listTabContainer) {
        listTabContainer.innerHTML = newListTabContainer.innerHTML;
        //console.log('Bank accounts list updated successfully');
      }

      // Reinitialize event listeners on the new pagination links
      initListTabAjaxPagination(config);

      // Re-attach event listener to the new per page select
      const newPerPageSelect = document.getElementById(config.perPageSelect);
      //PHẦN NÀY SẼ THỰC HIỆN SAU
      if (newPerPageSelect) {
        // Remove existing listeners to avoid duplicates
        newPerPageSelect.removeEventListener(
          "change",
          handleListTabPerPageChange
        );
        newPerPageSelect.addEventListener("change", handleListTabPerPageChange);
      }

      // Update URL without page reload
      const url = new URL(window.location);
      const cleanUrl = url.pathname;
      history.replaceState({}, "", `${cleanUrl}${config.paramPerPage}`);
    })
    .catch((error) => {
      //console.error('Error loading bank accounts page:', error);
      if (bankAccountsContainer) {
        bankAccountsContainer.innerHTML = `<div class="alert alert-danger">${__(
          "common.no_data"
        )}</div>`;
      }
    });
}

//init ajax pagination list tab
function initListTabAjaxPagination(config) {
  //Add click handler to all pagination links in the bank accounts tab
  //console.log('Initializing bank accounts AJAX pagination...');
  const paginationLinks = document.querySelectorAll(config.paginationLinks);

  paginationLinks.forEach(function (link) {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const href = this.getAttribute("href");
      //console.log('Bank accounts pagination link clicked:', href);
      const url = new URL(href, window.location.origin);
      const page =
        url.searchParams.get(config.page) || url.searchParams.get("page") || 1;
      const perPage =
        document.getElementById(config.perPageSelect)?.value || 10;
      //console.log('Loading bank accounts page:', page, 'per page:', perPage);
      loadListTabPage(page, perPage);
    });
  });
}

//change row perpage
function handleListTabPerPageChange() {
  //console.log('Bank accounts per page changed to:', this.value);
  loadListTabPage(1, this.value);
}

/**
 * Show loading indicator
 */
window.AjaxPagination = {
  //loading indicator page
  showLoadingIndicator: function () {
    if (!$("#ajax-loader").length) {
      $("<div>", {
        id: "ajax-loader",
        html: `<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> ${__(
          "common.loading"
        )}</div>`,
        css: {
          position: "fixed",
          top: "50%",
          left: "55%",
          transform: "translate(-50%, -50%)",
          zIndex: 9999,
          background: "rgba(255,255,255,0.8)",
          padding: "20px",
          borderRadius: "5px",
          boxShadow: "0 0 10px rgba(0,0,0,0.1)",
        },
      }).appendTo("body");
    } else {
      $("#ajax-loader").show();
    }
  },
  hideLoadingIndicator: function () {
    $("#ajax-loader").fadeOut(300);
  },

  //loading indicator tab
  showLoadingIndicatorTab: function (containerSelector = "body") {
    const $container = $(containerSelector);
    if (!$container.find("#ajax-loader-tab").length) {
      $("<div>", {
        id: "ajax-loader-tab",
        html: `<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> ${__(
          "common.loading"
        )}</div>`,
        css: {
          position: "absolute",
          top: "50%",
          left: "50%",
          transform: "translate(-50%, -50%)",
          zIndex: 9999,
          background: "rgba(255,255,255,0.9)",
          padding: "20px",
          borderRadius: "5px",
          boxShadow: "0 0 10px rgba(0,0,0,0.1)",
        },
      }).appendTo($container);
    } else {
      $container.find("#ajax-loader-tab").show();
    }
  },

  hideLoadingIndicatorTab: function () {
    $("#ajax-loader-tab").fadeOut(300);
  },
};

/**
 * Global AJAX Navigation Functions
 * These can be called from any page
 */

/**
 * Navigate to URL using AJAX
 * @param {string} url - URL to navigate to
 */
function navigateAjax(url) {
  $("<a>")
    .attr("href", url)
    .addClass("ajax-link")
    .appendTo("body")
    .trigger("click")
    .remove();
}

/**
 * Reload current page content via AJAX
 */
function reloadPageContent() {
  navigateAjax(window.location.href);
}

//global configuration for different table containers
window.tablePaginationConfig = {
  //default config
  default: {
    containerSelector: ".table-container",
    paginationSelector: ".pagination a",
    rowsPerPageSelector: "#rowsPerPage",
  },

  //specific configs for different modules
  "import-list": {
    containerSelector: ".import-list-container",
    paginationSelector: ".pagination a",
    rowsPerPageSelector: "#rowsPerPage",
  },
  "history-imports": {
    containerSelector: ".history-imports-list-container",
    paginationSelector: ".pagination a",
    rowsPerPageSelector: "#rowsPerPage",
  },
  "company-list": {
    containerSelector: ".organization-list-container",
    paginationSelector: ".pagination a",
    rowsPerPageSelector: "#rowsPerPage",
  },
  "form-list": {
    containerSelector: ".forms-list-container",
    paginationSelector: ".pagination a",
    rowsPerPageSelector: "#rowsPerPage",
  },
  "user-notes-list": {
    containerSelector: ".user-notes-list-container",
    paginationSelector: ".pagination a",
    rowsPerPageSelector: "#userNotesRowsPerPage",
  }
};

window.setupTablePagination = function (configKey = "default") {
  const config =
    window.tablePaginationConfig[configKey] ||
    window.tablePaginationConfig.default;

  //setup pagination links
  setupTablePaginationLinks(config);

  //setup rows per page
  setupTableRowsPerPage(config);
};

//set up pagination links for a specific table
// function setupTablePaginationLinks(config) {
//   const paginationLinks = document.querySelectorAll(
//     `${config.containerSelector} ${config.paginationSelector}`
//   );

//   //remove existing event listeners
//   paginationLinks.forEach((link) => {
//     const newLink = link.cloneNode(true);
//     link.parentNode.replaceChild(newLink, link);
//   });

//   //add new event listeners
//   document
//     .querySelectorAll(
//       `${config.containerSelector} ${config.paginationSelector}`
//     )
//     .forEach((link) => {
//       link.addEventListener("click", function (e) {
//         e.preventDefault();
//         // console.log('pagination link clicked');
//         const url = this.getAttribute("href");
//         loadTablePageWithAjax(url, config);
//       });
//     });
// }

//chuyển hàm setupTablePaginationLinks sang dùng event delegation
function setupTablePaginationLinks(config) {
  //gỡ listener cũ để tránh bị trùng
  document.removeEventListener("click", handlePaginationClick);
  document.addEventListener("click", handlePaginationClick);
}

function handlePaginationClick(e) {
  //kiểm tra nếu phần tử click là pagination link
  const target = e.target.closest(".pagination a");
  if (!target) return;

  e.preventDefault();

  //tìm config phù hợp dựa vào container mà link nằm trong
  const configKey = Object.keys(window.tablePaginationConfig).find((key) => {
    const config = window.tablePaginationConfig[key];
    const container = target.closest(config.containerSelector);
    return !!container; //nếu target nằm trong container đó
  });

  if (!configKey) return;

  const config = window.tablePaginationConfig[configKey];
  const url = target.getAttribute("href");

  //gọi ajax load lại bảng tương ứng
  loadTablePageWithAjax(url, config);
}

//set up rows per page for a specific table
function setupTableRowsPerPage(config) {
  // Sử dụng event delegation thay vì gắn trực tiếp
  document.removeEventListener("change", handleTableRowsPerPageChange);
  document.addEventListener("change", handleTableRowsPerPageChange);
}

function handleTableRowsPerPageChange(e) {
  // Kiểm tra nếu change event từ rowsPerPage selector
  if (e.target && e.target.id === "rowsPerPage") {
    // Tìm config phù hợp
    const configKey = Object.keys(window.tablePaginationConfig).find((key) => {
      const config = window.tablePaginationConfig[key];
      const container = e.target.closest(config.containerSelector);
      return !!container; //nếu e.target nằm trong container đó
    });

    if (configKey) {
      const config = window.tablePaginationConfig[configKey];
      changeTableRowsPerPage(e.target.value, config);
    }
  }
}

//load table page with ajax
function loadTablePageWithAjax(url, config, type = null) {
  //reuse existing loading functions
  window.AjaxPagination.showLoadingIndicator();

  $.ajax({
    url: url,
    type: "GET",
    headers: {
      "X-Requested-With": "XMLHttpRequest",
    },
    success: function (response) {
      //update the specific container
      $(config.containerSelector).html(response);
      //update browser URL
      if (window.history && window.history.pushState) {
        if (type === "filter") {
          window.history.replaceState({}, "", url);
        } else {
          window.history.pushState({ path: url }, "", url);
        }
      }

      //re-initialize pagination for this specific table
      setupTablePaginationLinks(config);
      setupTableRowsPerPage(config);

      //hide loading indicator
      // console.log("hide indicator");
      window.AjaxPagination.hideLoadingIndicator();
      // window.scrollTo(0, 0);

      //trigger custom event
      $(document).trigger("tablePaginationLoaded", [config]);

      //thêm hàm load lại số lượng records
      if (config.containerSelector === ".history-imports-list-container") {
        if (typeof window.updateImportHistoryCountInTitle === "function") {
          window.updateImportHistoryCountInTitle();
        }
      }
    },
    error: function (xhr, status, error) {
      window.AjaxPagination.hideLoadingIndicator();
    },
  });
}

//change rows per page for table
function changeTableRowsPerPage(perPage, config) {
  const url = new URL(window.location);
  console.log("change rows per page trigger with url: ", url);
  url.searchParams.set("perPage", perPage);
  url.searchParams.set("page", 1);
  loadTablePageWithAjax(url.toString(), config);
}

//helper function để thay đổi rows per page với url parameters
window.changeRowsPerPageWithParams = function (perPage, configKey = "default") {
  const currentUrl = new URL(window.location.href);
  currentUrl.searchParams.set("perPage", perPage);
  currentUrl.searchParams.set("page", 1);

  const config =
    window.tablePaginationConfig[configKey] ||
    window.tablePaginationConfig.default;
  loadTablePageWithAjax(currentUrl.toString(), config);
};

//backward compatibility - override existing functions
window.setupAjaxPagination = function () {
  //try to detect which table we're working with based on DOM
  if (document.querySelector(".import-list-container")) {
    setupTablePagination("import-list");
  } else if (document.querySelector(".history-imports-list-container")) {
    setupTablePagination("history-imports");
  } else if (document.querySelector(".company-list-container")) {
    setupTablePagination("company-list");
  } else if (document.querySelector(".organization-list-container")) {
    setupTablePagination("organization-list");
  } else if (document.querySelector(".forms-list-container")) {
    setupTablePagination("form-list");
  } else {
    setupTablePagination("default");
  }
};
