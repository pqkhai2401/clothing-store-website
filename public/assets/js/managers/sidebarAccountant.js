class SideBarMenuManager {
  constructor() {
    // Không cần khai báo menuConfigs thủ công nữa
    // JS sẽ tự động quét dựa trên cấu trúc DOM của Blade
    this.init();
  }

  init() {
    this.setupEventListeners();
    // Đợi DOM ổn định rồi mới tính toán trạng thái
    setTimeout(() => this.resetAllMenuStates(true), 50);
    this.setupDropdownToggles();
  }

  setupEventListeners() {
    window.addEventListener("popstate", () => {
      setTimeout(() => this.resetAllMenuStates(), 200);
    });

    document.addEventListener("visibilitychange", () => {
      if (!document.hidden) {
        setTimeout(() => this.resetAllMenuStates(), 100);
      }
    });

    window.addEventListener("focus", () => {
      setTimeout(() => this.resetAllMenuStates(), 100);
    });
  }

  // Tìm tất cả các cụm menu có dropdown trong sidebar
  getAllMenus() {
    const menus = [];
    const submenus = document.querySelectorAll(".nav-treeview.submenu");

    submenus.forEach((submenu) => {
      const parentNavItem = submenu.closest(".nav-item");
      const dropdownToggle = parentNavItem?.querySelector(
        ".dropdown-toggle-btn",
      );
      const arrow = dropdownToggle?.querySelector(".nav-arrow");

      if (submenu && dropdownToggle && arrow) {
        menus.push({ submenu, dropdownToggle, arrow, parentNavItem });
      }
    });
    return menus;
  }

  setupDropdownToggles() {
    const menus = this.getAllMenus();

    menus.forEach(({ submenu, dropdownToggle, arrow, parentNavItem }) => {
      const clickableArea = parentNavItem.querySelector(".parent-menu-item");

      if (clickableArea) {
        clickableArea.addEventListener("click", (e) => {
          const link = e.target.closest("a");
          if (
            link &&
            link.getAttribute("href") !== "javascript:void(0)" &&
            link.getAttribute("href") !== "#"
          ) {
            return;
          }

          e.preventDefault();
          e.stopPropagation();

          this.addRippleEffect(e, dropdownToggle); 
          this.toggleMenu(submenu, arrow, dropdownToggle, parentNavItem);
        });
      }
    });
  }

  openMenu(submenu, arrow, dropdownToggle, parentNavItem, instant = false) {
    if (instant) {
      submenu.style.transition = "none";
      arrow.style.transition = "none";
    }

    submenu.classList.add("show");
    arrow.classList.add("rotate-90");
    dropdownToggle.setAttribute("aria-expanded", "true");

    if (parentNavItem) {
      parentNavItem.classList.add("menu-open");
    }

    if (instant) {
      void submenu.offsetHeight;
      submenu.style.transition = "";
      arrow.style.transition = "";
    }
  }

  closeMenu(submenu, arrow, dropdownToggle, parentNavItem, instant = false) {
    if (instant) {
      submenu.style.transition = "none";
      arrow.style.transition = "none";
    }

    submenu.classList.remove("show");
    arrow.classList.remove("rotate-90");
    dropdownToggle.setAttribute("aria-expanded", "false");

    if (parentNavItem) {
      parentNavItem.classList.remove("menu-open");
    }

    if (instant) {
      void submenu.offsetHeight;
      submenu.style.transition = "";
      arrow.style.transition = "";
    }
  }

  toggleMenu(submenu, arrow, dropdownToggle, parentNavItem) {
    const isCurrentlyOpen = submenu.classList.contains("show");
    if (isCurrentlyOpen) {
      this.closeMenu(submenu, arrow, dropdownToggle, parentNavItem, false);
    } else {
      this.openMenu(submenu, arrow, dropdownToggle, parentNavItem, false);
    }
  }

  /**
   * Đồng bộ trạng thái menu dựa trên class 'active' mà Laravel Blade đã render
   */
  resetAllMenuStates(isInitialLoad = false) {
    const menus = this.getAllMenus();

    menus.forEach(({ submenu, dropdownToggle, arrow, parentNavItem }) => {
      // Kiểm tra xem bên trong submenu này có link nào đang .active không
      // Hoặc bản thân nút cha có .active không
      const hasActiveChild = submenu.querySelector(".nav-link.active") !== null;
      const isParentActive =
        parentNavItem.querySelector(".nav-link.d-flex.active") !== null;

      if (hasActiveChild || isParentActive) {
        this.openMenu(
          submenu,
          arrow,
          dropdownToggle,
          parentNavItem,
          isInitialLoad,
        );
      } else {
        // Chỉ đóng nếu không phải là item đang được kích hoạt
        this.closeMenu(
          submenu,
          arrow,
          dropdownToggle,
          parentNavItem,
          isInitialLoad,
        );
      }
    });
  }

  addRippleEffect(e, button) {
    const ripple = document.createElement("span");
    ripple.classList.add("ripple-effect");
    button.appendChild(ripple);

    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    ripple.style.width = ripple.style.height = size + "px";
    ripple.style.left = e.clientX - rect.left - size / 2 + "px";
    ripple.style.top = e.clientY - rect.top - size / 2 + "px";

    setTimeout(() => {
      ripple.remove();
    }, 600);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  window.sidebarMenuManager = new SideBarMenuManager();
});
