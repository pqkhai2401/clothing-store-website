/**
 * Global translation function for JavaScript.
 * This function mimics Laravel's `__` helper to retrieve translation strings
 * that have been exposed to the frontend via `window.translations`.
 *
 * @param {string} key - The translation key (e.g., 'notification.unread_notifications').
 * @param {object} replacements - Optional key-value pairs for replacements in the translation string.
 * @returns {string} The translated string, or the key if not found.
 */
window.__ = function (key, replacements = {}) {
  let translation = window.translations[key] || key;

  for (let placeholder in replacements) {
    translation = translation.replace(
      `:${placeholder}`,
      replacements[placeholder]
    );
  }

  return translation;
};

/**
 * Handle language change events
 * This function will be called when user switches language
 */
window.handleLanguageChange = function () {
  console.log("Language changed, reloading FormAccount list...");

  // Check if we're on organization detail page with FormAccount tab active
  const formAccountTab = document.getElementById("listformaccounts-tab");
  const formAccountTabPane = document.getElementById("listformaccounts");

  if (
    formAccountTab &&
    formAccountTabPane &&
    formAccountTabPane.classList.contains("active")
  ) {
    console.log("FormAccount tab is active, reloading...");

    // Reload FormAccount list with current language
    if (typeof window.loadFormAccountsPageWithAjax === "function") {
      const currentUrl = new URL(window.location.href);
      currentUrl.searchParams.set("tab", "listformaccounts");

      // Remove page parameter to start from first page
      currentUrl.searchParams.delete("form_accounts_page");

      window.loadFormAccountsPageWithAjax(
        currentUrl.toString(),
        "language_change"
      );
    } else {
      console.log("loadFormAccountsPageWithAjax not available, reloading page");
      window.location.reload();
    }
  }
};

// Initialize language change handling when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  // Add event listeners to language switcher links
  const languageLinks = document.querySelectorAll(
    ".language-menu .dropdown-item"
  );

  languageLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault(); // Prevent default navigation

      const url = this.getAttribute("href");
      console.log("Language change requested:", url);

      // Make AJAX request to change language
      fetch(url, {
        method: "GET",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN":
            document
              .querySelector('meta[name="csrf-token"]')
              ?.getAttribute("content") || "",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            console.log("Language changed successfully to:", data.locale);

            // Update the language display in header
            const languageToggle = document.querySelector(
              ".language-menu .dropdown-toggle"
            );
            if (languageToggle) {
              languageToggle.textContent =
                data.locale === "vi" ? "Tiếng Việt" : "English";
            }

            // Reload FormAccount list if on organization detail page
            window.handleLanguageChange();

            // Optionally reload the entire page to update all translations
            setTimeout(() => {
              window.location.reload();
            }, 500);
          } else {
            console.error("Language change failed");
            // Fallback to normal navigation
            window.location.href = url;
          }
        })
        .catch((error) => {
          console.error("Error changing language:", error);
          // Fallback to normal navigation
          window.location.href = url;
        });
    });
  });
});
