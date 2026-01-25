/**
 * Appointment General - Public JavaScript
 *
 * @package Appointment_General
 */

(function() {
	'use strict';

	// Flow configuration (from PHP)
	const flowConfig = typeof agFlowConfig !== 'undefined' ? agFlowConfig : {
		mode: 'service_first',
		steps: [
			{ key: 'service', label: 'Hizmet' },
			{ key: 'staff', label: 'Personel' },
			{ key: 'datetime', label: 'Tarih/Saat' },
			{ key: 'info', label: 'Bilgiler' }
		],
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
		selectedServices: [], // Çoklu hizmet için array
		selectedStaff: null,
		selectedDate: null,
		selectedTime: null,
		currentMonth: new Date(),
		availableSlots: [],
		staffList: [],
		servicesList: []
	};

	// DOM Elements
	let form, nextBtn, prevBtn, submitBtn, formActions;

	// Initialize
	document.addEventListener('DOMContentLoaded', function() {
		const bookingWrapper = document.getElementById('ag-booking-form');
		if (!bookingWrapper) return;

		form = document.getElementById('ag-booking-wizard');
		nextBtn = document.getElementById('ag-next-btn');
		prevBtn = document.getElementById('ag-prev-btn');
		submitBtn = document.getElementById('ag-submit-btn');
		formActions = document.getElementById('ag-form-actions');

		// Initialize based on flow mode
		initFlowMode();
		initNavigation();
		initCalendar();
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
		const filterContainer = document.getElementById('ag-category-filter');
		if (!filterContainer) return;

		const filterBtns = filterContainer.querySelectorAll('.ag-filter-btn');
		const serviceItems = document.querySelectorAll('.ag-service-item');

		filterBtns.forEach(function(btn) {
			btn.addEventListener('click', function() {
				const categoryId = this.dataset.category;

				// Active state güncelle
				filterBtns.forEach(function(b) {
					b.classList.remove('active');
				});
				this.classList.add('active');

				// Servisleri filtrele
				serviceItems.forEach(function(item) {
					const itemCategoryId = item.dataset.categoryId;

					if (categoryId === 'all') {
						item.style.display = '';
						item.classList.remove('ag-filtered-out');
					} else if (itemCategoryId === categoryId) {
						item.style.display = '';
						item.classList.remove('ag-filtered-out');
					} else {
						item.style.display = 'none';
						item.classList.add('ag-filtered-out');
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
		const serviceItems = document.querySelectorAll('.ag-service-item input[type="radio"]');

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
		const serviceItems = document.querySelectorAll('.ag-service-item input[type="checkbox"]');

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
					// Hizmeti çıkar
					state.selectedServices = state.selectedServices.filter(function(s) {
						return s.id != serviceId;
					});
				}

				// İlk seçilen hizmeti primary olarak kaydet (uyumluluk için)
				state.selectedService = state.selectedServices.length > 0 ? state.selectedServices[0] : null;

				// Reset date and time
				state.selectedDate = null;
				state.selectedTime = null;

				// In service_first mode, reset staff too
				if (flowConfig.mode === 'service_first' || flowConfig.mode === 'service_only') {
					state.selectedStaff = null;
				}

				// Özet güncelle
				updateSelectedServicesSummary();
				updateNextButton();
			});
		});
	}

	/**
	 * Update selected services summary
	 */
	function updateSelectedServicesSummary() {
		const summaryEl = document.getElementById('ag-selected-summary');
		if (!summaryEl) return;

		if (state.selectedServices.length === 0) {
			summaryEl.style.display = 'none';
			return;
		}

		summaryEl.style.display = 'block';

		// Toplam süre ve fiyat hesapla
		let totalDuration = 0;
		let totalPrice = 0;

		state.selectedServices.forEach(function(service) {
			totalDuration += parseInt(service.duration) || 0;
			totalPrice += parseFloat(service.price) || 0;
		});

		// Güncelle
		const durationEl = document.getElementById('ag-total-duration');
		const priceEl = document.getElementById('ag-total-price');

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
	 * Get total price for selected services
	 */
	function getTotalPrice() {
		if (flowConfig.multiService && state.selectedServices.length > 0) {
			return state.selectedServices.reduce(function(total, service) {
				return total + (parseFloat(service.price) || 0);
			}, 0);
		}
		return state.selectedService ? (parseFloat(state.selectedService.price) || 0) : 0;
	}

	/**
	 * Load all staff (for staff_first and staff_only modes)
	 */
	function loadAllStaff() {
		const staffList = document.getElementById('ag-staff-list');
		if (!staffList) return;

		staffList.innerHTML = '<div class="ag-loading-spinner">' + agPublic.strings.loading + '</div>';

		ajaxRequest('ag_get_all_staff', {}, function(response) {
			if (response.success && response.data.length > 0) {
				state.staffList = response.data;
				renderStaffList(response.data, true);
			} else {
				staffList.innerHTML = '<p class="ag-time-hint">' + agPublic.strings.select_staff + '</p>';
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
		const currentStepEl = document.querySelector('.ag-step[data-step="' + state.currentStep + '"]');
		if (currentStepEl) {
			currentStepEl.style.display = 'none';
		}

		// Show new step
		const newStepEl = document.querySelector('.ag-step[data-step="' + step + '"]');
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

		ajaxRequest('ag_get_staff_for_service', {
			service_id: state.selectedService.id
		}, function(response) {
			if (response.success && response.data.length > 0) {
				// Auto-select first available staff
				state.selectedStaff = response.data[0];
				renderCalendar();
			} else {
				// No staff available
				const slotsContainer = document.getElementById('ag-time-slots');
				if (slotsContainer) {
					slotsContainer.innerHTML = '<p class="ag-time-hint">' + agPublic.strings.no_slots + '</p>';
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
		const steps = document.querySelectorAll('.ag-progress-step');

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
		const staffList = document.getElementById('ag-staff-list');
		if (!staffList) return;

		staffList.innerHTML = '<div class="ag-loading-spinner">' + agPublic.strings.loading + '</div>';

		ajaxRequest('ag_get_staff_for_service', {
			service_id: state.selectedService.id
		}, function(response) {
			if (response.success && response.data.length > 0) {
				state.staffList = response.data;
				renderStaffList(response.data, false);
			} else {
				staffList.innerHTML = '<p class="ag-time-hint">' + agPublic.strings.select_staff + '</p>';
			}
		});
	}

	/**
	 * Load services for selected staff
	 */
	function loadServicesForStaff() {
		const serviceList = document.querySelector('.ag-service-list');
		if (!serviceList) return;

		serviceList.innerHTML = '<div class="ag-loading-spinner">' + agPublic.strings.loading + '</div>';

		ajaxRequest('ag_get_services_for_staff', {
			staff_id: state.selectedStaff.id
		}, function(response) {
			if (response.success && response.data.length > 0) {
				state.servicesList = response.data;
				renderServiceList(response.data);
			} else {
				serviceList.innerHTML = '<p class="ag-time-hint">' + agPublic.strings.no_services + '</p>';
			}
		});
	}

	/**
	 * Render staff list
	 */
	function renderStaffList(staffData, isFirstStep) {
		const staffList = document.getElementById('ag-staff-list');
		if (!staffList) return;

		let html = '';

		staffData.forEach(function(staff) {
			const avatar = staff.avatar_url
				? '<img src="' + staff.avatar_url + '" alt="' + staff.name + '">'
				: '<span class="ag-staff-avatar-placeholder">' + staff.name.charAt(0) + '</span>';

			html += '<label class="ag-staff-item">' +
				'<input type="radio" name="staff_id" value="' + staff.id + '">' +
				'<div class="ag-staff-card">' +
					'<div class="ag-staff-avatar">' + avatar + '</div>' +
					'<h4 class="ag-staff-name">' + staff.name + '</h4>' +
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
		const serviceList = document.querySelector('.ag-service-list');
		if (!serviceList) return;

		const currency = agPublic.currency;
		let html = '';

		servicesData.forEach(function(service) {
			const price = currency.position === 'before'
				? currency.symbol + service.price
				: service.price + ' ' + currency.symbol;

			html += '<label class="ag-service-item" data-service-id="' + service.id + '">' +
				'<input type="radio" name="service_id" value="' + service.id + '" required>' +
				'<div class="ag-service-card">' +
					'<span class="ag-service-color" style="background-color: ' + (service.color || '#3788d8') + '"></span>' +
					'<div class="ag-service-info">' +
						'<strong class="ag-service-name">' + service.name + '</strong>' +
						(service.description ? '<p class="ag-service-desc">' + service.description + '</p>' : '') +
						'<div class="ag-service-meta">' +
							'<span class="ag-service-duration">' +
								'<svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>' +
								service.duration + ' dk' +
							'</span>' +
							'<span class="ag-service-price">' + price + '</span>' +
						'</div>' +
					'</div>' +
					'<span class="ag-service-check">' +
						'<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>' +
					'</span>' +
				'</div>' +
			'</label>';
		});

		serviceList.innerHTML = html;

		// Add event listeners
		serviceList.querySelectorAll('input[type="radio"]').forEach(function(input) {
			input.addEventListener('change', function() {
				const serviceId = this.value;
				state.selectedService = servicesData.find(function(s) {
					return s.id == serviceId;
				});

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
		const calPrevBtn = document.getElementById('ag-prev-month');
		const calNextBtn = document.getElementById('ag-next-month');

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
		const titleEl = document.getElementById('ag-calendar-title');
		const bodyEl = document.getElementById('ag-calendar-body');

		if (!titleEl || !bodyEl) return;

		const year = state.currentMonth.getFullYear();
		const month = state.currentMonth.getMonth();

		// Update title
		const monthNames = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
			'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
		titleEl.textContent = monthNames[month] + ' ' + year;

		// Build calendar
		const firstDay = new Date(year, month, 1);
		const lastDay = new Date(year, month + 1, 0);
		const startDay = firstDay.getDay() || 7; // Monday = 1

		let html = '<div class="ag-calendar-grid">';

		// Day headers
		const dayNames = ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'];
		dayNames.forEach(function(day) {
			html += '<div class="ag-calendar-day-header">' + day + '</div>';
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
			html += '<div class="ag-calendar-day other-month">' + day.getDate() + '</div>';
		}

		// Current month days
		for (let day = 1; day <= lastDay.getDate(); day++) {
			const date = new Date(year, month, day);
			const dateStr = formatDate(date);

			let className = 'ag-calendar-day';

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
			html += '<div class="ag-calendar-day other-month">' + i + '</div>';
		}

		html += '</div>';
		bodyEl.innerHTML = html;

		// Add click events
		bodyEl.querySelectorAll('.ag-calendar-day:not(.disabled):not(.other-month)').forEach(function(dayEl) {
			dayEl.addEventListener('click', function() {
				// Remove previous selection
				bodyEl.querySelectorAll('.ag-calendar-day').forEach(function(d) {
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
		const slotsContainer = document.getElementById('ag-time-slots');
		if (!slotsContainer) return;

		slotsContainer.innerHTML = '<div class="ag-loading-spinner">' + agPublic.strings.loading + '</div>';

		// For staff_only mode, use first staff if none selected
		const staffId = state.selectedStaff ? state.selectedStaff.id : null;
		const serviceId = state.selectedService ? state.selectedService.id : null;

		if (!staffId || !serviceId) {
			slotsContainer.innerHTML = '<p class="ag-time-hint">' + agPublic.strings.no_slots + '</p>';
			return;
		}

		// Çoklu hizmet modunda toplam süreyi gönder
		const requestData = {
			staff_id: staffId,
			service_id: serviceId,
			date: state.selectedDate
		};

		if (flowConfig.multiService && state.selectedServices.length > 0) {
			requestData.total_duration = getTotalDuration();
		}

		ajaxRequest('ag_get_available_slots', requestData, function(response) {
			if (response.success && response.data.length > 0) {
				state.availableSlots = response.data;
				renderTimeSlots(response.data);
			} else {
				slotsContainer.innerHTML = '<p class="ag-time-hint">' + agPublic.strings.no_slots + '</p>';
			}
		});
	}

	/**
	 * Render time slots
	 */
	function renderTimeSlots(slots) {
		const container = document.getElementById('ag-time-slots');
		if (!container) return;

		let html = '<div class="ag-time-grid">';

		slots.forEach(function(slot) {
			const className = slot.available ? 'ag-time-slot' : 'ag-time-slot disabled';
			html += '<div class="' + className + '" data-time="' + slot.start + '">' + slot.start + '</div>';
		});

		html += '</div>';
		container.innerHTML = html;

		// Add click events
		container.querySelectorAll('.ag-time-slot:not(.disabled)').forEach(function(slotEl) {
			slotEl.addEventListener('click', function() {
				// Remove previous selection
				container.querySelectorAll('.ag-time-slot').forEach(function(s) {
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
	 * Render booking summary
	 */
	function renderSummary() {
		const summaryEl = document.getElementById('ag-booking-summary');
		if (!summaryEl) return;

		const currency = agPublic.currency;
		const formattedDate = formatDisplayDate(state.selectedDate);

		let html = '';

		// Çoklu hizmet modunda hizmetleri listele
		if (flowConfig.multiService && state.selectedServices.length > 0) {
			html += '<div class="ag-summary-row">' +
				'<span class="ag-summary-label">Hizmetler</span>' +
				'<span class="ag-summary-value">' +
				state.selectedServices.map(function(s) { return s.name; }).join(', ') +
				'</span>' +
			'</div>';
		} else if (state.selectedService) {
			html += '<div class="ag-summary-row">' +
				'<span class="ag-summary-label">Hizmet</span>' +
				'<span class="ag-summary-value">' + state.selectedService.name + '</span>' +
			'</div>';
		}

		// Only show staff if there's a staff step or service_only mode
		if (state.selectedStaff) {
			html += '<div class="ag-summary-row">' +
				'<span class="ag-summary-label">Personel</span>' +
				'<span class="ag-summary-value">' + state.selectedStaff.name + '</span>' +
			'</div>';
		}

		// Toplam süre ve fiyat
		const totalDuration = getTotalDuration();
		const totalPrice = getTotalPrice();
		const priceStr = currency.position === 'before'
			? currency.symbol + formatNumber(totalPrice)
			: formatNumber(totalPrice) + ' ' + currency.symbol;

		html += '<div class="ag-summary-row">' +
			'<span class="ag-summary-label">Tarih</span>' +
			'<span class="ag-summary-value">' + formattedDate + '</span>' +
		'</div>' +
		'<div class="ag-summary-row">' +
			'<span class="ag-summary-label">Saat</span>' +
			'<span class="ag-summary-value">' + state.selectedTime + '</span>' +
		'</div>' +
		'<div class="ag-summary-row">' +
			'<span class="ag-summary-label">Süre</span>' +
			'<span class="ag-summary-value">' + totalDuration + ' dk</span>' +
		'</div>' +
		'<div class="ag-summary-row total">' +
			'<span class="ag-summary-label">Toplam</span>' +
			'<span class="ag-summary-value">' + priceStr + '</span>' +
		'</div>';

		summaryEl.innerHTML = html;
	}

	/**
	 * Form submit
	 */
	function initFormSubmit() {
		form.addEventListener('submit', function(e) {
			e.preventDefault();

			// Validate required fields
			const name = document.getElementById('customer-name').value;
			const email = document.getElementById('customer-email').value;

			if (!name || !email) {
				alert(agPublic.strings.required_fields);
				return;
			}

			// Disable submit button
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<span class="ag-loading-spinner"></span> ' + agPublic.strings.loading;

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

			// Çoklu hizmet modunda ek verileri ekle
			if (flowConfig.multiService && state.selectedServices.length > 0) {
				data.multi_service = true;
				data.total_duration = getTotalDuration();
				data.total_price = getTotalPrice();
				// Service IDs as JSON array
				data.service_ids = JSON.stringify(state.selectedServices.map(function(s) { return s.id; }));
			}

			// Submit booking
			ajaxRequest('ag_create_booking', data, function(response) {
				if (response.success) {
					// Show success step
					goToStep(state.totalSteps + 1);
					renderSuccessDetails(response.data.booking);
				} else {
					alert(response.data || agPublic.strings.booking_error);
					submitBtn.disabled = false;
					submitBtn.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg> Randevu Al';
				}
			});
		});
	}

	/**
	 * Render success details
	 */
	function renderSuccessDetails(booking) {
		const detailsEl = document.getElementById('ag-success-details');
		if (!detailsEl) return;

		const currency = agPublic.currency;

		const price = currency.position === 'before'
			? currency.symbol + booking.price
			: booking.price + ' ' + currency.symbol;

		const formattedDate = formatDisplayDate(booking.booking_date);

		let html = '<div class="ag-summary-row">' +
			'<span class="ag-summary-label">Hizmet</span>' +
			'<span class="ag-summary-value">' + booking.service_name + '</span>' +
		'</div>' +
		'<div class="ag-summary-row">' +
			'<span class="ag-summary-label">Personel</span>' +
			'<span class="ag-summary-value">' + booking.staff_name + '</span>' +
		'</div>' +
		'<div class="ag-summary-row">' +
			'<span class="ag-summary-label">Tarih</span>' +
			'<span class="ag-summary-value">' + formattedDate + '</span>' +
		'</div>' +
		'<div class="ag-summary-row">' +
			'<span class="ag-summary-label">Saat</span>' +
			'<span class="ag-summary-value">' + booking.start_time.substring(0, 5) + '</span>' +
		'</div>' +
		'<div class="ag-summary-row total">' +
			'<span class="ag-summary-label">Toplam</span>' +
			'<span class="ag-summary-value">' + price + '</span>' +
		'</div>';

		detailsEl.innerHTML = html;
	}

	/**
	 * AJAX Request helper
	 */
	function ajaxRequest(action, data, callback) {
		data = data || {};
		data.action = action;
		data.nonce = agPublic.nonce;

		const formData = new FormData();
		for (const key in data) {
			formData.append(key, data[key]);
		}

		fetch(agPublic.ajaxUrl, {
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
				callback({ success: false, data: agPublic.strings.booking_error });
			}
		});
	}

	/**
	 * Get service by ID
	 */
	function getServiceById(id) {
		if (typeof agServicesData !== 'undefined') {
			return agServicesData.find(function(s) {
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
		const monthNames = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
			'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
		const month = monthNames[date.getMonth()];
		const year = date.getFullYear();

		return day + ' ' + month + ' ' + year;
	}

})();
