// Khi gọi hàm callApi nếu success thì trả về dữ liệu và gọi hàm showToast để hiển thị thông báo (Warning cũng vậy)
// Còn nếu error thì không cần gọi hàm showToast vì nó đã được gọi sẵn trong hàm callApi
const callApi = async (url, method = "GET", data = null) => {

  const metaTag = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = metaTag ? metaTag.getAttribute("content") : null;

  const headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
  };

  if (csrfToken) {
    headers["X-CSRF-TOKEN"] = csrfToken;
  }

  // Cấu hình request
  const options = {
        method: method,
        headers: headers
    };

    if (data && (method === "POST" || method === "PUT" || method === "PATCH")) {
        options.body = JSON.stringify(data);
    }

  try {
    // Gọi API
    const response = await fetch(url, options);

    let result = {};

    const contentType = response.headers.get("content-type");
    if (contentType && contentType.includes("application/json")) {
      result = await response.json();
    }

    // Xử lý lỗi (HTTP status)
    if (!response.ok) {
      const errorMessage =
        result.message || response.statusText || "Có lỗi xảy ra từ máy chủ!";
      showToast(errorMessage, "error"); // Hàm show UI thông báo
      throw new Error(errorMessage);
    }

    return result; // Trả về data nếu thành công
  } catch (error) {
    console.error("API Error:", error);

    // Bắt lỗi mất mạng hoặc CORS
    const errorMsg = error.message.toLowerCase();
    const isNetworkError =
      error.name === "TypeError" ||
      errorMsg.includes("fetch") ||
      errorMsg.includes("network") ||
      errorMsg.includes("load failed");

    if (isNetworkError) {
      showToast(
        "Không thể kết nối đến máy chủ. Vui lòng kiểm tra lại mạng internet!",
        "error",
      );
    }
    throw error;
  }
};

// Hàm tạo thông báo góc phải màn hình
const showToast = (message, type = "error") => {
  // Kiểm tra và tạo Hộp chứa (Container)
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";

    Object.assign(container.style, {
      position: "fixed",
      top: "20px",
      right: "20px",
      zIndex: "9999",
      display: "flex",
      flexDirection: "column",
      gap: "10px",
      pointerEvents: "none",
    });
    document.body.appendChild(container);
  }

  // Cấu hình Màu sắc và Icon (SVG)
  let bgColor, textColor, iconSvg;

  switch (type) {
    case "success":
      bgColor = "#28a745";
      textColor = "white";
      iconSvg = `<svg style="flex-shrink: 0; min-width: 28px; min-height: 28px; display: block;" viewBox="0 0 24 24" width="28" height="28"><circle cx="12" cy="12" r="12" fill="#d4edda"/><path d="M7 13l3 3 7-7" stroke="#28a745" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>`;
      break;

    case "warning":
      bgColor = "#ffc107";
      textColor = "#212529";
      iconSvg = `<svg style="flex-shrink: 0; min-width: 28px; min-height: 28px; display: block;" viewBox="0 0 24 24" width="28" height="28">
                    <circle cx="12" cy="12" r="12" fill="#fff3cd"/>
                    <path d="M12 5.5l-6 10.5h12z" fill="#f5a623" stroke="#f5a623" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 8v3.5m0 2h.01" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                 </svg>`;
      break;

    case "error":
    case "danger":
    default:
      bgColor = "#dc3545";
      textColor = "white";
      iconSvg = `<svg style="flex-shrink: 0; min-width: 28px; min-height: 28px; display: block;" viewBox="0 0 24 24" width="28" height="28"><circle cx="12" cy="12" r="12" fill="#f8d7da"/><path d="M15 9l-6 6m0-6l6 6" stroke="#dc3545" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>`;
      break;
  }

  // Tạo thẻ div cho Toast hiện tại
  const toast = document.createElement("div");
  toast.className = `custom-toast toast-${type}`;

  // Nút X ở góc trên bên phải
  toast.innerHTML = `
    <div style="display: flex; align-items: center; gap: 12px; padding-right: 20px;">
        <div style="display: flex; align-items: center; justify-content: center;">
            ${iconSvg}
        </div>
        <div style="flex: 1; font-family: sans-serif; font-size: 15px; line-height: 1.4;">
            ${message}
        </div>
    </div>
    <span class="toast-close" style="position: absolute; top: 3px; right: 12px; cursor: pointer; font-size: 24px; line-height: 1; opacity: 0.7;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">&times;</span>
  `;

  // CSS cho từng Toast
  Object.assign(toast.style, {
    backgroundColor: bgColor,
    color: textColor,
    padding: "15px 20px",
    borderRadius: "5px",
    boxShadow: "0 4px 6px rgba(0,0,0,0.1)",
    minWidth: "250px",
    maxWidth: "400px",
    wordBreak: "break-word",
    transform: "translateX(100%)",
    opacity: "0",
    transition: "all 0.4s ease-out",
    position: "relative",
    pointerEvents: "auto",
  });

  // Chèn vào container
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.transform = "translateX(0)";
    toast.style.opacity = "1";
  }, 10);

  // Hàm chung để đóng và xóa toast
  const closeToast = () => {
    toast.style.transform = "translateX(100%)";
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 400);
  };

  // Tự động ẩn sau 5 giây
  const autoHideTimeout = setTimeout(closeToast, 5000);

  // Bắt sự kiện khi click vào nút X
  const closeBtn = toast.querySelector(".toast-close");
  closeBtn.addEventListener("click", () => {
    clearTimeout(autoHideTimeout);
    closeToast();
  });
};
