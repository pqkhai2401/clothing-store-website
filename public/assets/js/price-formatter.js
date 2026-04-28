class PriceFormatter {
  /**
   * Khởi tạo xử lý định dạng giá cho các input
   * @param {string} selector - CSS selector cho input fields cần xử lý
   */
  static initFormatting(
    selector = 'input[name="priceVND"]:not(.tax-form-price)'
  ) {
    const priceInputs = document.querySelectorAll(selector);

    priceInputs.forEach(function (input) {
      // Skip formatting for disabled inputs
      if (input.disabled) return;

      // Format initial value if it exists
      if (input.value) {
        const cleanValue = input.value.replace(/[^\d]/g, "");
        if (cleanValue) {
          input.value = parseInt(cleanValue).toLocaleString("vi-VN");
        }
      }

      // Add event listener for input changes
      input.addEventListener("input", function (e) {
        let value = e.target.value.replace(/[^\d]/g, "");
        if (value) {
          // Add thousand separators
          value = parseInt(value).toLocaleString("vi-VN");
          e.target.value = value;
        }
      });

      // Add form submission handler to convert back to raw value
      const form = input.closest("form");
      if (form && !form.classList.contains("price-handler-initialized")) {
        form.classList.add("price-handler-initialized");

        form.addEventListener("submit", function (e) {
          // Skip processing if input is disabled
          if (input.disabled) return true;

          // Convert back to raw value for submission
          const rawValue = input.value.replace(/[^\d]/g, "");

          // Store original value to restore after submission
          const originalValue = input.value;

          // Set raw value for form submission
          input.value = rawValue;

          // Restore formatted value after submission (for non-AJAX forms)
          setTimeout(() => {
            input.value = originalValue;
          }, 100);

          return true;
        });
      }
    });
  }

  /**
   * Chuyển đổi giá trị đã định dạng thành số nguyên
   * @param {string} formattedValue - Giá trị đã định dạng (vd: "1.234.567")
   * @return {number} - Số nguyên (vd: 1234567)
   */
  static toRawValue(formattedValue) {
    if (!formattedValue) return 0;
    return parseInt(formattedValue.replace(/[^\d]/g, "")) || 0;
  }

  /**
   * Định dạng số nguyên thành chuỗi có dấu phân cách
   * @param {number} rawValue - Số nguyên (vd: 1234567)
   * @return {string} - Chuỗi đã định dạng (vd: "1.234.567")
   */
  static toFormattedValue(rawValue) {
    if (!rawValue) return "";
    return rawValue.toLocaleString("vi-VN");
  }
}
