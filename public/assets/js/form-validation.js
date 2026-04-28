const FormValidation = {
  init() {
    this.initValidation();
    this.initCustomMethods();
  },

  initCustomMethods() {
    // Validate price/amount
    $.validator.addMethod(
      "validAmount",
      function (value, element) {
        if (this.optional(element)) return true;
        const cleanValue = value.replace(/[^\d]/g, "");
        const amount = parseInt(cleanValue);
        return (
          !isNaN(amount) &&
          amount >= 0 &&
          amount <= 999999999999999 &&
          Number.isInteger(amount)
        );
      },
      translations.invalidPrice
    );

    // Validate tax percent
    $.validator.addMethod(
      "taxPercent",
      function (value, element) {
        const percent = parseFloat(value);
        return (
          this.optional(element) ||
          (!isNaN(percent) && percent >= 0 && percent <= 100)
        );
      },
      translations.invalidTaxPercent
    );

    // Validate quantity
    $.validator.addMethod(
      "validQuantity",
      function (value, element) {
        const qty = parseInt(value);
        return this.optional(element) || (!isNaN(qty) && qty > 0);
      },
      translations.invalidQuantity
    );
  },

  getBaseRules() {
    return {
      title: {
        required: true,
        minlength: 3,
        maxlength: 255,
      },
      categoryId: {
        required: true,
      },
      createdBy: {
        required: true,
      },
      description: {
        maxlength: 1000,
      },
      "attachments[]": {
        extension: "jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx",
        filesize: 10485760, // 10MB
      },
    };
  },

  getPaymentRules() {
    return {
      bankNumber: {
        required: true,
        minlength: 8,
        maxlength: 20,
        digits: true,
      },
      accountHolder: {
        required: true,
        minlength: 3,
        maxlength: 255,
      },
      bankName: {
        required: true,
      },
      currency: {
        required: true,
      },
      swiftCode: {
        required: function (element) {
          return $("#currency").val() !== "VND";
        },
      },
      price: {
        required: true,
        validAmount: true,
      },
      priceVND: {
        required: function (element) {
          return $("#currency").val() !== "VND";
        },
        validAmount: true,
      },
    };
  },

  getInvoiceRules() {
    return {
      buyerName: {
        required: true,
        minlength: 3,
        maxlength: 255,
      },
      taxCode: {
        required: true,
      },
      currency: {
        required: true,
      },
      quantity: {
        required: true,
        validQuantity: true,
      },
      price: {
        required: true,
        validAmount: true,
      },
      taxPercent: {
        required: true,
        taxPercent: true,
      },
      priceVND: {
        required: true,
        validAmount: true,
      },
    };
  },

  getTaxRules() {
    return {
      organizationId: {
        required: true,
      },
      // taxTypeId: {
      //   required: true,
      // },
      taxPeriod: {
        required: true,
      },
      // currency: {
      //   required: true,
      // },
      price: {
        required: true,
        validAmount: true,
      },
    };
  },

  initValidation() {
    const $form = $("#formCreate");
    if (!$form.length) return;

    const validator = $form.validate({
      // Ignore hidden fields
      ignore:
        ":hidden:not([class~=selectized]),:hidden > .selectized, .selectize-control .selectize-input input",

      rules: this.getBaseRules(),
      messages: this.getBaseMessages(),

      errorElement: "div",
      errorClass: "invalid-feedback",
      validClass: "is-valid",

      errorPlacement: function (error, element) {
        error.addClass("invalid-feedback d-block");

        if (element.hasClass("selectize-input")) {
          error.insertAfter(element.closest(".selectize-control"));
        } else if (element.is(":checkbox") || element.is(":radio")) {
          error.insertAfter(element.closest(".form-check"));
        } else if (element.hasClass("form-select")) {
          error.insertAfter(element.closest(".form-group"));
        } else if (element.parent(".input-group").length) {
          error.insertAfter(element.parent());
        } else if (element.prop("type") === "file") {
          error.insertAfter(element.parent());
        } else {
          error.insertAfter(element);
        }
      },

      // Validate on various events
      onkeyup: function (element) {
        // Validate on keyup for better UX
        if (
          $(element).hasClass("is-invalid") ||
          $(element).hasClass("is-valid")
        ) {
          $(element).valid();
        }
      },

      onfocusout: function (element) {
        // Validate when user leaves the field
        $(element).valid();
      },

      onclick: function (element) {
        // Validate checkboxes and radio buttons on click
        if (element.type === "checkbox" || element.type === "radio") {
          $(element).valid();
        }
      },

      highlight: function (element) {
        const $element = $(element);
        $element.addClass("is-invalid").removeClass("is-valid");

        // Handle input groups
        if ($element.parent(".input-group").length) {
          $element.parent().addClass("is-invalid").removeClass("is-valid");
        }

        // Handle select2/selectize
        if ($element.hasClass("selectized")) {
          $element
            .siblings(".selectize-control")
            .find(".selectize-input")
            .addClass("is-invalid")
            .removeClass("is-valid");
        }
      },

      unhighlight: function (element) {
        const $element = $(element);
        $element.addClass("is-valid").removeClass("is-invalid");

        // Handle input groups
        if ($element.parent(".input-group").length) {
          $element.parent().addClass("is-valid").removeClass("is-invalid");
        }

        // Handle select2/selectize
        if ($element.hasClass("selectized")) {
          $element
            .siblings(".selectize-control")
            .find(".selectize-input")
            .addClass("is-valid")
            .removeClass("is-invalid");
        }
      },

      submitHandler: function (form) {
        const $submitBtn = $(form).find('button[type="submit"]');
        const originalText = $submitBtn.html();

        // Disable submit button and show loading
        $submitBtn
          .prop("disabled", true)
          .html(
            '<i class="fas fa-spinner fa-spin me-1"></i>' +
              translations.processing
          );

        // Parse all amount/price fields before submit
        $('input[data-type="currency"]').each(function () {
          const rawValue = $(this).val();
          const parsedValue = FormValidation.parseAmount(rawValue);
          $(this).val(parsedValue);
        });

        try {
          form.submit();
        } catch (error) {
          console.error("Form submission error:", error);
          // Reset button state
          $submitBtn.prop("disabled", false).html(originalText);
          // Show error notification
          showNotification("error", translations.submitError);
        }

        // Add timeout to prevent indefinite loading state
        setTimeout(() => {
          $submitBtn.prop("disabled", false).html(originalText);
        }, 30000);
      },
    });

    // Handle dynamic validation when category changes
    $("#category").on("change", function () {
      const category = $(this).val();
      let additionalRules = {};
      let additionalMessages = {};

      switch (category) {
        case "1": // Payment
          additionalRules = FormValidation.getPaymentRules();
          additionalMessages = FormValidation.getPaymentMessages();
          break;
        case "2": // Invoice
          additionalRules = FormValidation.getInvoiceRules();
          additionalMessages = FormValidation.getInvoiceMessages();
          break;
        case "3": // Tax
          additionalRules = FormValidation.getTaxRules();
          additionalMessages = FormValidation.getTaxMessages();
          break;
      }

      validator.settings.rules = {
        ...FormValidation.getBaseRules(),
        ...additionalRules,
      };
      validator.settings.messages = {
        ...FormValidation.getBaseMessages(),
        ...additionalMessages,
      };
    });

    // Add realtime validation
    $form.on("input change", "input, select, textarea", function () {
      const element = $(this);
      if (
        element.val() &&
        (element.hasClass("is-invalid") || element.hasClass("is-valid"))
      ) {
        element.valid();
      }
    });

    return validator;
  },
};

// Initialize on document ready
$(document).ready(function () {
  FormValidation.init();
});
