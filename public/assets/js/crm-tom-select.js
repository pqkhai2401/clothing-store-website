(function (window) {
	"use strict";

	if (window.initCrmTomSelect) {
		return;
	}

	function resolveElement(elementOrSelector) {
		if (!elementOrSelector) return null;
		if (typeof elementOrSelector === "string") {
			return document.querySelector(elementOrSelector);
		}
		return elementOrSelector;
	}

	function withSearchPlaceholder(instance, placeholderText, shouldFocus) {
		if (!instance || !instance.dropdown) return;
		const searchInput =
			instance.dropdown.querySelector(".dropdown-input") ||
			instance.dropdown.querySelector('input[type="text"]');

		if (!searchInput) return;
		searchInput.setAttribute("placeholder", placeholderText || "Tim kiem...");
		if (shouldFocus) {
			searchInput.focus();
		}
	}

	function isInsideModal(element) {
		var parent = element.parentElement;
		while (parent) {
			if (parent.classList && parent.classList.contains("modal")) {
				return true;
			}
			parent = parent.parentElement;
		}
		return false;
	}

	function normalizePlugins(isMultiple, plugins) {
		if (Array.isArray(plugins) && plugins.length) {
			return plugins;
		}

		var defaults = ["dropdown_input"];
		if (isMultiple) {
			defaults.push("remove_button");
		}
		return defaults;
	}

	function initCrmTomSelect(elementOrSelector, options) {
		if (!window.TomSelect) return null;

		var el = resolveElement(elementOrSelector);
		if (!el) return null;

		var opts = options || {};
		var isMultiple = typeof opts.isMultiple === "boolean" ? opts.isMultiple : !!el.multiple;
		var placeholder = opts.placeholder || el.getAttribute("placeholder") || "";
		var dropdownParent = opts.dropdownParent || null;
		var searchPlaceholder = opts.searchPlaceholder || "Tim kiem...";
		var inModal = isInsideModal(el);

		if (el.tomselect) {
			el.tomselect.destroy();
		}

		var config = {
			plugins: normalizePlugins(isMultiple, opts.plugins),
			maxItems: isMultiple ? null : 1,
			closeAfterSelect: !isMultiple,
			hideSelected: !!isMultiple,
			allowEmptyOption: true,
			create: false,
			placeholder: placeholder,
			wrapperClass: "ts-wrapper crm-ts-wrapper",
			controlClass: "ts-control crm-ts-control",
			dropdownClass: "ts-dropdown crm-ts-dropdown",
			render: {
				no_results: function () {
					return '<div class="no-results">Không có kết quả trùng khớp.</div>';
				},
				loading: function () {
					return '<div class="spinner"></div>';
				},
			},
		};

		// Only use dropdownParent: 'body' for elements inside modal
		if (inModal) {
			config.dropdownParent = "body";
		}

		if (dropdownParent) {
			config.dropdownParent = dropdownParent;
		}

		if (opts.tomSelectOptions && typeof opts.tomSelectOptions === "object") {
			config = Object.assign(config, opts.tomSelectOptions);
		}

		var instance = new TomSelect(el, config);

		instance.on("dropdown_open", function () {
			withSearchPlaceholder(instance, searchPlaceholder, opts.focusSearchOnOpen !== false);

			// Only apply positioning fix for elements inside modal
			if (!inModal) return;

			function updatePosition() {
				var control = instance.control;
				var dropdown = instance.dropdown;
				if (control && dropdown && dropdown.classList.contains("positioned")) {
					var rect = control.getBoundingClientRect();
					dropdown.style.top = rect.bottom + 4 + "px";
					dropdown.style.left = rect.left + "px";
					dropdown.style.width = rect.width + "px";
				}
			}

			// Initial position
			setTimeout(function () {
				var control = instance.control;
				var dropdown = instance.dropdown;
				if (control && dropdown) {
					var rect = control.getBoundingClientRect();
					dropdown.style.top = rect.bottom + 4 + "px";
					dropdown.style.left = rect.left + "px";
					dropdown.style.width = rect.width + "px";
					dropdown.classList.add("positioned");
				}
			}, 0);

			// Update on resize and scroll
			window.addEventListener("resize", updatePosition);
			window.addEventListener("scroll", updatePosition, true);

			// Clean up on close
			instance.once("dropdown_close", function () {
				window.removeEventListener("resize", updatePosition);
				window.removeEventListener("scroll", updatePosition, true);
			});
		});

		instance.on("dropdown_close", function () {
			var dropdown = instance.dropdown;
			if (dropdown) {
				dropdown.classList.remove("positioned");
			}
		});

		withSearchPlaceholder(instance, searchPlaceholder, false);

		return instance;
	}

	function initCrmTomSelectList(selector, options) {
		var nodes = document.querySelectorAll(selector || "select");
		var instances = [];

		nodes.forEach(function (node) {
			var instance = initCrmTomSelect(node, options || {});
			if (instance) {
				instances.push(instance);
			}
		});

		return instances;
	}

	window.initCrmTomSelect = initCrmTomSelect;
	window.initCrmTomSelectList = initCrmTomSelectList;
})(window);
