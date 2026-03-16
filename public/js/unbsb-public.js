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
		const filterContainer = document.getElementById('unbsb-category-filter');
		if (!filterContainer) return;

		const filterBtns = filterContainer.querySelectorAll('.unbsb-filter-btn');
		const serviceItems = document.querySelectorAll('.unbsb-service-item');

		filterBtns.forEach(function(btn) {
			btn.addEventListener('click', function() {
				const categoryId = this.dataset.category;

				// Update active state
				filterBtns.forEach(function(b) {
					b.classList.remove('active');
				});
				this.classList.add('active');

				// Servisleri filtrele
				serviceItems.forEach(function(item) {
					const itemCategoryId = item.dataset.categoryId;

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
			});
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
					// Hizmeti ekle
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
				updateNextButton();
			});
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
		const staffList = document.getElementById('unbsb-staff-list');
		if (!staffList) return;

		staffList.innerHTML = '<div class="unbsb-loading-spinner">' + unbsbPublic.strings.loading + '</div>';

		ajaxRequest('unbsb_get_all_staff', {}, function(response) {
			if (response.success && response.data.length > 0) {
				state.staffList = response.data;
				renderStaffList(response.data, true);
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
					// Load staff for selected service
					loadStaffForService();
				}
				break;
			case 'datetime':
				// Auto-select staff for service_only mode
				if (flowConfig.mode === 'service_only' && !state.selectedStaff) {
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
		const staffList = document.getElementById('unbsb-staff-list');
		if (!staffList) return;

		staffList.innerHTML = '<div class="unbsb-loading-spinner">' + unbsbPublic.strings.loading + '</div>';

		ajaxRequest('unbsb_get_staff_for_service', {
			service_id: state.selectedService.id
		}, function(response) {
			if (response.success && response.data.length > 0) {
				state.staffList = response.data;
				renderStaffList(response.data, false);
			} else {
				staffList.innerHTML = '<p class="unbsb-time-hint">' + unbsbPublic.strings.select_staff + '</p>';
			}
		});
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
	 * Render staff list
	 */
	function renderStaffList(staffData, isFirstStep) {
		const staffList = document.getElementById('unbsb-staff-list');
		if (!staffList) return;

		let html = '';

		staffData.forEach(function(staff) {
			const avatar = staff.avatar_url
				? '<img src="' + staff.avatar_url + '" alt="' + staff.name + '">'
				: '<span class="unbsb-staff-avatar-placeholder">' + staff.name.charAt(0) + '</span>';

			html += '<label class="unbsb-staff-item">' +
				'<input type="radio" name="staff_id" value="' + staff.id + '">' +
				'<div class="unbsb-staff-card">' +
					'<div class="unbsb-staff-avatar">' + avatar + '</div>' +
					'<h4 class="unbsb-staff-name">' + staff.name + '</h4>' +
				'</div>' +
			'</label>';
		});

		staffList.innerHTML = html;

		// Add event listeners
		staffList.querySelectorAll('input[type="radio"]').forEach(function(input) {
			input.addEventListener('change', function() {
				state.selectedStaff = staffData.find(function(s) {
					return s.id == this.value;
				}.bind(this));

				// Reset date and time
				state.selectedDate = null;
				state.selectedTime = null;

				// In staff_first mode, reset service too
				if (flowConfig.mode === 'staff_first') {
					state.selectedService = null;
				}

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
						'<strong class="unbsb-service-name">' + service.name + '</strong>' +
						(service.description ? '<p class="unbsb-service-desc">' + service.description + '</p>' : '') +
						'<span class="unbsb-service-duration">' +
							'<svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>' +
							service.duration + ' ' + unbsbPublic.strings.minute_short +
						'</span>' +
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
						msgEl.textContent = res.data.message || 'OK';
						msgEl.style.display = '';
						setTimeout(function() { location.reload(); }, 800);
					} else {
						msgEl.className = 'unbsb-auth-message error';
						msgEl.textContent = res.data || 'Error';
						msgEl.style.display = '';
						btn.disabled = false;
						btn.textContent = originalText;
					}
				})
				.catch(function() {
					msgEl.className = 'unbsb-auth-message error';
					msgEl.textContent = 'Connection error';
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
					msgEl.textContent = accountData && accountData.strings ? accountData.strings.password_mismatch : 'Passwords do not match';
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
						msgEl.textContent = res.data.message || 'OK';
						msgEl.style.display = '';
						setTimeout(function() { location.reload(); }, 800);
					} else {
						msgEl.className = 'unbsb-auth-message error';
						msgEl.textContent = res.data || 'Error';
						msgEl.style.display = '';
						btn.disabled = false;
						btn.textContent = originalText;
					}
				})
				.catch(function() {
					msgEl.className = 'unbsb-auth-message error';
					msgEl.textContent = 'Connection error';
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
