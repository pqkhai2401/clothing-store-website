class SideBarMenuManager {
  constructor() {
    this.menuConfigs = [
      {
        submenuId: "userSubmenu",
        routes: [
          "/user",
          "/user/accountant",
          "/user/submitter",
          "/user/approver",
        ],
        name: "user",
      },
      {
        submenuId: "formSubmenu",
        routes: ["/form", "/form/invoice", "/form/payment", "/form/tax"],
        name: "form",
      },
    ];
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.initializeMenuStates();
    this.setupDropdownToggles();
  }

  setupEventListeners() {
    // Handle browser back/forward navigation
    window.addEventListener("popstate", (event) => {
      // Use longer delay to ensure DOM is updated
      setTimeout(() => {
        this.resetAllMenuStates();
      }, 200);
    });

    // Also listen for page visibility change (alternative approach)
    document.addEventListener("visibilitychange", () => {
      if (!document.hidden) {
        setTimeout(() => this.resetAllMenuStates(), 100);
      }
    });

    // Reset on page load (in case of direct navigation)
    document.addEventListener("DOMContentLoaded", () => {
      this.resetAllMenuStates();
    });

    // Additional fallback - reset on window focus
    window.addEventListener("focus", () => {
      setTimeout(() => this.resetAllMenuStates(), 100);
    });
  }

  initializeMenuStates() {
    this.menuConfigs.forEach((config) => {
      const submenu = document.getElementById(config.submenuId);
      const dropdownToggle = this.getDropdownToggle(config.submenuId);
      const arrow = dropdownToggle?.querySelector(".nav-arrow");
      const parentNavItem = dropdownToggle?.closest(".nav-item");

      if (submenu && dropdownToggle && arrow && parentNavItem) {
        const isActive = submenu.classList.contains("show");
        if (isActive) {
          this.openMenu(submenu, arrow, dropdownToggle, parentNavItem);
        } else {
          this.closeMenu(submenu, arrow, dropdownToggle, parentNavItem);
        }
      }
    });
  }

  setupDropdownToggles() {
    this.menuConfigs.forEach((config) => {
      const dropdownToggle = this.getDropdownToggle(config.submenuId);
      const submenu = document.getElementById(config.submenuId);
      const arrow = dropdownToggle?.querySelector(".nav-arrow");
      const parentNavItem = dropdownToggle?.closest(".nav-item");

      if (dropdownToggle && submenu && arrow) {
        dropdownToggle.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.addRippleEffect(e, e.target);
          this.toggleMenu(submenu, arrow, dropdownToggle, parentNavItem);
        });
      }

      //prevent submenu from closing when clicking on sub-items
      const subMenuItems = document.querySelectorAll(
        `#${config.submenuId} .nav-link`
      );
      subMenuItems.forEach((item) => {
        item.addEventListener("click", (e) => {
          //allow navigation but keep dropdown open
          //the dropdown state will be maintained by server-side logic
        });
      });
    });
  }

  getDropdownToggle(submenuId) {
    const submenu = document.getElementById(submenuId);
    return submenu?.previousElementSibling?.querySelector(
      ".dropdown-toggle-btn"
    );
  }

  openMenu(submenu, arrow, dropdownToggle, parentNavItem) {
    // Remove any inline styles that might interfere
    submenu.style.display = "";
    submenu.style.maxHeight = "";

    // Add show class
    submenu.classList.add("show");
    arrow.classList.add("rotate-90");
    dropdownToggle.setAttribute("aria-expanded", "true");

    if (parentNavItem) {
      parentNavItem.classList.remove("menu-closed");
      parentNavItem.classList.add("menu-open");
    }
  }

  closeMenu(submenu, arrow, dropdownToggle, parentNavItem) {
    // Remove show class
    submenu.classList.remove("show");
    arrow.classList.remove("rotate-90");
    dropdownToggle.setAttribute("aria-expanded", "false");

    if (parentNavItem) {
      parentNavItem.classList.remove("menu-open");
      parentNavItem.classList.add("menu-closed");
    }
  }

  toggleMenu(submenu, arrow, dropdownToggle, parentNavItem) {
    const isCurrentlyOpen = submenu.classList.contains("show");

    if (isCurrentlyOpen) {
      this.closeMenu(submenu, arrow, dropdownToggle, parentNavItem);
    } else {
      this.openMenu(submenu, arrow, dropdownToggle, parentNavItem);
    }
  }

  resetAllMenuStates() {
    this.menuConfigs.forEach((config) => {
      const submenu = document.getElementById(config.submenuId);
      const dropdownToggle = this.getDropdownToggle(config.submenuId);
      const arrow = dropdownToggle?.querySelector(".nav-arrow");
      const parentNavItem = dropdownToggle?.closest(".nav-item");

      if (submenu && dropdownToggle && arrow && parentNavItem) {
        //check if current route is NOT in the menu's routes
        const currentPath = window.location.pathname;
        const isRelevantRoute = config.routes.some((route) =>
          currentPath.includes(route)
        );

        if (!isRelevantRoute) {
          this.closeMenu(submenu, arrow, dropdownToggle, parentNavItem);
        } else {
          this.openMenu(submenu, arrow, dropdownToggle, parentNavItem);
        }
      } else {
        console.log(`Menu ${config.name}: Elements not found`);
      }
    });
  }

  addRippleEffect(e, button) {
    const ripple = document.createElement("span");
    ripple.classList.add("ripple-effect");
    button.appendChild(ripple);

    //position ripple
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + "px";
    ripple.style.left = e.clientX - rect.left - size / 2 + "px";
    ripple.style.top = e.clientY - rect.top - size / 2 + "px";

    //remove ripple after animation
    setTimeout(() => {
      ripple.remove();
    }, 600);
  }

  //method to add custom menu configuration
  addMenuConfig(config) {
    this.menuConfigs.push(config);
    this.setupDropdownToggles();
  }
}

//initialize the sidebar menu manager when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  window.sidebarMenuManager = new SideBarMenuManager();
});
