/**
 * Unbelievable Salon Booking - Public JavaScript
 *
 * @package Unbelievable_Salon_Booking
 */

(function() {
	'use strict';

	// Flow configuration (from PHP)
	const defaultSteps = typeof unbsbPublic !== 'undefined' ? [
		{ key: 'service', label: unbsbPublic.strings.step_service },
		{ key: 'staff', label: unbsbPublic.strings.step_staff },
		{ key: 'datetime', label: unbsbPublic.strings.step_datetime },
		{ key: 'info', label: unbsbPublic.strings.step_info }
	] : [
		{ key: 'service', label: 'Service' },
		{ key: 'staff', label: 'Staff' },
		{ key: 'datetime', label: 'Date/Time' },
		{ key: 'info', label: 'Information' }
	];

	const flowConfig = typeof unbsbFlowConfig !== 'undefined' ? unbsbFlowConfig : {
		mode: 'service_first',
		steps: defaultSteps,
		stepNumbers: { service: 1, staff: 2, datetime: 3, info: 4 },
		totalSteps: 4,
		hasServiceStep: true,
		hasStaffStep: true,
		multiService: false
	};

	// Global state
	const state = {
		currentStep: 1,
		totalSteps: flowConfig.totalSteps,
		selectedService: null,
		selectedServices: [], // Array for multi-service
		selectedStaff: null,
		selectedDate: null,
		selectedTime: null,
		currentMonth: new Date(),
		availableSlots: [],
		staffList: [],
		servicesList: [],
		// Promo code state
		promoCode: null,
		promoCodeId: null,
		discountAmount: 0,
		discountType: null
	};

	/**
	 * Format time string based on user's time format setting.
	 * Converts 24h "HH:MM" to 12h "h:MM AM/PM" when format is "g:i A".
	 *
	 * @param {string} time24 Time in "HH:MM" or "HH:MM:SS" format.
	 * @return {string} Formatted time string.
	 */
	function formatTime(time24) {
		if (!time24) return '';
		var timeFormat = typeof unbsbPublic !== 'undefined' ? unbsbPublic.timeFormat : 'H:i';
		var parts = time24.split(':');
		var hours = parseInt(parts[0], 10);
		var minutes = parts[1] || '00';

		if (timeFormat === 'g:i A' || timeFormat === 'g:i a') {
			var ampm = hours >= 12 ? 'PM' : 'AM';
			var h = hours % 12;
			if (0 === h) h = 12;
			return h + ':' + minutes + ' ' + ampm;
		}

		return parts[0] + ':' + minutes;
	}

	// DOM Elements
	let form, nextBtn, prevBtn, submitBtn, formActions;

	// Initialize
	document.addEventListener('DOMContentLoaded', function() {
		const bookingWrapper = document.getElementById('unbsb-booking-form');
		if (!bookingWrapper) return;

		form = document.getElementById('unbsb-booking-wizard');
		nextBtn = document.getElementById('unbsb-next-btn');
		prevBtn = document.getElementById('unbsb-prev-btn');
		submitBtn = document.getElementById('unbsb-submit-btn');
		formActions = document.getElementById('unbsb-form-actions');

		// Initialize based on flow mode
		initFlowMode();
		initNavigation();
		initCalendar();
		initPromoCode();
		initFormSubmit();
	});

	/**
	 * Initialize based on flow mode
	 */
	function initFlowMode() {
		const mode = flowConfig.mode;

		switch (mode) {
			case 'service_first':
				initServiceSelection();
				initCategoryFilter();
				break;
			case 'staff_first':
				loadAllStaff();
				break;
			case 'service_only':
				initServiceSelection();
				initCategoryFilter();
				break;
			case 'staff_only':
				loadAllStaff();
				break;
			default:
				initServiceSelection();
				initCategoryFilter();
		}
	}

	/**
	 * Category Filter
	 */
	function initCategoryFilter() {
		var filterContainer = document.getElementById('unbsb-category-filter');
		if (!filterContainer) return;

		var wrapper = document.getElementById('unbsb-filter-wrapper');
		var arrowLeft = document.getElementById('unbsb-filter-arrow-left');
		var arrowRight = document.getElementById('unbsb-filter-arrow-right');
		var dotsContainer = document.getElementById('unbsb-filter-dots');
		var filterBtns = filterContainer.querySelectorAll('.unbsb-filter-btn');
		var serviceItems = document.querySelectorAll('.unbsb-service-item');
		var categoryGroups = document.querySelectorAll('.unbsb-service-category-group');
		var scrollAmount = 150;
		var hasSwiped = false;

		// --- Overflow detection & scroll position tracking ---
		function updateScrollState() {
			if (!wrapper) return;
			var isOverflowing = filterContainer.scrollWidth > filterContainer.clientWidth + 1;
			var scrollLeft = filterContainer.scrollLeft;
			var maxScroll = filterContainer.scrollWidth - filterContainer.clientWidth;
			var atStart = scrollLeft <= 2;
			var atEnd = scrollLeft >= maxScroll - 2;

			wrapper.classList.toggle('unbsb-overflowing', isOverflowing);
			wrapper.classList.toggle('unbsb-scroll-start', atStart);
			wrapper.classList.toggle('unbsb-scroll-end', atEnd);

			updateDots();
		}

		updateScrollState();
		window.addEventListener('resize', updateScrollState);
		filterContainer.addEventListener('scroll', updateScrollState, { passive: true });

		// --- Arrow buttons ---
		if (arrowLeft) {
			arrowLeft.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				filterContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
			});
		}
		if (arrowRight) {
			arrowRight.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				filterContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
			});
		}

		// --- Nudge animation (once, on load, if overflowing) ---
		setTimeout(function() {
			if (filterContainer.scrollWidth > filterContainer.clientWidth + 1) {
				filterContainer.classList.add('unbsb-nudge');
				filterContainer.addEventListener('animationend', function() {
					filterContainer.classList.remove('unbsb-nudge');
				}, { once: true });
			}
		}, 600);

		// --- Mobile swipe hint — hide after first user scroll ---
		filterContainer.addEventListener('scroll', function() {
			if (!hasSwiped && wrapper) {
				hasSwiped = true;
				wrapper.classList.add('unbsb-swiped');
			}
		}, { passive: true, once: true });

		// --- Scroll indicator dots (mobile) ---
		function buildDots() {
			if (!dotsContainer) return;
			dotsContainer.innerHTML = '';
			if (filterContainer.scrollWidth <= filterContainer.clientWidth + 1) return;

			var visibleWidth = filterContainer.clientWidth;
			var totalWidth = filterContainer.scrollWidth;
			var dotCount = Math.max(2, Math.round(totalWidth / visibleWidth));

			for (var i = 0; i < dotCount; i++) {
				var dot = document.createElement('span');
				dot.className = 'unbsb-dot';
				if (0 === i) dot.classList.add('active');
				dotsContainer.appendChild(dot);
			}
		}

		function updateDots() {
			if (!dotsContainer) return;
			var dots = dotsContainer.querySelectorAll('.unbsb-dot');
			if (0 === dots.length) return;

			var maxScroll = filterContainer.scrollWidth - filterContainer.clientWidth;
			if (maxScroll <= 0) return;
			var progress = filterContainer.scrollLeft / maxScroll;
			var activeIndex = Math.round(progress * (dots.length - 1));

			dots.forEach(function(dot, i) {
				dot.classList.toggle('active', i === activeIndex);
			});
		}

		buildDots();
		window.addEventListener('resize', buildDots);

		// --- Filter button click logic ---
		filterBtns.forEach(function(btn) {
			btn.addEventListener('click', function() {
				var categoryId = this.dataset.category;

				// Update active state
				filterBtns.forEach(function(b) {
					b.classList.remove('active');
				});
				this.classList.add('active');

				// If we have category groups (multi-service mode), filter groups
				if (categoryGroups.length > 0) {
					categoryGroups.forEach(function(group) {
						var groupCatId = group.dataset.categoryId;
						if (categoryId === 'all') {
							group.style.display = '';
							group.classList.remove('unbsb-filtered-out');
						} else if (groupCatId === categoryId) {
							group.style.display = '';
							group.classList.remove('unbsb-filtered-out');
						} else {
							group.style.display = 'none';
							group.classList.add('unbsb-filtered-out');
						}
					});
				} else {
					// Flat list (single-service mode)
					serviceItems.forEach(function(item) {
						var itemCategoryId = item.dataset.categoryId;
						if (categoryId === 'all') {
							item.style.display = '';
							item.classList.remove('unbsb-filtered-out');
						} else if (itemCategoryId === categoryId) {
							item.style.display = '';
							item.classList.remove('unbsb-filtered-out');
						} else {
							item.style.display = 'none';
							item.classList.add('unbsb-filtered-out');
						}
					});
				}
			});
		});

		// --- Category header click to toggle collapse ---
		categoryGroups.forEach(function(group) {
			var header = group.querySelector('.unbsb-service-category-header');
			if (header) {
				header.addEventListener('click', function() {
					group.classList.toggle('unbsb-collapsed');
				});
			}
		});
	}

	/**
	 * Get current step key
	 */
	function getCurrentStepKey() {
		const step = flowConfig.steps.find(function(s, index) {
			return index + 1 === state.currentStep;
		});
		return step ? step.key : null;
	}

	/**
	 * Get step number by key
	 */
	function getStepNumber(key) {
		return flowConfig.stepNumbers[key] || 0;
	}

	/**
	 * Service Selection
	 */
	function initServiceSelection() {
		if (flowConfig.multiService) {
			initMultiServiceSelection();
		} else {
			initSingleServiceSelection();
		}
	}

	/**
	 * Single Service Selection (Radio)
	 */
	function initSingleServiceSelection() {
		const serviceItems = document.querySelectorAll('.unbsb-service-item input[type="radio"]');

		serviceItems.forEach(function(input) {
			input.addEventListener('change', function() {
				const serviceId = this.value;
				state.selectedService = getServiceById(serviceId);

				// Reset date and time
				state.selectedDate = null;
				state.selectedTime = null;

				// In service_first mode, reset staff too
				if (flowConfig.mode === 'service_first' || flowConfig.mode === 'service_only') {
					state.selectedStaff = null;
				}

				updateNextButton();
			});
		});
	}

	/**
	 * Multi Service Selection (Checkbox)
	 */
	function initMultiServiceSelection() {
		const serviceItems = document.querySelectorAll('.unbsb-service-item input[type="checkbox"]');

		serviceItems.forEach(function(input) {
			input.addEventListener('change', function() {
				const serviceId = this.value;
				const service = getServiceById(serviceId);

				if (this.checked) {
					// Add service
					if (!state.selectedServices.find(function(s) { return s.id == serviceId; })) {
						state.selectedServices.push(service);
					}
				} else {
					// Remove service
					state.selectedServices = state.selectedServices.filter(function(s) {
						return s.id != serviceId;
					});
				}

				// Save first selected service as primary (for compatibility)
				state.selectedService = state.selectedServices.length > 0 ? state.selectedServices[0] : null;

				// Reset date and time
				state.selectedDate = null;
				state.selectedTime = null;

				// In service_first mode, reset staff too
				if (flowConfig.mode === 'service_first' || flowConfig.mode === 'service_only') {
					state.selectedStaff = null;
				}

				// Update summary
				updateSelectedServicesSummary();
				updateCategorySelectedCounts();
				updateNextButton();
			});
		});
	}

	/**
	 * Update category group selected count badges
	 */
	function updateCategorySelectedCounts() {
		var groups = document.querySelectorAll('.unbsb-service-category-group');
		groups.forEach(function(group) {
			var checked = group.querySelectorAll('.unbsb-service-item input[type="checkbox"]:checked');
			var badge = group.querySelector('.unbsb-category-selected-count');
			if (!badge) return;
			if (checked.length > 0) {
				badge.textContent = checked.length;
				badge.style.display = '';
			} else {
				badge.style.display = 'none';
			}
		});
	}

	/**
	 * Update selected services summary
	 */
	function updateSelectedServicesSummary() {
		const summaryEl = document.getElementById('unbsb-selected-summary');
		if (!summaryEl) return;

		if (state.selectedServices.length === 0) {
			summaryEl.style.display = 'none';
			return;
		}

		summaryEl.style.display = 'block';

		// Calculate total duration and price
		let totalDuration = 0;
		let totalPrice = 0;

		state.selectedServices.forEach(function(service) {
			totalDuration += parseInt(service.duration) || 0;
			totalPrice += getEffectivePrice(service);
		});

		// Update
		const durationEl = document.getElementById('unbsb-total-duration');
		const priceEl = document.getElementById('unbsb-total-price');

		if (durationEl) durationEl.textContent = totalDuration;
		if (priceEl) priceEl.textContent = formatNumber(totalPrice);
	}

	/**
	 * Format number
	 */
	function formatNumber(num) {
		return new Intl.NumberFormat('tr-TR').format(num);
	}

	/**
	 * Get total duration for selected services
	 */
	function getTotalDuration() {
		if (flowConfig.multiService && state.selectedServices.length > 0) {
			return state.selectedServices.reduce(function(total, service) {
				return total + (parseInt(service.duration) || 0);
			}, 0);
		}
		return state.selectedService ? (parseInt(state.selectedService.duration) || 0) : 0;
	}

	/**
	 * Get the effective price for a service (discounted if available)
	 */
	function getEffectivePrice(service) {
		if (service.discounted_price && parseFloat(service.discounted_price) > 0 && parseFloat(service.discounted_price) < parseFloat(service.price)) {
			return parseFloat(service.discounted_price);
		}
		return parseFloat(service.price) || 0;
	}

	/**
	 * Check if service has a discount
	 */
	function hasDiscount(service) {
		return service.discounted_price && parseFloat(service.discounted_price) > 0 && parseFloat(service.discounted_price) < parseFloat(service.price);
	}

	/**
	 * Format price with currency
	 */
	function formatPrice(amount) {
		var currency = typeof unbsbPublic !== 'undefined' ? unbsbPublic.currency : { position: 'after', symbol: '€' };
		return currency.position === 'before'
			? currency.symbol + formatNumber(amount)
			: formatNumber(amount) + ' ' + currency.symbol;
	}

	/**
	 * Get total price for selected services
	 */
	function getTotalPrice() {
		if (flowConfig.multiService && state.selectedServices.length > 0) {
			return state.selectedServices.reduce(function(total, service) {
				return total + getEffectivePrice(service);
			}, 0);
		}
		return state.selectedService ? getEffectivePrice(state.selectedService) : 0;
	}

	/**
	 * Load all staff (for staff_first and staff_only modes)
	 */
	function loadAllStaff() {
		var staffList = document.getElementById('unbsb-staff-list');
		if (!staffList) return;

		staffList.innerHTML = renderStaffSkeleton();

		ajaxRequest('unbsb_get_all_staff', {}, function(response) {
			if (response.success && response.data.length > 0) {
				state.staffList = response.data;
				// Load nearest slots for all staff
				loadStaffNearestSlots(null);
			} else {
				staffList.innerHTML = '<p class="unbsb-time-hint">' + unbsbPublic.strings.select_staff + '</p>';
			}
		});
	}

	/**
	 * Navigation
	 */
	function initNavigation() {
		nextBtn.addEventListener('click', function() {
			if (state.currentStep < state.totalSteps) {
				goToStep(state.currentStep + 1);
			}
		});

		prevBtn.addEventListener('click', function() {
			if (state.currentStep > 1) {
				goToStep(state.currentStep - 1);
			}
		});
	}

	/**
	 * Go to specific step
	 */
	function goToStep(step) {
		// Validate current step before moving forward
		if (step > state.currentStep && !validateStep(state.currentStep)) {
			return;
		}

		// Hide current step
		const currentStepEl = document.querySelector('.unbsb-step[data-step="' + state.currentStep + '"]');
		if (currentStepEl) {
			currentStepEl.style.display = 'none';
		}

		// Show new step
		const newStepEl = document.querySelector('.unbsb-step[data-step="' + step + '"]');
		if (newStepEl) {
			newStepEl.style.display = 'block';
		}

		// Update progress
		updateProgress(step);

		// Update state
		state.currentStep = step;

		// Load step data based on step key
		const stepKey = getCurrentStepKey();
		onStepEnter(stepKey);

		// Update buttons
		updateNavButtons();
		updateNextButton();
	}

	/**
	 * Handle step enter
	 */
	function onStepEnter(stepKey) {
		switch (stepKey) {
			case 'service':
				if (flowConfig.mode === 'staff_first') {
					// Load services for selected staff
					loadServicesForStaff();
				}
				break;
			case 'staff':
				if (flowConfig.mode === 'service_first') {
					// Load staff for selected service (with nearest slots)
					loadStaffForService();
				}
				break;
			case 'datetime':
				// If date+time already selected from staff availability cards, skip
				if (state.selectedDate && state.selectedTime && state.selectedStaff && state.selectedStaff.id !== 'any') {
					var infoStep = getStepNumber('info');
					if (infoStep) {
						goToStep(infoStep);
						return;
					}
				}
				// "Any Staff" → auto-select then show calendar
				if (state.selectedStaff && state.selectedStaff.id === 'any') {
					autoSelectStaff();
				} else if (flowConfig.mode === 'service_only' && !state.selectedStaff) {
					autoSelectStaff();
				} else {
					renderCalendar();
				}
				break;
			case 'info':
				renderSummary();
				break;
		}
	}

	/**
	 * Auto-select staff for service_only mode
	 */
	function autoSelectStaff() {
		if (!state.selectedService) return;

		ajaxRequest('unbsb_get_staff_for_service', {
			service_id: state.selectedService.id
		}, function(response) {
			if (response.success && response.data.length > 0) {
				// Auto-select first available staff
				state.selectedStaff = response.data[0];
				renderCalendar();
			} else {
				// No staff available
				const slotsContainer = document.getElementById('unbsb-time-slots');
				if (slotsContainer) {
					slotsContainer.innerHTML = '<p class="unbsb-time-hint">' + unbsbPublic.strings.no_slots + '</p>';
				}
			}
		});
	}

	/**
	 * Validate step
	 */
	function validateStep(step) {
		const stepConfig = flowConfig.steps[step - 1];
		if (!stepConfig) return true;

		switch (stepConfig.key) {
			case 'service':
				if (flowConfig.multiService) {
					return state.selectedServices.length > 0;
				}
				return !!state.selectedService;
			case 'staff':
				return !!state.selectedStaff;
			case 'datetime':
				return !!state.selectedDate && !!state.selectedTime;
			case 'info':
				return true;
			default:
				return true;
		}
	}

	/**
	 * Update progress indicators
	 */
	function updateProgress(step) {
		const steps = document.querySelectorAll('.unbsb-progress-step');

		steps.forEach(function(stepEl, index) {
			const stepNum = index + 1;

			stepEl.classList.remove('active', 'completed');

			if (stepNum < step) {
				stepEl.classList.add('completed');
			} else if (stepNum === step) {
				stepEl.classList.add('active');
			}
		});
	}

	/**
	 * Update navigation buttons
	 */
	function updateNavButtons() {
		prevBtn.style.display = state.currentStep > 1 ? 'inline-flex' : 'none';

		if (state.currentStep === state.totalSteps) {
			nextBtn.style.display = 'none';
			submitBtn.style.display = 'inline-flex';
		} else {
			nextBtn.style.display = 'inline-flex';
			submitBtn.style.display = 'none';
		}

		// Hide all buttons on success step
		if (state.currentStep === state.totalSteps + 1) {
			formActions.style.display = 'none';
		}
	}

	/**
	 * Update next button state
	 */
	function updateNextButton() {
		nextBtn.disabled = !validateStep(state.currentStep);
	}

	/**
	 * Load staff for selected service
	 */
	function loadStaffForService() {
		var staffList = document.getElementById('unbsb-staff-list');
		if (!staffList) return;

		staffList.innerHTML = renderStaffSkeleton();

		// Build service IDs — always check DOM first for reliability
		var serviceIds = [];

		// Multi-service: read checked checkboxes
		var checkedCheckboxes = document.querySelectorAll('input[name="service_ids[]"]:checked');
		if (checkedCheckboxes.length > 0) {
			serviceIds = Array.from(checkedCheckboxes).map(function(cb) { return cb.value; });
		}

		// Single-service: read checked radio
		if (0 === serviceIds.length) {
			var checkedRadio = document.querySelector('input[name="service_id"]:checked');
			if (checkedRadio) {
				serviceIds = [checkedRadio.value];
			}
		}

		// State fallback
		if (0 === serviceIds.length) {
			if (flowConfig.multiService && state.selectedServices.length > 0) {
				serviceIds = state.selectedServices.map(function(s) { return s.id; });
			} else if (state.selectedService) {
				serviceIds = [state.selectedService.id];
			}
		}

		loadStaffNearestSlots(serviceIds);
	}

	/**
	 * Load services for selected staff
	 */
	function loadServicesForStaff() {
		const serviceList = document.querySelector('.unbsb-service-list');
		if (!serviceList) return;

		serviceList.innerHTML = '<div class="unbsb-loading-spinner">' + unbsbPublic.strings.loading + '</div>';

		ajaxRequest('unbsb_get_services_for_staff', {
			staff_id: state.selectedStaff.id
		}, function(response) {
			if (response.success && response.data.length > 0) {
				state.servicesList = response.data;
				renderServiceList(response.data);
			} else {
				serviceList.innerHTML = '<p class="unbsb-time-hint">' + unbsbPublic.strings.no_services + '</p>';
			}
		});
	}

	/**
	 * Load nearest available slots for staff members via AJAX
	 */
	function loadStaffNearestSlots(serviceIds) {
		var staffList = document.getElementById('unbsb-staff-list');
		if (!staffList) return;

		// Ensure serviceIds is populated — DOM fallback
		if (!serviceIds || 0 === serviceIds.length) {
			serviceIds = [];
			var checkedCheckboxes = document.querySelectorAll('input[name="service_ids[]"]:checked');
			if (checkedCheckboxes.length > 0) {
				serviceIds = Array.from(checkedCheckboxes).map(function(cb) { return cb.value; });
			}
			if (0 === serviceIds.length) {
				var checkedRadio = document.querySelector('input[name="service_id"]:checked');
				if (checkedRadio) {
					serviceIds = [checkedRadio.value];
				}
			}
		}

		var params = {};
		if (serviceIds && serviceIds.length > 0) {
			params.service_ids = serviceIds.join(',');
		}

		params.action = 'unbsb_get_staff_nearest_slots';
		params.nonce = unbsbPublic.nonce;

		var formData = new FormData();
		for (var key in params) {
			formData.append(key, params[key]);
		}

		fetch(unbsbPublic.ajaxUrl, { method: 'POST', body: formData })
		.then(function(r) { return r.json(); })
		.then(function(response) {
			var staffData = response.success && response.data ? response.data : [];
			if (staffData.length > 0) {
				state.staffList = staffData;
				renderStaffAvailability(staffData);
			} else {
				staffList.innerHTML = '<p class="unbsb-time-hint">' + (unbsbPublic.strings.no_staff || 'No staff available.') + '</p>';
			}
		})
		.catch(function() {
			staffList.innerHTML = '<p class="unbsb-time-hint">' + (unbsbPublic.strings.no_staff || 'No staff available.') + '</p>';
		});
	}

	/**
	 * Render staff availability cards
	 */
	function renderStaffAvailability(staffData) {
		var staffList = document.getElementById('unbsb-staff-list');
		if (!staffList) return;

		var html = '';
		var strings = unbsbPublic.strings;

		// "Any Staff" option
		html += '<div class="unbsb-staff-avail-card unbsb-any-staff" data-staff-id="any">' +
			'<div class="unbsb-staff-header">' +
				'<div class="unbsb-staff-avatar">' +
					'<span class="unbsb-any-staff-icon">' +
						'<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>' +
					'</span>' +
				'</div>' +
				'<div class="unbsb-staff-info">' +
					'<h4 class="unbsb-staff-name">' + (strings.any_staff || 'Any Staff') + '</h4>' +
					'<p class="unbsb-staff-bio">' + (strings.any_staff_desc || 'We\'ll assign the first available staff member') + '</p>' +
				'</div>' +
			'</div>' +
		'</div>';

		// Staff cards with availability
		staffData.forEach(function(staff) {
			var avatar = staff.avatar_url
				? '<img src="' + staff.avatar_url + '" alt="' + (staff.staff_name || staff.name) + '">'
				: '<span class="unbsb-staff-avatar-placeholder">' + (staff.staff_name || staff.name).charAt(0) + '</span>';

			html += '<div class="unbsb-staff-avail-card" data-staff-id="' + staff.staff_id + '">' +
				'<div class="unbsb-staff-header">' +
					'<div class="unbsb-staff-avatar">' + avatar + '</div>' +
					'<div class="unbsb-staff-info">' +
						'<h4 class="unbsb-staff-name">' + staff.staff_name + '</h4>' +
						(staff.bio ? '<p class="unbsb-staff-bio">' + staff.bio + '</p>' : '') +
					'</div>' +
				'</div>';

			if (staff.nearest_date && staff.slots && staff.slots.length > 0) {
				html += '<div class="unbsb-staff-nearest-label">' +
					'<svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>' +
					(strings.nearest_available || 'Nearest available:') + ' ' + staff.nearest_date_formatted +
				'</div>' +
				'<div class="unbsb-staff-slots">';

				staff.slots.forEach(function(slot) {
					html += '<button type="button" class="unbsb-staff-slot" data-staff-id="' + staff.staff_id + '" data-date="' + staff.nearest_date + '" data-time="' + slot + '">' +
						formatTime(slot) +
					'</button>';
				});

				html += '<button type="button" class="unbsb-staff-more-dates" data-staff-id="' + staff.staff_id + '">' +
					(strings.more_dates || 'More dates') +
					' <svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6-6-6z"/></svg>' +
				'</button>' +
				'</div>';
			} else {
				html += '<p class="unbsb-staff-no-slots">' + (strings.no_available_slots || 'No available slots this week') + '</p>';
			}

			html += '</div>';
		});

		staffList.innerHTML = html;

		// --- Event listeners ---

		// Slot click: select staff + date + time
		staffList.querySelectorAll('.unbsb-staff-slot').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var staffId = this.dataset.staffId;
				var date = this.dataset.date;
				var time = this.dataset.time;

				// Set state
				state.selectedStaff = staffData.find(function(s) { return s.staff_id == staffId; });
				state.selectedDate = date;
				state.selectedTime = time;

				// Set hidden fields
				var dateInput = document.getElementById('booking-date');
				var timeInput = document.getElementById('start-time');
				if (dateInput) dateInput.value = date;
				if (timeInput) timeInput.value = time;

				// Update UI — highlight selected card and slot
				staffList.querySelectorAll('.unbsb-staff-avail-card').forEach(function(card) {
					card.classList.remove('unbsb-staff-selected');
				});
				this.closest('.unbsb-staff-avail-card').classList.add('unbsb-staff-selected');

				staffList.querySelectorAll('.unbsb-staff-slot').forEach(function(s) {
					s.classList.remove('active');
				});
				this.classList.add('active');

				updateNextButton();
			});
		});

		// "Any Staff" card click
		var anyStaffCard = staffList.querySelector('.unbsb-any-staff');
		if (anyStaffCard) {
			anyStaffCard.addEventListener('click', function() {
				// Set a marker so the system knows to auto-assign
				state.selectedStaff = { id: 'any', name: unbsbPublic.strings.any_staff || 'Any Staff' };
				state.selectedDate = null;
				state.selectedTime = null;

				// Clear hidden fields
				var dateInput = document.getElementById('booking-date');
				var timeInput = document.getElementById('start-time');
				if (dateInput) dateInput.value = '';
				if (timeInput) timeInput.value = '';

				// Highlight "Any Staff" card
				staffList.querySelectorAll('.unbsb-staff-avail-card').forEach(function(card) {
					card.classList.remove('unbsb-staff-selected');
				});
				this.classList.add('unbsb-staff-selected');

				staffList.querySelectorAll('.unbsb-staff-slot').forEach(function(s) {
					s.classList.remove('active');
				});

				updateNextButton();
			});
		}

		// "More dates" link → select staff and go to datetime step
		staffList.querySelectorAll('.unbsb-staff-more-dates').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var staffId = this.dataset.staffId;

				state.selectedStaff = staffData.find(function(s) { return s.staff_id == staffId; });
				state.selectedDate = null;
				state.selectedTime = null;

				// Highlight selected staff card
				staffList.querySelectorAll('.unbsb-staff-avail-card').forEach(function(card) {
					card.classList.remove('unbsb-staff-selected');
				});
				this.closest('.unbsb-staff-avail-card').classList.add('unbsb-staff-selected');

				staffList.querySelectorAll('.unbsb-staff-slot').forEach(function(s) {
					s.classList.remove('active');
				});

				// Jump to datetime step
				var datetimeStep = getStepNumber('datetime');
				if (datetimeStep) {
					goToStep(datetimeStep);
				}
			});
		});
	}

	/**
	 * Render staff loading skeleton
	 */
	function renderStaffSkeleton() {
		var html = '<div class="unbsb-staff-skeleton">';
		for (var i = 0; i < 3; i++) {
			html += '<div class="unbsb-staff-skeleton-card">' +
				'<div class="unbsb-skeleton-header">' +
					'<div class="unbsb-skeleton-avatar"></div>' +
					'<div class="unbsb-skeleton-lines">' +
						'<div class="unbsb-skeleton-line" style="width: 45%"></div>' +
						'<div class="unbsb-skeleton-line" style="width: 70%"></div>' +
					'</div>' +
				'</div>' +
				'<div class="unbsb-skeleton-slots">' +
					'<div class="unbsb-skeleton-slot"></div>' +
					'<div class="unbsb-skeleton-slot"></div>' +
					'<div class="unbsb-skeleton-slot"></div>' +
				'</div>' +
			'</div>';
		}
		html += '</div>';
		return html;
	}

	/**
	 * Legacy renderStaffList — fallback for staff_first mode
	 */
	function renderStaffList(staffData, isFirstStep) {
		var staffList = document.getElementById('unbsb-staff-list');
		if (!staffList) return;

		var html = '';

		staffData.forEach(function(staff) {
			var avatar = staff.avatar_url
				? '<img src="' + staff.avatar_url + '" alt="' + (staff.staff_name || staff.name) + '">'
				: '<span class="unbsb-staff-avatar-placeholder">' + (staff.staff_name || staff.name).charAt(0) + '</span>';

			html += '<div class="unbsb-staff-avail-card" data-staff-id="' + staff.id + '" style="cursor:pointer">' +
				'<div class="unbsb-staff-header">' +
					'<div class="unbsb-staff-avatar">' + avatar + '</div>' +
					'<div class="unbsb-staff-info">' +
						'<h4 class="unbsb-staff-name">' + (staff.staff_name || staff.name) + '</h4>' +
					'</div>' +
				'</div>' +
			'</div>';
		});

		staffList.innerHTML = html;

		// Click to select staff (simple mode without slots)
		staffList.querySelectorAll('.unbsb-staff-avail-card').forEach(function(card) {
			card.addEventListener('click', function() {
				var staffId = this.dataset.staffId;
				state.selectedStaff = staffData.find(function(s) {
					return s.id == staffId;
				});

				// Reset date and time
				state.selectedDate = null;
				state.selectedTime = null;

				// In staff_first mode, reset service too
				if (flowConfig.mode === 'staff_first') {
					state.selectedService = null;
				}

				// Highlight
				staffList.querySelectorAll('.unbsb-staff-avail-card').forEach(function(c) {
					c.classList.remove('unbsb-staff-selected');
				});
				this.classList.add('unbsb-staff-selected');

				updateNextButton();
			});
		});
	}

	/**
	 * Render service list (for staff_first mode)
	 */
	function renderServiceList(servicesData) {
		const serviceList = document.querySelector('.unbsb-service-list');
		if (!serviceList) return;

		const currency = unbsbPublic.currency;
		const inputType = flowConfig.multiService ? 'checkbox' : 'radio';
		const inputName = flowConfig.multiService ? 'service_ids[]' : 'service_id';
		const inputRequired = flowConfig.multiService ? '' : ' required';
		let html = '';

		servicesData.forEach(function(service) {
			var priceAreaHtml = '';
			var effectivePrice = hasDiscount(service) ? service.discounted_price : service.price;

			if (hasDiscount(service)) {
				var originalPrice = currency.position === 'before'
					? currency.symbol + Math.round(service.price)
					: Math.round(service.price) + ' ' + currency.symbol;
				var discountedPriceNum = Math.round(parseFloat(service.discounted_price));
				var discountPct = Math.round(((parseFloat(service.price) - parseFloat(service.discounted_price)) / parseFloat(service.price)) * 100);

				priceAreaHtml = '<div class="unbsb-service-price-area">' +
					'<span class="unbsb-price-original-top">' + originalPrice + '</span>' +
					'<span class="unbsb-price-current unbsb-price-discounted-big">' +
						(currency.position === 'before'
							? currency.symbol + discountedPriceNum
							: discountedPriceNum + '<small> ' + currency.symbol + '</small>') +
					'</span>' +
					'<span class="unbsb-discount-badge">-' + discountPct + '%</span>' +
				'</div>';
			} else {
				var priceNum = Math.round(parseFloat(service.price));
				priceAreaHtml = '<div class="unbsb-service-price-area">' +
					'<span class="unbsb-price-current">' +
						(currency.position === 'before'
							? currency.symbol + priceNum
							: priceNum + '<small> ' + currency.symbol + '</small>') +
					'</span>' +
				'</div>';
			}

			html += '<label class="unbsb-service-item" data-service-id="' + service.id + '"' +
				' data-category-id="' + (service.category_id || '0') + '"' +
				' data-price="' + effectivePrice + '"' +
				' data-duration="' + service.duration + '">' +
				'<input type="' + inputType + '" name="' + inputName + '" value="' + service.id + '"' + inputRequired + '>' +
				'<div class="unbsb-service-card">' +
					'<span class="unbsb-service-check">' +
						'<svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>' +
					'</span>' +
					'<span class="unbsb-service-color" style="background-color: ' + (service.color || '#3788d8') + '"></span>' +
					'<div class="unbsb-service-info">' +
						'<div class="unbsb-service-name-row">' +
							'<strong class="unbsb-service-name">' + service.name + '</strong>' +
							'<span class="unbsb-service-duration">' +
								'<svg viewBox="0 0 24 24" width="12" height="12"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>' +
								service.duration + ' ' + unbsbPublic.strings.minute_short +
							'</span>' +
						'</div>' +
						(service.description ? '<p class="unbsb-service-desc">' + service.description + '</p>' : '') +
					'</div>' +
					priceAreaHtml +
				'</div>' +
			'</label>';
		});

		serviceList.innerHTML = html;

		// Add event listeners
		var inputSelector = 'input[type="' + inputType + '"]';
		serviceList.querySelectorAll(inputSelector).forEach(function(input) {
			input.addEventListener('change', function() {
				if (flowConfig.multiService) {
					handleMultiServiceChange();
				} else {
					var serviceId = this.value;
					state.selectedService = servicesData.find(function(s) {
						return s.id == serviceId;
					});
				}

				// Reset date and time
				state.selectedDate = null;
				state.selectedTime = null;

				updateNextButton();
			});
		});
	}

	/**
	 * Calendar
	 */
	function initCalendar() {
		const calPrevBtn = document.getElementById('unbsb-prev-month');
		const calNextBtn = document.getElementById('unbsb-next-month');

		if (calPrevBtn) {
			calPrevBtn.addEventListener('click', function() {
				state.currentMonth.setMonth(state.currentMonth.getMonth() - 1);
				renderCalendar();
			});
		}

		if (calNextBtn) {
			calNextBtn.addEventListener('click', function() {
				state.currentMonth.setMonth(state.currentMonth.getMonth() + 1);
				renderCalendar();
			});
		}
	}

	/**
	 * Render calendar
	 */
	function renderCalendar() {
		const titleEl = document.getElementById('unbsb-calendar-title');
		const bodyEl = document.getElementById('unbsb-calendar-body');

		if (!titleEl || !bodyEl) return;

		const year = state.currentMonth.getFullYear();
		const month = state.currentMonth.getMonth();

		// Update title
		const monthNames = unbsbPublic.strings.month_names;
		titleEl.textContent = monthNames[month] + ' ' + year;

		// Build calendar
		const firstDay = new Date(year, month, 1);
		const lastDay = new Date(year, month + 1, 0);
		const startDay = firstDay.getDay() || 7; // Monday = 1

		let html = '<div class="unbsb-calendar-grid">';

		// Day headers
		const dayNames = unbsbPublic.strings.day_names;
		dayNames.forEach(function(day) {
			html += '<div class="unbsb-calendar-day-header">' + day + '</div>';
		});

		// Calendar days
		const today = new Date();
		today.setHours(0, 0, 0, 0);

		const maxFutureDays = 30; // From settings
		const maxDate = new Date();
		maxDate.setDate(maxDate.getDate() + maxFutureDays);

		// Previous month days
		for (let i = 1; i < startDay; i++) {
			const day = new Date(year, month, 1 - (startDay - i));
			html += '<div class="unbsb-calendar-day other-month">' + day.getDate() + '</div>';
		}

		// Current month days
		for (let day = 1; day <= lastDay.getDate(); day++) {
			const date = new Date(year, month, day);
			const dateStr = formatDate(date);

			let className = 'unbsb-calendar-day';

			if (date < today || date > maxDate) {
				className += ' disabled';
			}

			if (date.toDateString() === today.toDateString()) {
				className += ' today';
			}

			if (state.selectedDate === dateStr) {
				className += ' selected';
			}

			html += '<div class="' + className + '" data-date="' + dateStr + '">' + day + '</div>';
		}

		// Next month days
		const remaining = 42 - (startDay - 1 + lastDay.getDate());
		for (let i = 1; i <= remaining; i++) {
			html += '<div class="unbsb-calendar-day other-month">' + i + '</div>';
		}

		html += '</div>';
		bodyEl.innerHTML = html;

		// Add click events
		bodyEl.querySelectorAll('.unbsb-calendar-day:not(.disabled):not(.other-month)').forEach(function(dayEl) {
			dayEl.addEventListener('click', function() {
				// Remove previous selection
				bodyEl.querySelectorAll('.unbsb-calendar-day').forEach(function(d) {
					d.classList.remove('selected');
				});

				// Select new date
				this.classList.add('selected');
				state.selectedDate = this.dataset.date;
				document.getElementById('booking-date').value = state.selectedDate;

				// Reset time
				state.selectedTime = null;
				document.getElementById('start-time').value = '';

				// Load available slots
				loadAvailableSlots();
				updateNextButton();
			});
		});
	}

	/**
	 * Load available time slots
	 */
	function loadAvailableSlots() {
		const slotsContainer = document.getElementById('unbsb-time-slots');
		if (!slotsContainer) return;

		slotsContainer.innerHTML = '<div class="unbsb-loading-spinner">' + unbsbPublic.strings.loading + '</div>';

		// For staff_only mode, use first staff if none selected
		const staffId = state.selectedStaff ? state.selectedStaff.id : null;
		const serviceId = state.selectedService ? state.selectedService.id : null;

		if (!staffId || !serviceId) {
			slotsContainer.innerHTML = '<p class="unbsb-time-hint">' + unbsbPublic.strings.no_slots + '</p>';
			return;
		}

		// Send total duration in multi-service mode
		const requestData = {
			staff_id: staffId,
			service_id: serviceId,
			date: state.selectedDate
		};

		if (flowConfig.multiService && state.selectedServices.length > 0) {
			requestData.total_duration = getTotalDuration();
		}

		ajaxRequest('unbsb_get_available_slots', requestData, function(response) {
			if (response.success && response.data.length > 0) {
				state.availableSlots = response.data;
				renderTimeSlots(response.data);
			} else {
				slotsContainer.innerHTML = '<p class="unbsb-time-hint">' + unbsbPublic.strings.no_slots + '</p>';
			}
		});
	}

	/**
	 * Render time slots
	 */
	function renderTimeSlots(slots) {
		const container = document.getElementById('unbsb-time-slots');
		if (!container) return;

		let html = '<div class="unbsb-time-grid">';

		slots.forEach(function(slot) {
			const className = slot.available ? 'unbsb-time-slot' : 'unbsb-time-slot disabled';
			html += '<div class="' + className + '" data-time="' + slot.start + '">' + formatTime(slot.start) + '</div>';
		});

		html += '</div>';
		container.innerHTML = html;

		// Add click events
		container.querySelectorAll('.unbsb-time-slot:not(.disabled)').forEach(function(slotEl) {
			slotEl.addEventListener('click', function() {
				// Remove previous selection
				container.querySelectorAll('.unbsb-time-slot').forEach(function(s) {
					s.classList.remove('selected');
				});

				// Select new time
				this.classList.add('selected');
				state.selectedTime = this.dataset.time;
				document.getElementById('start-time').value = state.selectedTime;

				updateNextButton();
			});
		});
	}

	/**
	 * Promo code functionality
	 */
	function initPromoCode() {
		var applyBtn = document.getElementById('unbsb-apply-promo');
		var promoInput = document.getElementById('unbsb-promo-code-input');
		var promoHidden = document.getElementById('unbsb-promo-code-hidden');
		var promoMessage = document.getElementById('unbsb-promo-code-message');

		if (!applyBtn || !promoInput) return;

		applyBtn.addEventListener('click', function() {
			var code = promoInput.value.trim().toUpperCase();

			// If promo already applied, this is a "Remove" action
			if (state.promoCode) {
				clearPromoCode();
				return;
			}

			if (!code) return;

			// Require login to use promo codes
			if (!unbsbPublic.isLoggedIn) {
				var loginMsg = unbsbPublic.strings.promo_login_required;
				if (unbsbPublic.loginUrl) {
					loginMsg += ' <a href="' + unbsbPublic.loginUrl + '" style="font-weight:600; text-decoration:underline;">' + (unbsbPublic.strings.login || 'Log In') + '</a>';
				}
				showPromoMessage(loginMsg, 'error');
				return;
			}

			// Gather validation data
			var customerEmail = document.getElementById('customer-email') ? document.getElementById('customer-email').value : '';
			var totalAmount = getTotalPrice();
			var serviceIds = [];

			if (flowConfig.multiService && state.selectedServices.length > 0) {
				serviceIds = state.selectedServices.map(function(s) { return s.id; });
			} else if (state.selectedService) {
				serviceIds = [state.selectedService.id];
			}

			applyBtn.disabled = true;
			applyBtn.textContent = unbsbPublic.strings.loading;

			ajaxRequest('unbsb_validate_promo_code', {
				promo_code: code,
				customer_email: customerEmail,
				service_ids: JSON.stringify(serviceIds),
				service_id: serviceIds.length > 0 ? serviceIds[0] : 0,
				total_amount: totalAmount
			}, function(response) {
				applyBtn.disabled = false;

				if (response.success) {
					state.promoCode = code;
					state.promoCodeId = response.data.promo_code_id;
					state.discountAmount = parseFloat(response.data.discount_amount);
					state.discountType = response.data.discount_type;

					promoHidden.value = code;
					promoInput.readOnly = true;
					promoInput.classList.add('unbsb-promo-applied');

					applyBtn.textContent = unbsbPublic.strings.remove || 'Remove';
					applyBtn.classList.add('unbsb-promo-remove-btn');

					showPromoMessage(response.data.message, 'success');
					renderSummary();
				} else {
					showPromoMessage(response.data || unbsbPublic.strings.promo_invalid, 'error');
				}
			});
		});

		// Enter key on promo input
		promoInput.addEventListener('keydown', function(e) {
			if ('Enter' === e.key) {
				e.preventDefault();
				applyBtn.click();
			}
		});

		function showPromoMessage(message, type) {
			if (promoMessage) {
				promoMessage.innerHTML = message;
				promoMessage.className = 'unbsb-promo-message unbsb-promo-message-' + type;
				promoMessage.style.display = 'block';
			}
		}
	}

	function clearPromoCode() {
		state.promoCode = null;
		state.promoCodeId = null;
		state.discountAmount = 0;
		state.discountType = null;

		var promoInput = document.getElementById('unbsb-promo-code-input');
		var promoHidden = document.getElementById('unbsb-promo-code-hidden');
		var promoMessage = document.getElementById('unbsb-promo-code-message');
		var applyBtn = document.getElementById('unbsb-apply-promo');

		if (promoInput) {
			promoInput.value = '';
			promoInput.readOnly = false;
			promoInput.classList.remove('unbsb-promo-applied');
		}
		if (promoHidden) promoHidden.value = '';
		if (promoMessage) promoMessage.style.display = 'none';
		if (applyBtn) {
			applyBtn.textContent = unbsbPublic.strings.apply || 'Apply';
			applyBtn.classList.remove('unbsb-promo-remove-btn');
		}

		renderSummary();
	}

	/**
	 * Render booking summary
	 */
	function renderSummary() {
		const summaryEl = document.getElementById('unbsb-booking-summary');
		if (!summaryEl) return;

		const currency = unbsbPublic.currency;
		const formattedDate = formatDisplayDate(state.selectedDate);

		let html = '';

		// List services in multi-service mode
		if (flowConfig.multiService && state.selectedServices.length > 0) {
			html += '<div class="unbsb-summary-row">' +
				'<span class="unbsb-summary-label">' + unbsbPublic.strings.services + '</span>' +
				'<span class="unbsb-summary-value">' +
				state.selectedServices.map(function(s) { return s.name; }).join(', ') +
				'</span>' +
			'</div>';
		} else if (state.selectedService) {
			html += '<div class="unbsb-summary-row">' +
				'<span class="unbsb-summary-label">' + unbsbPublic.strings.service + '</span>' +
				'<span class="unbsb-summary-value">' + state.selectedService.name + '</span>' +
			'</div>';
		}

		// Only show staff if there's a staff step or service_only mode
		if (state.selectedStaff) {
			html += '<div class="unbsb-summary-row">' +
				'<span class="unbsb-summary-label">' + unbsbPublic.strings.staff + '</span>' +
				'<span class="unbsb-summary-value">' + state.selectedStaff.name + '</span>' +
			'</div>';
		}

		// Total duration and price
		const totalDuration = getTotalDuration();
		const totalPrice = getTotalPrice();
		const priceStr = currency.position === 'before'
			? currency.symbol + formatNumber(totalPrice)
			: formatNumber(totalPrice) + ' ' + currency.symbol;

		html += '<div class="unbsb-summary-row">' +
			'<span class="unbsb-summary-label">' + unbsbPublic.strings.date + '</span>' +
			'<span class="unbsb-summary-value">' + formattedDate + '</span>' +
		'</div>' +
		'<div class="unbsb-summary-row">' +
			'<span class="unbsb-summary-label">' + unbsbPublic.strings.time + '</span>' +
			'<span class="unbsb-summary-value">' + formatTime(state.selectedTime) + '</span>' +
		'</div>' +
		'<div class="unbsb-summary-row">' +
			'<span class="unbsb-summary-label">' + unbsbPublic.strings.duration + '</span>' +
			'<span class="unbsb-summary-value">' + totalDuration + ' ' + unbsbPublic.strings.minute_short + '</span>' +
		'</div>';

		// Show discount row if promo code applied
		if (state.promoCode && state.discountAmount > 0) {
			var discountStr = currency.position === 'before'
				? '-' + currency.symbol + formatNumber(state.discountAmount)
				: '-' + formatNumber(state.discountAmount) + ' ' + currency.symbol;

			html += '<div class="unbsb-summary-row unbsb-summary-discount">' +
				'<span class="unbsb-summary-label">' + (unbsbPublic.strings.discount || 'Discount') + ' <span class="unbsb-promo-badge">' + state.promoCode + '</span></span>' +
				'<span class="unbsb-summary-value unbsb-discount-value">' + discountStr + '</span>' +
			'</div>';

			var discountedTotal = Math.max(0, totalPrice - state.discountAmount);
			var discountedPriceStr = currency.position === 'before'
				? currency.symbol + formatNumber(discountedTotal)
				: formatNumber(discountedTotal) + ' ' + currency.symbol;

			html += '<div class="unbsb-summary-row total">' +
				'<span class="unbsb-summary-label">' + unbsbPublic.strings.total + '</span>' +
				'<span class="unbsb-summary-value">' + discountedPriceStr + '</span>' +
			'</div>';
		} else {
			html += '<div class="unbsb-summary-row total">' +
				'<span class="unbsb-summary-label">' + unbsbPublic.strings.total + '</span>' +
				'<span class="unbsb-summary-value">' + priceStr + '</span>' +
			'</div>';
		}

		summaryEl.innerHTML = html;
	}

	/**
	 * Form submit
	 */
	function initFormSubmit() {
		form.addEventListener('submit', async function(e) {
			e.preventDefault();

			// Validate required fields
			const name = document.getElementById('customer-name').value;
			const email = document.getElementById('customer-email').value;

			if (!name || !email) {
				alert(unbsbPublic.strings.required_fields);
				return;
			}

			// Disable submit button
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<span class="unbsb-loading-spinner"></span> ' + unbsbPublic.strings.loading;

			// Collect form data
			const formData = new FormData(form);
			const data = {};

			// Handle array fields (service_ids[])
			formData.forEach(function(value, key) {
				if (key.endsWith('[]')) {
					const baseKey = key.slice(0, -2);
					if (!data[baseKey]) {
						data[baseKey] = [];
					}
					data[baseKey].push(value);
				} else {
					data[key] = value;
				}
			});

			// Ensure staff_id is set for service_only mode
			if (flowConfig.mode === 'service_only' && state.selectedStaff) {
				data.staff_id = state.selectedStaff.id;
			}

			// Add extra data in multi-service mode
			if (flowConfig.multiService && state.selectedServices.length > 0) {
				data.multi_service = true;
				data.total_duration = getTotalDuration();
				data.total_price = getTotalPrice();
				// Service IDs as JSON array
				data.service_ids = JSON.stringify(state.selectedServices.map(function(s) { return s.id; }));
			}

			// Add promo code if applied
			if (state.promoCode) {
				data.promo_code = state.promoCode;
			}

			// Get CAPTCHA token if enabled (reCAPTCHA v3)
			if (unbsbPublic.captcha && unbsbPublic.captcha.enabled && unbsbPublic.captcha.provider === 'recaptcha') {
				try {
					if (typeof window.unbsbGetCaptchaToken === 'function') {
						data.captcha_token = await window.unbsbGetCaptchaToken('booking');
					}
				} catch (captchaError) {
					console.error('CAPTCHA error:', captchaError);
					// Continue without token - server will handle validation
				}
			}

			// Submit booking
			ajaxRequest('unbsb_create_booking', data, function(response) {
				if (response.success) {
					// Show success step
					goToStep(state.totalSteps + 1);
					renderSuccessDetails(response.data.booking);
				} else {
					alert(response.data || unbsbPublic.strings.booking_error);
					submitBtn.disabled = false;
					submitBtn.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg> ' + unbsbPublic.strings.book_now;
				}
			});
		});
	}

	/**
	 * Render success details
	 */
	function renderSuccessDetails(booking) {
		const detailsEl = document.getElementById('unbsb-success-details');
		if (!detailsEl) return;

		const currency = unbsbPublic.currency;

		const price = currency.position === 'before'
			? currency.symbol + booking.price
			: booking.price + ' ' + currency.symbol;

		const formattedDate = formatDisplayDate(booking.booking_date);

		let html = '<div class="unbsb-summary-row">' +
			'<span class="unbsb-summary-label">' + unbsbPublic.strings.service + '</span>' +
			'<span class="unbsb-summary-value">' + booking.service_name + '</span>' +
		'</div>' +
		'<div class="unbsb-summary-row">' +
			'<span class="unbsb-summary-label">' + unbsbPublic.strings.staff + '</span>' +
			'<span class="unbsb-summary-value">' + booking.staff_name + '</span>' +
		'</div>' +
		'<div class="unbsb-summary-row">' +
			'<span class="unbsb-summary-label">' + unbsbPublic.strings.date + '</span>' +
			'<span class="unbsb-summary-value">' + formattedDate + '</span>' +
		'</div>' +
		'<div class="unbsb-summary-row">' +
			'<span class="unbsb-summary-label">' + unbsbPublic.strings.time + '</span>' +
			'<span class="unbsb-summary-value">' + formatTime(booking.start_time.substring(0, 5)) + '</span>' +
		'</div>' +
		'<div class="unbsb-summary-row total">' +
			'<span class="unbsb-summary-label">' + unbsbPublic.strings.total + '</span>' +
			'<span class="unbsb-summary-value">' + price + '</span>' +
		'</div>';

		detailsEl.innerHTML = html;
	}

	/**
	 * AJAX Request helper
	 */
	function ajaxRequest(action, data, callback) {
		data = data || {};
		data.action = action;
		data.nonce = unbsbPublic.nonce;

		const formData = new FormData();
		for (const key in data) {
			formData.append(key, data[key]);
		}

		fetch(unbsbPublic.ajaxUrl, {
			method: 'POST',
			body: formData
		})
		.then(function(response) {
			return response.json();
		})
		.then(function(response) {
			if (callback) {
				callback(response);
			}
		})
		.catch(function(error) {
			console.error('AJAX Error:', error);
			if (callback) {
				callback({ success: false, data: unbsbPublic.strings.booking_error });
			}
		});
	}

	/**
	 * Get service by ID
	 */
	function getServiceById(id) {
		if (typeof unbsbServicesData !== 'undefined') {
			return unbsbServicesData.find(function(s) {
				return s.id == id;
			});
		}
		return null;
	}

	/**
	 * Format date to YYYY-MM-DD
	 */
	function formatDate(date) {
		const year = date.getFullYear();
		const month = String(date.getMonth() + 1).padStart(2, '0');
		const day = String(date.getDate()).padStart(2, '0');
		return year + '-' + month + '-' + day;
	}

	/**
	 * Format date for display
	 */
	function formatDisplayDate(dateStr) {
		const date = new Date(dateStr);
		const day = date.getDate();
		const monthNames = unbsbPublic.strings.month_names;
		const month = monthNames[date.getMonth()];
		const year = date.getFullYear();

		return day + ' ' + month + ' ' + year;
	}

	/**
	 * ==========================================
	 * Account Page (Login / Register / My Bookings)
	 * ==========================================
	 */
	function initAccountPage() {
		var wrapper = document.querySelector('.unbsb-account-wrapper');
		if ( ! wrapper ) {
			return;
		}

		var accountData = typeof unbsbAccount !== 'undefined' ? unbsbAccount : null;
		var ajaxUrl = accountData ? accountData.ajaxUrl : (typeof unbsbPublic !== 'undefined' ? unbsbPublic.ajaxUrl : '');

		// Tab switching.
		var tabs = wrapper.querySelectorAll('.unbsb-auth-tab');
		var loginPanel = document.getElementById('unbsb-login-panel');
		var registerPanel = document.getElementById('unbsb-register-panel');

		tabs.forEach(function(tab) {
			tab.addEventListener('click', function() {
				var target = this.getAttribute('data-tab');

				tabs.forEach(function(t) { t.classList.remove('active'); });
				this.classList.add('active');

				if ( target === 'login' ) {
					loginPanel.style.display = '';
					loginPanel.classList.add('active');
					registerPanel.style.display = 'none';
					registerPanel.classList.remove('active');
				} else {
					registerPanel.style.display = '';
					registerPanel.classList.add('active');
					loginPanel.style.display = 'none';
					loginPanel.classList.remove('active');
				}
			});
		});

		// Get nonce from hidden form field as fallback.
		var nonce = accountData ? accountData.nonce : '';
		if ( ! nonce ) {
			var nonceField = document.querySelector('#unbsb-login-form [name="unbsb_auth_nonce"]');
			if ( nonceField ) {
				nonce = nonceField.value;
			}
		}

		// Login form.
		var loginForm = document.getElementById('unbsb-login-form');
		if ( loginForm && ajaxUrl ) {
			loginForm.addEventListener('submit', function(e) {
				e.preventDefault();
				var msgEl = document.getElementById('unbsb-login-message');
				var btn = loginForm.querySelector('button[type="submit"]');
				var originalText = btn.textContent;

				btn.disabled = true;
				btn.textContent = '...';
				msgEl.style.display = 'none';

				var formData = new FormData();
				formData.append('action', 'unbsb_customer_login');
				formData.append('nonce', nonce);
				formData.append('email', loginForm.querySelector('[name="email"]').value);
				formData.append('password', loginForm.querySelector('[name="password"]').value);

				fetch(ajaxUrl, {
					method: 'POST',
					body: formData
				})
				.then(function(res) { return res.json(); })
				.then(function(res) {
					if ( res.success ) {
						msgEl.className = 'unbsb-auth-message success';
						msgEl.textContent = res.data.message || (accountData && accountData.strings && accountData.strings.ok ? accountData.strings.ok : 'OK');
						msgEl.style.display = '';
						setTimeout(function() { location.reload(); }, 800);
					} else {
						msgEl.className = 'unbsb-auth-message error';
						msgEl.textContent = res.data || (accountData && accountData.strings && accountData.strings.error ? accountData.strings.error : 'Error');
						msgEl.style.display = '';
						btn.disabled = false;
						btn.textContent = originalText;
					}
				})
				.catch(function() {
					msgEl.className = 'unbsb-auth-message error';
					msgEl.textContent = accountData && accountData.strings && accountData.strings.connection_error ? accountData.strings.connection_error : 'Connection error';
					msgEl.style.display = '';
					btn.disabled = false;
					btn.textContent = originalText;
				});
			});
		}

		// Register form.
		var registerForm = document.getElementById('unbsb-register-form');
		if ( registerForm && ajaxUrl ) {
			// Get register nonce from its own form.
			var registerNonce = nonce;
			var regNonceField = registerForm.querySelector('[name="unbsb_auth_nonce_register"]');
			if ( regNonceField ) {
				registerNonce = regNonceField.value;
			}

			registerForm.addEventListener('submit', function(e) {
				e.preventDefault();
				var msgEl = document.getElementById('unbsb-register-message');
				var btn = registerForm.querySelector('button[type="submit"]');
				var originalText = btn.textContent;

				var password = registerForm.querySelector('[name="password"]').value;
				var passwordConfirm = registerForm.querySelector('[name="password_confirm"]').value;

				if ( password !== passwordConfirm ) {
					msgEl.className = 'unbsb-auth-message error';
					msgEl.textContent = accountData && accountData.strings && accountData.strings.password_mismatch ? accountData.strings.password_mismatch : 'Passwords do not match';
					msgEl.style.display = '';
					return;
				}

				btn.disabled = true;
				btn.textContent = '...';
				msgEl.style.display = 'none';

				var formData = new FormData();
				formData.append('action', 'unbsb_customer_register');
				formData.append('nonce', registerNonce);
				formData.append('name', registerForm.querySelector('[name="name"]').value);
				formData.append('email', registerForm.querySelector('[name="email"]').value);
				formData.append('phone', registerForm.querySelector('[name="phone"]').value);
				formData.append('password', password);
				formData.append('password_confirm', passwordConfirm);

				fetch(ajaxUrl, {
					method: 'POST',
					body: formData
				})
				.then(function(res) { return res.json(); })
				.then(function(res) {
					if ( res.success ) {
						msgEl.className = 'unbsb-auth-message success';
						msgEl.textContent = res.data.message || (accountData && accountData.strings && accountData.strings.ok ? accountData.strings.ok : 'OK');
						msgEl.style.display = '';
						setTimeout(function() { location.reload(); }, 800);
					} else {
						msgEl.className = 'unbsb-auth-message error';
						msgEl.textContent = res.data || (accountData && accountData.strings && accountData.strings.error ? accountData.strings.error : 'Error');
						msgEl.style.display = '';
						btn.disabled = false;
						btn.textContent = originalText;
					}
				})
				.catch(function() {
					msgEl.className = 'unbsb-auth-message error';
					msgEl.textContent = accountData && accountData.strings && accountData.strings.connection_error ? accountData.strings.connection_error : 'Connection error';
					msgEl.style.display = '';
					btn.disabled = false;
					btn.textContent = originalText;
				});
			});
		}
	}

	// Initialize account page.
	if ( document.readyState === 'loading' ) {
		document.addEventListener('DOMContentLoaded', initAccountPage);
	} else {
		initAccountPage();
	}

})();
