/**
 * Unbelievable Salon Booking - Admin JavaScript
 *
 * @package Unbelievable_Salon_Booking
 */

(function() {
	'use strict';

	// DOM Ready
	document.addEventListener('DOMContentLoaded', function() {
		initModals();
		initCategories();
		initServices();
		initStaff();
		initBookings();
		initSettings();
		initCalendar();
		initCopyButtons();
		initEmailTemplates();
		initStaffSchedule();
		initPromoCodes();
		initExportImport();
		initNewBookingPage();
		initCompleteBooking();
		initStaffBookings();
		initStaffScheduleOwn();
	});

	/**
	 * Toast notification
	 */
	function showToast(message, type) {
		type = type || 'success';

		const toast = document.createElement('div');
		toast.className = 'unbsb-toast unbsb-toast-' + type;
		toast.textContent = message;
		document.body.appendChild(toast);

		setTimeout(function() {
			toast.remove();
		}, 3000);
	}

	/**
	 * AJAX Request helper
	 */
	function ajaxRequest(action, data, callback) {
		data = data || {};
		data.action = action;
		data.nonce = unbsbAdmin.nonce;

		const formData = new FormData();
		for (const key in data) {
			if (Array.isArray(data[key])) {
				data[key].forEach(function(val) {
					formData.append(key + '[]', val);
				});
			} else {
				formData.append(key, data[key]);
			}
		}

		fetch(unbsbAdmin.ajaxUrl, {
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
			showToast(unbsbAdmin.strings.error, 'error');
		});
	}

	/**
	 * Modal functionality
	 */
	function initModals() {
		// Close modal on overlay click
		document.querySelectorAll('.unbsb-modal-overlay').forEach(function(overlay) {
			overlay.addEventListener('click', function() {
				closeModal(this.closest('.unbsb-modal'));
			});
		});

		// Close modal on close button click
		document.querySelectorAll('.unbsb-modal-close').forEach(function(btn) {
			btn.addEventListener('click', function() {
				closeModal(this.closest('.unbsb-modal'));
			});
		});

		// Close modal on Escape key
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				const modal = document.querySelector('.unbsb-modal[style*="block"]');
				if (modal) {
					closeModal(modal);
				}
			}
		});
	}

	function openModal(modalId) {
		const modal = document.getElementById(modalId);
		if (modal) {
			modal.style.display = 'flex';
			document.body.style.overflow = 'hidden';
		}
	}

	function closeModal(modal) {
		if (modal) {
			modal.style.display = 'none';
			document.body.style.overflow = '';
		}
	}

	/**
	 * Categories functionality
	 */
	function initCategories() {
		const addBtn = document.getElementById('unbsb-add-category');
		const addBtnEmpty = document.getElementById('unbsb-add-category-empty');
		const saveBtn = document.getElementById('unbsb-save-category');
		const modal = document.getElementById('unbsb-category-modal');
		const form = document.getElementById('unbsb-category-form');

		if (!modal) return;

		// Color picker label update
		const categoryColor = document.getElementById('category-color');
		const categoryColorLabel = document.getElementById('category-color-label');
		if (categoryColor && categoryColorLabel) {
			categoryColor.addEventListener('input', function() {
				categoryColorLabel.textContent = this.value.toUpperCase();
			});
		}

		// Color presets
		document.querySelectorAll('.unbsb-color-preset').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const color = this.dataset.color;
				if (categoryColor) {
					categoryColor.value = color;
					if (categoryColorLabel) {
						categoryColorLabel.textContent = color.toUpperCase();
					}
				}
			});
		});

		// Add category button
		[addBtn, addBtnEmpty].forEach(function(btn) {
			if (btn) {
				btn.addEventListener('click', function() {
					resetCategoryForm();
					document.getElementById('unbsb-category-modal-title').textContent = unbsbAdmin.strings.new_category;
					openModal('unbsb-category-modal');
				});
			}
		});

		// Edit category buttons
		document.querySelectorAll('.unbsb-edit-category').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const id = this.dataset.id;
				const category = getCategoryById(id);
				if (category) {
					fillCategoryForm(category);
					document.getElementById('unbsb-category-modal-title').textContent = unbsbAdmin.strings.edit_category;
					openModal('unbsb-category-modal');
				}
			});
		});

		// Delete category buttons
		document.querySelectorAll('.unbsb-delete-category').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const serviceCount = parseInt(this.dataset.serviceCount) || 0;
				let confirmMsg = unbsbAdmin.strings.confirm_delete;

				if (serviceCount > 0) {
					confirmMsg = unbsbAdmin.strings.category_has_services.replace('%d', serviceCount);
				}

				if (confirm(confirmMsg)) {
					const id = this.dataset.id;
					ajaxRequest('unbsb_delete_category', { id: id }, function(response) {
						if (response.success) {
							showToast(response.data);
							location.reload();
						} else {
							showToast(response.data, 'error');
						}
					});
				}
			});
		});

		// Save category
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				const formData = new FormData(form);
				const data = {};
				formData.forEach(function(value, key) {
					data[key] = value;
				});

				ajaxRequest('unbsb_save_category', data, function(response) {
					if (response.success) {
						showToast(response.data.message);
						closeModal(modal);
						location.reload();
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}
	}

	function resetCategoryForm() {
		const form = document.getElementById('unbsb-category-form');
		if (form) {
			form.reset();
			document.getElementById('category-id').value = '';
			document.getElementById('category-color').value = '#3788d8';
			const colorLabel = document.getElementById('category-color-label');
			if (colorLabel) {
				colorLabel.textContent = '#3788D8';
			}
			// Reset status radio buttons
			const activeRadio = form.querySelector('input[name="status"][value="active"]');
			if (activeRadio) {
				activeRadio.checked = true;
			}
		}
	}

	function fillCategoryForm(category) {
		document.getElementById('category-id').value = category.id;
		document.getElementById('category-name').value = category.name;
		document.getElementById('category-description').value = category.description || '';
		const color = category.color || '#3788d8';
		document.getElementById('category-color').value = color;
		document.getElementById('category-sort-order').value = category.sort_order || 0;

		// Update color label
		const colorLabel = document.getElementById('category-color-label');
		if (colorLabel) {
			colorLabel.textContent = color.toUpperCase();
		}

		// Set status radio button
		const statusRadio = document.querySelector('input[name="status"][value="' + category.status + '"]');
		if (statusRadio) {
			statusRadio.checked = true;
		}
	}

	function getCategoryById(id) {
		if (typeof unbsbCategories !== 'undefined') {
			return unbsbCategories.find(function(c) {
				return c.id == id;
			});
		}
		return null;
	}

	/**
	 * Services functionality
	 */
	function initServices() {
		const addBtn = document.getElementById('unbsb-add-service');
		const addBtnEmpty = document.getElementById('unbsb-add-service-empty');
		const saveBtn = document.getElementById('unbsb-save-service');
		const modal = document.getElementById('unbsb-service-modal');
		const form = document.getElementById('unbsb-service-form');

		if (!modal) return;

		// Add service button
		[addBtn, addBtnEmpty].forEach(function(btn) {
			if (btn) {
				btn.addEventListener('click', function() {
					resetServiceForm();
					document.getElementById('unbsb-service-modal-title').textContent = unbsbAdmin.strings.new_service;
					openModal('unbsb-service-modal');
				});
			}
		});

		// Edit service buttons
		document.querySelectorAll('.unbsb-edit-service').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const id = this.dataset.id;
				const service = getServiceById(id);
				if (service) {
					fillServiceForm(service);
					document.getElementById('unbsb-service-modal-title').textContent = unbsbAdmin.strings.edit_service;
					openModal('unbsb-service-modal');
				}
			});
		});

		// Delete service buttons
		document.querySelectorAll('.unbsb-delete-service').forEach(function(btn) {
			btn.addEventListener('click', function() {
				if (confirm(unbsbAdmin.strings.confirm_delete)) {
					const id = this.dataset.id;
					ajaxRequest('unbsb_delete_service', { id: id }, function(response) {
						if (response.success) {
							showToast(response.data);
							location.reload();
						} else {
							showToast(response.data, 'error');
						}
					});
				}
			});
		});

		// Save service
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				const formData = new FormData(form);
				const data = {};
				formData.forEach(function(value, key) {
					data[key] = value;
				});

				ajaxRequest('unbsb_save_service', data, function(response) {
					if (response.success) {
						showToast(response.data.message);
						closeModal(modal);
						location.reload();
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Color picker label update
		const colorInput = document.getElementById('service-color');
		if (colorInput) {
			colorInput.addEventListener('input', function() {
				updateColorLabel(this.value);
			});
		}

		// Inline category add
		initInlineCategoryAdd();
	}

	/**
	 * Inline Category Add functionality
	 */
	function initInlineCategoryAdd() {
		const addBtn = document.getElementById('unbsb-add-category-inline');
		const inlineForm = document.getElementById('unbsb-new-category-form');
		const saveBtn = document.getElementById('unbsb-save-category-inline');
		const cancelBtn = document.getElementById('unbsb-cancel-category-inline');
		const nameInput = document.getElementById('new-category-name');
		const colorInput = document.getElementById('new-category-color');
		const categorySelect = document.getElementById('service-category');

		if (!addBtn || !inlineForm) return;

		// Show inline form
		addBtn.addEventListener('click', function() {
			inlineForm.style.display = 'block';
			nameInput.value = '';
			colorInput.value = '#3788d8';
			nameInput.focus();
		});

		// Cancel
		cancelBtn.addEventListener('click', function() {
			inlineForm.style.display = 'none';
		});

		// Save new category
		saveBtn.addEventListener('click', function() {
			const name = nameInput.value.trim();
			const color = colorInput.value;

			if (!name) {
				showToast(unbsbAdmin.strings.category_name_required, 'error');
				nameInput.focus();
				return;
			}

			saveBtn.disabled = true;

			ajaxRequest('unbsb_save_category', {
				name: name,
				color: color,
				status: 'active'
			}, function(response) {
				saveBtn.disabled = false;

				if (response.success) {
					// Add new option to select
					const newOption = document.createElement('option');
					newOption.value = response.data.id;
					newOption.textContent = name;
					categorySelect.appendChild(newOption);

					// Select the new category
					categorySelect.value = response.data.id;

					// Hide form
					inlineForm.style.display = 'none';

					// Update global categories array
					if (typeof unbsbCategories !== 'undefined') {
						unbsbCategories.push({
							id: response.data.id,
							name: name,
							color: color,
							status: 'active'
						});
					}

					showToast(response.data.message || unbsbAdmin.strings.category_added);
				} else {
					showToast(response.data, 'error');
				}
			});
		});

		// Enter key to save
		nameInput.addEventListener('keydown', function(e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				saveBtn.click();
			}
			if (e.key === 'Escape') {
				cancelBtn.click();
			}
		});
	}

	function resetServiceForm() {
		const form = document.getElementById('unbsb-service-form');
		if (form) {
			form.reset();
			document.getElementById('service-id').value = '';
			document.getElementById('service-discounted-price').value = '';
			document.getElementById('service-color').value = '#3788d8';

			const categorySelect = document.getElementById('service-category');
			if (categorySelect) {
				categorySelect.value = '';
			}

			// Hide inline category form
			const inlineForm = document.getElementById('unbsb-new-category-form');
			if (inlineForm) {
				inlineForm.style.display = 'none';
			}

			// Update color label
			updateColorLabel('#3788d8');

			// Reset status radio
			const activeRadio = document.querySelector('input[name="status"][value="active"]');
			if (activeRadio) {
				activeRadio.checked = true;
			}
		}
	}

	function updateColorLabel(color) {
		const label = document.getElementById('service-color-label');
		if (label) {
			label.textContent = color;
		}
	}

	function fillServiceForm(service) {
		document.getElementById('service-id').value = service.id;
		document.getElementById('service-name').value = service.name;
		document.getElementById('service-description').value = service.description || '';
		document.getElementById('service-duration').value = service.duration;
		document.getElementById('service-price').value = service.price;
		document.getElementById('service-discounted-price').value = service.discounted_price || '';
		document.getElementById('service-buffer-before').value = service.buffer_before || 0;
		document.getElementById('service-buffer-after').value = service.buffer_after || 0;

		const color = service.color || '#3788d8';
		document.getElementById('service-color').value = color;
		updateColorLabel(color);

		// Status radio button
		const statusRadio = document.querySelector('input[name="status"][value="' + service.status + '"]');
		if (statusRadio) {
			statusRadio.checked = true;
		}

		// Kategori
		const categorySelect = document.getElementById('service-category');
		if (categorySelect) {
			categorySelect.value = service.category_id || '';
		}
	}

	function getServiceById(id) {
		if (typeof unbsbServices !== 'undefined') {
			return unbsbServices.find(function(s) {
				return s.id == id;
			});
		}
		return null;
	}

	/**
	 * Staff functionality
	 */
	function initStaff() {
		const addBtn = document.getElementById('unbsb-add-staff');
		const addBtnEmpty = document.getElementById('unbsb-add-staff-empty');
		const saveBtn = document.getElementById('unbsb-save-staff');
		const saveHoursBtn = document.getElementById('unbsb-save-hours');
		const modal = document.getElementById('unbsb-staff-modal');
		const hoursModal = document.getElementById('unbsb-hours-modal');
		const form = document.getElementById('unbsb-staff-form');

		if (!modal) return;

		// Add staff button
		[addBtn, addBtnEmpty].forEach(function(btn) {
			if (btn) {
				btn.addEventListener('click', function() {
					resetStaffForm();
					document.getElementById('unbsb-staff-modal-title').textContent = unbsbAdmin.strings.new_staff;
					openModal('unbsb-staff-modal');
				});
			}
		});

		// Edit staff buttons
		document.querySelectorAll('.unbsb-edit-staff').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const id = this.dataset.id;
				const staff = getStaffById(id);
				if (staff) {
					fillStaffForm(staff);
					document.getElementById('unbsb-staff-modal-title').textContent = unbsbAdmin.strings.edit_staff;
					openModal('unbsb-staff-modal');
				}
			});
		});

		// Edit hours buttons
		document.querySelectorAll('.unbsb-edit-hours').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const id = this.dataset.id;
				const name = this.dataset.name;
				document.getElementById('hours-staff-id').value = id;
				const staffNameEl = document.getElementById('unbsb-hours-staff-name');
				if (staffNameEl) {
					staffNameEl.textContent = name;
				}
				openModal('unbsb-hours-modal');
			});
		});

		// Working hours quick actions
		const setWeekdaysBtn = document.getElementById('unbsb-hours-set-weekdays');
		const setAllBtn = document.getElementById('unbsb-hours-set-all');
		const clearAllBtn = document.getElementById('unbsb-hours-clear-all');

		if (setWeekdaysBtn) {
			setWeekdaysBtn.addEventListener('click', function() {
				document.querySelectorAll('.unbsb-hours-working-toggle').forEach(function(checkbox) {
					const dayNum = checkbox.name.match(/\[(\d+)\]/)[1];
					// Weekdays (1-5) are checked, weekend (0, 6) unchecked
					checkbox.checked = (dayNum >= 1 && dayNum <= 5);
				});
				document.querySelectorAll('.unbsb-hours-time-input').forEach(function(input) {
					if (input.name.includes('[start_time]')) {
						input.value = '09:00';
					} else if (input.name.includes('[end_time]')) {
						input.value = '18:00';
					}
				});
			});
		}

		if (setAllBtn) {
			setAllBtn.addEventListener('click', function() {
				document.querySelectorAll('.unbsb-hours-working-toggle').forEach(function(checkbox) {
					checkbox.checked = true;
				});
			});
		}

		if (clearAllBtn) {
			clearAllBtn.addEventListener('click', function() {
				document.querySelectorAll('.unbsb-hours-working-toggle').forEach(function(checkbox) {
					checkbox.checked = false;
				});
			});
		}

		// Category collapse/expand
		document.querySelectorAll('.unbsb-service-category-header').forEach(function(header) {
			header.addEventListener('click', function(e) {
				// Don't toggle when clicking the checkbox itself
				if (e.target.classList.contains('unbsb-category-toggle-all')) {
					return;
				}
				var group = header.closest('.unbsb-service-category-group');
				group.classList.toggle('unbsb-category-collapsed');
			});
		});

		// Category toggle-all checkboxes
		document.querySelectorAll('.unbsb-category-toggle-all').forEach(function(toggleAll) {
			toggleAll.addEventListener('change', function() {
				var group = this.closest('.unbsb-service-category-group');
				var checkboxes = group.querySelectorAll('input[name="services[]"]');
				var checked = this.checked;
				checkboxes.forEach(function(cb) {
					cb.checked = checked;
				});
				updateCategoryCount(group);
			});
		});

		// Individual service checkbox change -> update category count + toggle custom fields
		document.querySelectorAll('.unbsb-service-category-group input[name="services[]"]').forEach(function(cb) {
			cb.addEventListener('change', function() {
				var group = this.closest('.unbsb-service-category-group');
				updateCategoryCount(group);
				toggleServiceCustomFields(this);
			});
		});

		// Delete staff buttons
		document.querySelectorAll('.unbsb-delete-staff').forEach(function(btn) {
			btn.addEventListener('click', function() {
				if (confirm(unbsbAdmin.strings.confirm_delete)) {
					const id = this.dataset.id;
					ajaxRequest('unbsb_delete_staff', { id: id }, function(response) {
						if (response.success) {
							showToast(response.data);
							location.reload();
						} else {
							showToast(response.data, 'error');
						}
					});
				}
			});
		});

		// Salary type toggle
		document.querySelectorAll('#unbsb-staff-form input[name="salary_type"]').forEach(function(radio) {
			radio.addEventListener('change', updateSalaryFields);
		});

		// WordPress Account handlers
		initWpAccountHandlers();

		// Save staff
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				const formData = new FormData(form);
				const data = {};
				const services = [];
				var servicePrices = {};
				var serviceDurations = {};

				formData.forEach(function(value, key) {
					if (key === 'services[]') {
						services.push(value);
					} else {
						// Capture service_prices[ID] and service_durations[ID]
						var priceMatch = key.match(/^service_prices\[(\d+)\]$/);
						var durationMatch = key.match(/^service_durations\[(\d+)\]$/);
						if (priceMatch && value !== '') {
							servicePrices[priceMatch[1]] = value;
						} else if (durationMatch && value !== '') {
							serviceDurations[durationMatch[1]] = value;
						} else {
							data[key] = value;
						}
					}
				});

				data.services = services;
				data.service_prices = servicePrices;
				data.service_durations = serviceDurations;

				ajaxRequest('unbsb_save_staff', data, function(response) {
					if (response.success) {
						showToast(response.data.message);
						closeModal(modal);
						location.reload();
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Save working hours
		if (saveHoursBtn) {
			saveHoursBtn.addEventListener('click', function() {
				const staffId = document.getElementById('hours-staff-id').value;
				const hoursForm = document.getElementById('unbsb-hours-form');
				const formData = new FormData(hoursForm);
				const hours = [];

				// Parse form data into hours array
				for (let i = 0; i <= 6; i++) {
					const isWorking = formData.get('hours[' + i + '][is_working]');
					hours.push({
						day_of_week: i,
						is_working: isWorking ? 1 : 0,
						start_time: formData.get('hours[' + i + '][start_time]'),
						end_time: formData.get('hours[' + i + '][end_time]')
					});
				}

				ajaxRequest('unbsb_save_working_hours', {
					staff_id: staffId,
					hours: JSON.stringify(hours)
				}, function(response) {
					if (response.success) {
						showToast(response.data);
						closeModal(hoursModal);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}
	}

	function toggleServiceCustomFields(checkbox) {
		var wrap = checkbox.closest('.unbsb-service-checkbox-wrap');
		if (!wrap) return;
		var fields = wrap.querySelector('.unbsb-service-custom-fields');
		if (!fields) return;
		fields.style.display = checkbox.checked ? 'flex' : 'none';
		if (!checkbox.checked) {
			// Clear custom values when unchecked.
			fields.querySelectorAll('input[type="number"]').forEach(function(input) {
				input.value = '';
			});
		}
	}

	function toggleAllServiceCustomFields() {
		document.querySelectorAll('.unbsb-service-checkbox-wrap input[name="services[]"]').forEach(function(cb) {
			toggleServiceCustomFields(cb);
		});
	}

	function updateCategoryCount(group) {
		var checkboxes = group.querySelectorAll('input[name="services[]"]');
		var checked = group.querySelectorAll('input[name="services[]"]:checked');
		var countEl = group.querySelector('.unbsb-category-count');
		var toggleAll = group.querySelector('.unbsb-category-toggle-all');

		if (countEl) {
			countEl.textContent = checked.length + '/' + checkboxes.length;
		}
		if (toggleAll) {
			toggleAll.checked = checkboxes.length > 0 && checked.length === checkboxes.length;
			toggleAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
		}
	}

	function updateAllCategoryCounts() {
		document.querySelectorAll('.unbsb-service-category-group').forEach(function(group) {
			updateCategoryCount(group);
		});
	}

	function resetStaffForm() {
		const form = document.getElementById('unbsb-staff-form');
		if (form) {
			form.reset();
			document.getElementById('staff-id').value = '';
			// Reset status radio buttons
			const activeRadio = form.querySelector('input[name="status"][value="active"]');
			if (activeRadio) {
				activeRadio.checked = true;
			}
			// Reset salary type
			const percentageRadio = form.querySelector('input[name="salary_type"][value="percentage"]');
			if (percentageRadio) {
				percentageRadio.checked = true;
			}
			var salaryPercentageInput = document.getElementById('staff-salary-percentage');
			var salaryFixedInput = document.getElementById('staff-salary-fixed');
			if (salaryPercentageInput) salaryPercentageInput.value = '';
			if (salaryFixedInput) salaryFixedInput.value = '';
			updateSalaryFields();
			// Reset WP account section.
			resetWpAccountSection();
			// Uncheck all services and hide custom fields
			form.querySelectorAll('input[name="services[]"]').forEach(function(checkbox) {
				checkbox.checked = false;
			});
			toggleAllServiceCustomFields();
			// Update category counts
			updateAllCategoryCounts();
		}
	}

	function fillStaffForm(staff) {
		document.getElementById('staff-id').value = staff.id;
		document.getElementById('staff-name').value = staff.name;
		document.getElementById('staff-email').value = staff.email || '';
		document.getElementById('staff-phone').value = staff.phone || '';
		document.getElementById('staff-bio').value = staff.bio || '';

		// Set status radio button
		const statusRadio = document.querySelector('#unbsb-staff-form input[name="status"][value="' + staff.status + '"]');
		if (statusRadio) {
			statusRadio.checked = true;
		}

		// Check services
		document.querySelectorAll('input[name="services[]"]').forEach(function(checkbox) {
			checkbox.checked = staff.services && staff.services.includes(checkbox.value);
		});

		// Fill custom prices/durations from service_details
		if (staff.service_details && Array.isArray(staff.service_details)) {
			staff.service_details.forEach(function(detail) {
				var sid = String(detail.service_id);
				if (detail.custom_price) {
					var priceInput = document.querySelector('input[name="service_prices[' + sid + ']"]');
					if (priceInput) {
						priceInput.value = detail.custom_price;
					}
				}
				if (detail.custom_duration) {
					var durationInput = document.querySelector('input[name="service_durations[' + sid + ']"]');
					if (durationInput) {
						durationInput.value = detail.custom_duration;
					}
				}
			});
		}

		// Salary fields
		var salaryType = staff.salary_type || 'percentage';
		var salaryTypeRadio = document.querySelector('#unbsb-staff-form input[name="salary_type"][value="' + salaryType + '"]');
		if (salaryTypeRadio) {
			salaryTypeRadio.checked = true;
		}
		var salaryPercentageInput = document.getElementById('staff-salary-percentage');
		var salaryFixedInput = document.getElementById('staff-salary-fixed');
		if (salaryPercentageInput) {
			salaryPercentageInput.value = staff.salary_percentage || '';
		}
		if (salaryFixedInput) {
			salaryFixedInput.value = staff.salary_fixed || '';
		}
		updateSalaryFields();

		// WP Account section.
		if (staff.user_id && parseInt(staff.user_id) > 0) {
			showLinkedAccount(staff.wp_user_login || '', staff.wp_user_email || '');
		} else {
			resetWpAccountSection();
		}

		// Show/hide custom fields based on checked state
		toggleAllServiceCustomFields();
		// Update category counts
		updateAllCategoryCounts();
	}

	function getStaffById(id) {
		if (typeof unbsbStaff !== 'undefined') {
			return unbsbStaff.find(function(s) {
				return s.id == id;
			});
		}
		return null;
	}

	function updateSalaryFields() {
		var form = document.getElementById('unbsb-staff-form');
		if (!form) return;

		var salaryType = form.querySelector('input[name="salary_type"]:checked');
		var type = salaryType ? salaryType.value : 'percentage';
		var percentageField = document.getElementById('unbsb-salary-percentage-field');
		var fixedField = document.getElementById('unbsb-salary-fixed-field');

		if (!percentageField || !fixedField) return;

		if ('percentage' === type) {
			percentageField.style.display = '';
			fixedField.style.display = 'none';
		} else if ('fixed' === type) {
			percentageField.style.display = 'none';
			fixedField.style.display = '';
		} else {
			// mix
			percentageField.style.display = '';
			fixedField.style.display = '';
		}
	}

	/**
	 * WP Account section helpers
	 */
	function resetWpAccountSection() {
		var unlinked = document.getElementById('unbsb-wp-account-unlinked');
		var linked = document.getElementById('unbsb-wp-account-linked');
		var search = document.getElementById('unbsb-wp-account-search');
		if (unlinked) unlinked.style.display = '';
		if (linked) linked.style.display = 'none';
		if (search) search.style.display = 'none';
		var searchInput = document.getElementById('unbsb-wp-user-search');
		if (searchInput) searchInput.value = '';
		var results = document.getElementById('unbsb-wp-user-results');
		if (results) {
			results.style.display = 'none';
			results.innerHTML = '';
		}
	}

	function showLinkedAccount(login, email) {
		var unlinked = document.getElementById('unbsb-wp-account-unlinked');
		var linked = document.getElementById('unbsb-wp-account-linked');
		var search = document.getElementById('unbsb-wp-account-search');
		var userEl = document.getElementById('unbsb-wp-account-user');
		if (unlinked) unlinked.style.display = 'none';
		if (search) search.style.display = 'none';
		if (linked) linked.style.display = '';
		if (userEl) {
			userEl.textContent = login + (email ? ' (' + email + ')' : '');
		}
	}

	function initWpAccountHandlers() {
		var createBtn = document.getElementById('unbsb-create-wp-account');
		var linkBtn = document.getElementById('unbsb-link-wp-account-btn');
		var cancelSearchBtn = document.getElementById('unbsb-cancel-link-search');
		var unlinkBtn = document.getElementById('unbsb-unlink-wp-account');
		var searchInput = document.getElementById('unbsb-wp-user-search');
		var searchSection = document.getElementById('unbsb-wp-account-search');
		var resultsEl = document.getElementById('unbsb-wp-user-results');

		if (!createBtn) return;

		// Create Account.
		createBtn.addEventListener('click', function() {
			var staffId = document.getElementById('staff-id').value;
			if (!staffId) {
				showToast(unbsbAdmin.strings.error, 'error');
				return;
			}

			createBtn.disabled = true;
			createBtn.textContent = unbsbAdmin.strings.saving;

			ajaxRequest('unbsb_create_staff_user', { staff_id: staffId }, function(response) {
				createBtn.disabled = false;
				createBtn.textContent = unbsbAdmin.strings.create_account;
				if (response.success) {
					showToast(unbsbAdmin.strings.account_created);
					showLinkedAccount(response.data.user_login || '', '');
					// Update staff data in memory.
					var staff = getStaffById(staffId);
					if (staff) {
						staff.user_id = response.data.user_id;
						staff.wp_user_login = response.data.user_login;
					}
				} else {
					showToast(response.data || unbsbAdmin.strings.error, 'error');
				}
			});
		});

		// Link Existing → show search.
		if (linkBtn) {
			linkBtn.addEventListener('click', function() {
				var unlinked = document.getElementById('unbsb-wp-account-unlinked');
				if (unlinked) unlinked.style.display = 'none';
				if (searchSection) searchSection.style.display = '';
				if (searchInput) searchInput.focus();
			});
		}

		// Cancel search.
		if (cancelSearchBtn) {
			cancelSearchBtn.addEventListener('click', function() {
				if (searchSection) searchSection.style.display = 'none';
				var unlinked = document.getElementById('unbsb-wp-account-unlinked');
				if (unlinked) unlinked.style.display = '';
				if (searchInput) searchInput.value = '';
				if (resultsEl) {
					resultsEl.style.display = 'none';
					resultsEl.innerHTML = '';
				}
			});
		}

		// Search on Enter key.
		if (searchInput) {
			searchInput.addEventListener('keydown', function(e) {
				if ('Enter' === e.key) {
					e.preventDefault();
					performUserSearch();
				}
			});
		}

		// Unlink account.
		if (unlinkBtn) {
			unlinkBtn.addEventListener('click', function() {
				if (!confirm(unbsbAdmin.strings.confirm_unlink)) return;

				var staffId = document.getElementById('staff-id').value;
				unlinkBtn.disabled = true;

				ajaxRequest('unbsb_unlink_staff_user', { staff_id: staffId }, function(response) {
					unlinkBtn.disabled = false;
					if (response.success) {
						showToast(unbsbAdmin.strings.account_unlinked);
						resetWpAccountSection();
						var staff = getStaffById(staffId);
						if (staff) {
							staff.user_id = 0;
							staff.wp_user_login = '';
							staff.wp_user_email = '';
						}
					} else {
						showToast(response.data || unbsbAdmin.strings.error, 'error');
					}
				});
			});
		}

		function performUserSearch() {
			var query = searchInput ? searchInput.value.trim() : '';
			if (!query) return;

			var staffId = document.getElementById('staff-id').value;
			if (!staffId) return;

			if (resultsEl) {
				resultsEl.style.display = '';
				resultsEl.innerHTML = '<div class="unbsb-wp-user-searching">' + unbsbAdmin.strings.searching + '</div>';
			}

			ajaxRequest('unbsb_link_staff_user', { staff_id: staffId, search: query }, function(response) {
				if (response.success) {
					showToast(unbsbAdmin.strings.account_linked);
					showLinkedAccount(response.data.user_login || '', '');
					if (resultsEl) {
						resultsEl.style.display = 'none';
						resultsEl.innerHTML = '';
					}
					var staff = getStaffById(staffId);
					if (staff) {
						staff.user_id = response.data.user_id;
						staff.wp_user_login = response.data.user_login;
					}
				} else {
					if (resultsEl) {
						resultsEl.innerHTML = '<div class="unbsb-wp-user-no-results">' + (response.data || unbsbAdmin.strings.no_users_found) + '</div>';
					}
				}
			});
		}
	}

	/**
	 * Customers functionality
	 */
	function initCustomers() {
		var modal = document.getElementById('unbsb-customer-modal');
		if (!modal) return;

		// Open modal: New Customer
		var addBtn = document.getElementById('unbsb-add-customer');
		var addBtnEmpty = document.getElementById('unbsb-add-customer-empty');

		function openNewCustomer() {
			resetCustomerForm();
			document.getElementById('unbsb-customer-modal-title').textContent = unbsbAdmin.strings.new_customer || 'New Customer';
			openModal('unbsb-customer-modal');
		}

		if (addBtn) addBtn.addEventListener('click', openNewCustomer);
		if (addBtnEmpty) addBtnEmpty.addEventListener('click', openNewCustomer);

		// Open modal: Edit Customer
		document.querySelectorAll('.unbsb-edit-customer').forEach(function(btn) {
			btn.addEventListener('click', function() {
				resetCustomerForm();
				document.getElementById('unbsb-customer-modal-title').textContent = unbsbAdmin.strings.edit_customer || 'Edit Customer';
				document.getElementById('customer-id').value = this.dataset.id;
				document.getElementById('customer-name').value = this.dataset.name || '';
				document.getElementById('customer-email').value = this.dataset.email || '';
				document.getElementById('customer-phone').value = this.dataset.phone || '';
				document.getElementById('customer-notes').value = this.dataset.notes || '';
				openModal('unbsb-customer-modal');
			});
		});

		// Save customer
		var saveBtn = document.getElementById('unbsb-save-customer');
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				var form = document.getElementById('unbsb-customer-form');
				if (!form.checkValidity()) {
					form.reportValidity();
					return;
				}

				var data = {};
				new FormData(form).forEach(function(value, key) {
					data[key] = value;
				});

				ajaxRequest('unbsb_save_customer', data, function(response) {
					if (response.success) {
						showToast(response.data.message);
						closeModal(modal);
						location.reload();
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Delete customer
		document.querySelectorAll('.unbsb-delete-customer').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var id = this.dataset.id;
				if (!confirm(unbsbAdmin.strings.confirm_delete)) return;

				ajaxRequest('unbsb_delete_customer', { id: id }, function(response) {
					if (response.success) {
						showToast(response.data);
						location.reload();
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});
	}

	function resetCustomerForm() {
		var form = document.getElementById('unbsb-customer-form');
		if (form) {
			form.reset();
			document.getElementById('customer-id').value = '';
		}
	}

	initCustomers();

	/**
	 * Bookings functionality
	 */
	function initBookings() {
		// Status change
		document.querySelectorAll('.unbsb-status-select').forEach(function(select) {
			select.addEventListener('change', function() {
				const id = this.dataset.id;
				const status = this.value;

				ajaxRequest('unbsb_update_booking_status', {
					id: id,
					status: status
				}, function(response) {
					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});

		// Delete booking
		document.querySelectorAll('.unbsb-delete-booking').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var id = this.dataset.id;
				if (!confirm(unbsbAdmin.strings.confirm_delete)) return;

				ajaxRequest('unbsb_delete_booking', { id: id }, function(response) {
					if (response.success) {
						var row = document.querySelector('tr[data-id="' + id + '"]');
						if (row) row.remove();
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});

		// Add booking button
		const addBookingBtn = document.getElementById('unbsb-add-booking');
		const addBookingModal = document.getElementById('unbsb-add-booking-modal');
		const saveBookingBtn = document.getElementById('unbsb-save-booking');
		const addBookingForm = document.getElementById('unbsb-add-booking-form');

		if (addBookingBtn && addBookingModal) {
			addBookingBtn.addEventListener('click', function() {
				resetAddBookingForm();
				openModal('unbsb-add-booking-modal');
			});
		}

		// Service selection - show service info
		const bookingServiceSelect = document.getElementById('booking-service');
		const serviceInfoDiv = document.getElementById('booking-service-info');
		const serviceDurationSpan = document.getElementById('booking-service-duration');
		const servicePriceSpan = document.getElementById('booking-service-price');

		if (bookingServiceSelect && serviceInfoDiv) {
			bookingServiceSelect.addEventListener('change', function() {
				const selectedOption = this.options[this.selectedIndex];
				if (selectedOption && selectedOption.value) {
					const duration = selectedOption.dataset.duration || '30';
					const price = selectedOption.dataset.price || '0';
					if (serviceDurationSpan) {
						serviceDurationSpan.textContent = duration;
					}
					if (servicePriceSpan) {
						servicePriceSpan.textContent = parseFloat(price).toFixed(2);
					}
					serviceInfoDiv.style.display = 'block';
				} else {
					serviceInfoDiv.style.display = 'none';
				}
			});
		}

		// Save booking
		if (saveBookingBtn && addBookingForm) {
			saveBookingBtn.addEventListener('click', function() {
				const formData = new FormData(addBookingForm);
				const data = {};

				formData.forEach(function(value, key) {
					data[key] = value;
				});

				// Form validation
				if (!data.staff_id || !data.service_id || !data.customer_name ||
					!data.customer_email || !data.booking_date || !data.start_time) {
					showToast(unbsbAdmin.strings.fill_required_fields, 'error');
					return;
				}

				saveBookingBtn.disabled = true;
				saveBookingBtn.innerHTML = '<span class="dashicons dashicons-update-alt unbsb-spin"></span> ' + unbsbAdmin.strings.saving;

				ajaxRequest('unbsb_admin_create_booking', data, function(response) {
					saveBookingBtn.disabled = false;
					saveBookingBtn.innerHTML = '<span class="dashicons dashicons-saved"></span> ' + unbsbAdmin.strings.create_booking;

					if (response.success) {
						showToast(response.data.message);
						closeModal(addBookingModal);
						location.reload();
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}
	}

	/**
	 * Reset add booking form
	 */
	function resetAddBookingForm() {
		const form = document.getElementById('unbsb-add-booking-form');
		if (form) {
			form.reset();
			// Set today's date as default
			const dateInput = document.getElementById('booking-date');
			if (dateInput) {
				dateInput.value = new Date().toISOString().split('T')[0];
			}
			// Reset status radio buttons
			const pendingRadio = form.querySelector('input[name="status"][value="pending"]');
			if (pendingRadio) {
				pendingRadio.checked = true;
			}
			// Hide service info
			const serviceInfoDiv = document.getElementById('booking-service-info');
			if (serviceInfoDiv) {
				serviceInfoDiv.style.display = 'none';
			}
		}
	}

	/**
	 * Complete Booking with Payment
	 */
	function initCompleteBooking() {
		var completeModal = document.getElementById('unbsb-complete-booking-modal');
		if (!completeModal) return;

		var saveBtn = document.getElementById('unbsb-complete-booking-save');
		var amountInput = document.getElementById('unbsb-complete-amount');
		var serviceInfo = document.getElementById('unbsb-complete-service-info');
		var bookingIdEl = document.getElementById('unbsb-complete-booking-id');
		var currentBookingId = null;

		// Open modal on complete button click (delegated).
		document.addEventListener('click', function(e) {
			var btn = e.target.closest('.unbsb-complete-booking');
			if (!btn) return;

			currentBookingId = btn.dataset.id;
			var serviceName = btn.dataset.service || '';
			var price = btn.dataset.price || '0';

			// Populate modal.
			if (bookingIdEl) {
				bookingIdEl.textContent = '#' + currentBookingId;
			}
			if (serviceInfo) {
				serviceInfo.innerHTML = '<span class="dashicons dashicons-admin-tools"></span> ' +
					'<strong>' + escHtml(serviceName) + '</strong>' +
					' &mdash; ' + parseFloat(price).toFixed(2) + ' ' + (unbsbAdmin.currency ? unbsbAdmin.currency.symbol : '');
			}
			if (amountInput) {
				amountInput.value = parseFloat(price).toFixed(2);
			}

			// Reset payment method to cash.
			var cashRadio = completeModal.querySelector('input[name="unbsb_payment_method"][value="cash"]');
			if (cashRadio) {
				cashRadio.checked = true;
			}

			openModal('unbsb-complete-booking-modal');
		});

		// Save complete booking.
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				if (!currentBookingId) return;

				var paidAmount = amountInput ? amountInput.value : '0';
				var paymentMethod = completeModal.querySelector('input[name="unbsb_payment_method"]:checked');
				paymentMethod = paymentMethod ? paymentMethod.value : 'cash';

				saveBtn.disabled = true;
				saveBtn.innerHTML = '<span class="dashicons dashicons-update-alt unbsb-spin"></span> ' + unbsbAdmin.strings.saving;

				ajaxRequest('unbsb_complete_booking_with_payment', {
					booking_id: currentBookingId,
					paid_amount: paidAmount,
					payment_method: paymentMethod
				}, function(response) {
					saveBtn.disabled = false;
					saveBtn.innerHTML = '<span class="dashicons dashicons-yes-alt"></span> ' + unbsbAdmin.strings.complete_and_save;

					if (response.success) {
						showToast(unbsbAdmin.strings.booking_completed);
						closeModal(completeModal);

						// Update row status.
						var row = document.querySelector('tr[data-id="' + currentBookingId + '"]');
						if (row) {
							// Update status select (admin bookings page).
							var statusSelect = row.querySelector('.unbsb-status-select');
							if (statusSelect) {
								statusSelect.value = 'completed';
							}

							// Update status badge (staff bookings page).
							var statusBadge = row.querySelector('.unbsb-status');
							if (statusBadge) {
								statusBadge.className = 'unbsb-status unbsb-status-completed';
								statusBadge.textContent = unbsbAdmin.strings.completed || 'Completed';
							}

							// Remove the complete button from the row.
							var completeBtn = row.querySelector('.unbsb-complete-booking');
							if (completeBtn) {
								completeBtn.remove();
							}
						}

						currentBookingId = null;
					} else {
						showToast(response.data || unbsbAdmin.strings.error, 'error');
					}
				});
			});
		}
	}

	/**
	 * Settings functionality
	 */
	function initSettings() {
		const form = document.getElementById('unbsb-settings-form');
		const saveBtn = document.getElementById('unbsb-save-settings');

		if (!form || !saveBtn) return;

		form.addEventListener('submit', function(e) {
			e.preventDefault();
		});

		saveBtn.addEventListener('click', function() {
			const formData = new FormData(form);
			const data = {};

			formData.forEach(function(value, key) {
				data[key] = value;
			});

			ajaxRequest('unbsb_save_settings', data, function(response) {
				if (response.success) {
					showToast(response.data);
				} else {
					showToast(response.data, 'error');
				}
			});
		});

		// SMS Enable/Disable toggle
		const smsEnabledCheckbox = document.getElementById('unbsb_sms_enabled');
		const smsSettings = document.getElementById('unbsb-sms-settings');

		if (smsEnabledCheckbox && smsSettings) {
			smsEnabledCheckbox.addEventListener('change', function() {
				smsSettings.style.display = this.checked ? 'block' : 'none';
			});
		}

		// CAPTCHA Provider toggle
		const captchaProvider = document.getElementById('unbsb_captcha_provider');
		const captchaSettings = document.getElementById('unbsb-captcha-settings');
		const recaptchaScore = document.getElementById('unbsb-recaptcha-score');

		if (captchaProvider && captchaSettings) {
			captchaProvider.addEventListener('change', function() {
				const provider = this.value;

				// Show/hide CAPTCHA settings
				captchaSettings.style.display = provider !== 'none' ? 'block' : 'none';

				// Show/hide reCAPTCHA score setting
				if (recaptchaScore) {
					recaptchaScore.style.display = provider === 'recaptcha' ? 'block' : 'none';
				}
			});
		}

		// SMS Balance Check
		const balanceBtn = document.getElementById('unbsb-sms-check-balance');
		const balanceResult = document.getElementById('unbsb-sms-balance-result');

		if (balanceBtn) {
			balanceBtn.addEventListener('click', function() {
				balanceResult.textContent = unbsbAdmin.strings.checking;
				balanceBtn.disabled = true;

				ajaxRequest('unbsb_sms_get_balance', {}, function(response) {
					balanceBtn.disabled = false;
					if (response.success) {
						balanceResult.textContent = response.data.message;
						balanceResult.style.color = '#10b981';
					} else {
						balanceResult.textContent = response.data;
						balanceResult.style.color = '#ef4444';
					}
				});
			});
		}

		// SMS Test Send
		const testBtn = document.getElementById('unbsb-sms-send-test');
		const testPhone = document.getElementById('unbsb_sms_test_phone');

		if (testBtn && testPhone) {
			testBtn.addEventListener('click', function() {
				const phone = testPhone.value.trim();

				if (!phone) {
					showToast(unbsbAdmin.strings.enter_test_phone, 'error');
					return;
				}

				testBtn.disabled = true;
				testBtn.textContent = unbsbAdmin.strings.sending;

				ajaxRequest('unbsb_sms_send_test', { phone: phone }, function(response) {
					testBtn.disabled = false;
					testBtn.innerHTML = '<span class="dashicons dashicons-email"></span> ' + unbsbAdmin.strings.send_test_sms;

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// SMS Templates Save
		const saveTemplatesBtn = document.getElementById('unbsb-save-sms-templates');

		if (saveTemplatesBtn) {
			saveTemplatesBtn.addEventListener('click', function() {
				const templates = [];
				const templateItems = document.querySelectorAll('.unbsb-sms-template-item');

				templateItems.forEach(function(item) {
					const textarea = item.querySelector('textarea');
					const checkbox = item.querySelector('input[type="checkbox"]');

					if (textarea) {
						const name = textarea.name;
						const id = name.replace('sms_template_', '');

						templates.push({
							id: id,
							message: textarea.value,
							is_active: checkbox ? checkbox.checked : true
						});
					}
				});

				ajaxRequest('unbsb_save_sms_templates', { templates: JSON.stringify(templates) }, function(response) {
					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Email Template Tabs
		const emailTabBtns = document.querySelectorAll('.unbsb-tab-btn');
		const emailTabContents = document.querySelectorAll('.unbsb-tab-content');

		emailTabBtns.forEach(function(btn) {
			btn.addEventListener('click', function() {
				const targetId = this.dataset.tab;

				// Remove active from all
				emailTabBtns.forEach(function(b) { b.classList.remove('active'); });
				emailTabContents.forEach(function(c) { c.classList.remove('active'); });

				// Add active to clicked
				this.classList.add('active');
				document.getElementById(targetId).classList.add('active');
			});
		});

		// Email Editor Toolbar
		document.querySelectorAll('.unbsb-editor-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const command = this.dataset.command;
				const tabContent = this.closest('.unbsb-tab-content');
				const textarea = tabContent.querySelector('textarea');

				let insertText = '';
				switch(command) {
					case 'bold':
						insertText = '<strong>text</strong>';
						break;
					case 'italic':
						insertText = '<em>text</em>';
						break;
					case 'h3':
						insertText = '<h3>Heading</h3>';
						break;
					case 'p':
						insertText = '<p>Paragraph text</p>';
						break;
					case 'table':
						insertText = '<table>\n<tr><td><strong>Field:</strong></td><td>Value</td></tr>\n</table>';
						break;
					case 'button':
						insertText = '<p style="text-align: center;">\n<a href="{manage_booking_url}" class="button">Button Text</a>\n</p>';
						break;
				}

				if (insertText && textarea) {
					const start = textarea.selectionStart;
					const end = textarea.selectionEnd;
					const text = textarea.value;
					textarea.value = text.substring(0, start) + insertText + text.substring(end);
					textarea.focus();
					textarea.selectionStart = textarea.selectionEnd = start + insertText.length;
				}
			});
		});

		// Insert Variable Dropdown
		document.querySelectorAll('.unbsb-editor-insert-var').forEach(function(select) {
			select.addEventListener('change', function() {
				if (!this.value) return;

				const tabContent = this.closest('.unbsb-tab-content');
				const textarea = tabContent.querySelector('textarea');

				if (textarea) {
					const start = textarea.selectionStart;
					const text = textarea.value;
					textarea.value = text.substring(0, start) + this.value + text.substring(start);
					textarea.focus();
					textarea.selectionStart = textarea.selectionEnd = start + this.value.length;
				}

				this.value = '';
			});
		});

		// Save Email Templates
		const saveEmailTemplatesBtn = document.getElementById('unbsb-save-email-templates');

		if (saveEmailTemplatesBtn) {
			saveEmailTemplatesBtn.addEventListener('click', function() {
				const templates = [];

				document.querySelectorAll('.unbsb-tab-content').forEach(function(tabContent) {
					const id = tabContent.id.replace('email-template-', '');
					const subjectInput = tabContent.querySelector('input[name^="email_template_subject_"]');
					const contentTextarea = tabContent.querySelector('textarea[name^="email_template_content_"]');
					const activeCheckbox = tabContent.querySelector('input[name^="email_template_active_"]');

					if (subjectInput && contentTextarea) {
						templates.push({
							id: id,
							subject: subjectInput.value,
							content: contentTextarea.value,
							is_active: activeCheckbox ? activeCheckbox.checked : true
						});
					}
				});

				saveEmailTemplatesBtn.disabled = true;
				saveEmailTemplatesBtn.textContent = unbsbAdmin.strings.saving;

				ajaxRequest('unbsb_save_email_templates', { templates: JSON.stringify(templates) }, function(response) {
					saveEmailTemplatesBtn.disabled = false;
					saveEmailTemplatesBtn.innerHTML = '<span class="dashicons dashicons-saved"></span> ' + unbsbAdmin.strings.save_templates;

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Send Test Email
		const sendTestEmailBtn = document.getElementById('unbsb-email-send-test');
		const testEmailInput = document.getElementById('unbsb_email_test_address');

		if (sendTestEmailBtn && testEmailInput) {
			sendTestEmailBtn.addEventListener('click', function() {
				const email = testEmailInput.value.trim();
				const activeTab = document.querySelector('.unbsb-tab-content.active');
				const templateType = activeTab ? activeTab.dataset.templateType : '';

				if (!email) {
					showToast(unbsbAdmin.strings.enter_test_email, 'error');
					return;
				}

				if (!templateType) {
					showToast(unbsbAdmin.strings.select_template, 'error');
					return;
				}

				sendTestEmailBtn.disabled = true;
				sendTestEmailBtn.textContent = unbsbAdmin.strings.sending;

				ajaxRequest('unbsb_email_send_test', { email: email, template_type: templateType }, function(response) {
					sendTestEmailBtn.disabled = false;
					sendTestEmailBtn.innerHTML = '<span class="dashicons dashicons-email"></span> ' + unbsbAdmin.strings.test_send;

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Preview Email Template
		const previewBtn = document.getElementById('unbsb-preview-email-template');
		const previewModal = document.getElementById('unbsb-email-preview-modal');
		const previewFrame = document.getElementById('unbsb-email-preview-frame');

		if (previewBtn && previewModal && previewFrame) {
			previewBtn.addEventListener('click', function() {
				const activeTab = document.querySelector('.unbsb-tab-content.active');
				const templateType = activeTab ? activeTab.dataset.templateType : '';

				if (!templateType) {
					showToast(unbsbAdmin.strings.select_template, 'error');
					return;
				}

				previewBtn.disabled = true;
				previewBtn.textContent = unbsbAdmin.strings.loading;

				ajaxRequest('unbsb_email_preview', { template_type: templateType }, function(response) {
					previewBtn.disabled = false;
					previewBtn.innerHTML = '<span class="dashicons dashicons-visibility"></span> ' + unbsbAdmin.strings.preview;

					if (response.success) {
						previewFrame.srcdoc = response.data.html;
						openModal('unbsb-email-preview-modal');
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}
	}

	/**
	 * Calendar functionality
	 */
	function initCalendar() {
		var calendarEl = document.getElementById('unbsb-calendar');
		if (!calendarEl || typeof FullCalendar === 'undefined') return;

		var staffSelect = document.getElementById('unbsb-cal-staff');
		var isStaffPortal = !staffSelect; // Staff portal has no staff filter

		var statusLabels = {
			'pending': unbsbAdmin.strings.pending || 'Pending',
			'confirmed': unbsbAdmin.strings.confirmed || 'Confirmed',
			'cancelled': unbsbAdmin.strings.cancelled || 'Cancelled',
			'completed': unbsbAdmin.strings.completed || 'Completed',
			'no_show': unbsbAdmin.strings.no_show || 'No Show'
		};

		var statusIcons = {
			'pending': '\u23F3',
			'confirmed': '\u2705',
			'cancelled': '\u274C',
			'completed': '\u2714',
			'no_show': '\u26A0'
		};

		function formatPrice(price) {
			var formatted = parseFloat(price).toFixed(2);
			if (unbsbAdmin.currency.position === 'before') {
				return unbsbAdmin.currency.symbol + formatted;
			}
			return formatted + ' ' + unbsbAdmin.currency.symbol;
		}

		var calendar = new FullCalendar.Calendar(calendarEl, {
			initialView: 'timeGridWeek',
			locale: unbsbAdmin.locale || 'en',
			headerToolbar: {
				left: 'prev,today,next',
				center: 'title',
				right: 'dayGridMonth,timeGridWeek,timeGridDay'
			},
			slotMinTime: '08:00:00',
			slotMaxTime: '21:00:00',
			slotDuration: '00:15:00',
			allDaySlot: false,
			nowIndicator: true,
			editable: false,
			selectable: false,
			expandRows: true,
			height: 'auto',
			slotLabelFormat: {
				hour: '2-digit',
				minute: '2-digit',
				hour12: false
			},
			eventTimeFormat: {
				hour: '2-digit',
				minute: '2-digit',
				hour12: false
			},

			events: function(info, successCallback, failureCallback) {
				var staffId = staffSelect ? staffSelect.value : '';
				var params = new URLSearchParams({
					action: 'unbsb_get_calendar_events',
					nonce: unbsbAdmin.nonce,
					start: info.startStr,
					end: info.endStr
				});
				if (staffId) {
					params.append('staff_id', staffId);
				}
				fetch(unbsbAdmin.ajaxUrl + '?' + params.toString())
					.then(function(r) { return r.json(); })
					.then(function(result) {
						if (result.success) {
							successCallback(result.data);
						} else {
							failureCallback(result.data);
						}
					})
					.catch(function(err) { failureCallback(err); });
			},

			eventContent: function(arg) {
				var props = arg.event.extendedProps;
				var startTime = arg.event.start ? arg.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }) : '';
				var endTime = arg.event.end ? arg.event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }) : '';
				var icon = statusIcons[props.status] || '';

				var html = '<div class="unbsb-fc-event">';
				html += '<div class="unbsb-fc-event-customer">' + escHtml(props.customer_name) + '</div>';
				html += '<div class="unbsb-fc-event-time">' + startTime + ' - ' + endTime + '</div>';
				html += '<div class="unbsb-fc-event-service">' + icon + ' ' + escHtml(props.service_name) + '</div>';
				if (!isStaffPortal && props.staff_name) {
					html += '<div class="unbsb-fc-event-staff">' + escHtml(props.staff_name) + '</div>';
				}
				html += '</div>';

				return { html: html };
			},

			eventClick: function(info) {
				var props = info.event.extendedProps;
				var startTime = info.event.start ? info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }) : '';
				var endTime = info.event.end ? info.event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false }) : '';

				var detail = document.getElementById('unbsb-cal-booking-detail');
				var idEl = document.getElementById('unbsb-cal-booking-id');
				if (!detail) return;

				if (idEl) {
					idEl.textContent = '#' + props.booking_id;
				}

				var html = '<div class="unbsb-sp-detail-grid">';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.customer + '</strong><span>' + escHtml(props.customer_name);
				if (props.customer_phone) html += '<br>' + escHtml(props.customer_phone);
				html += '</span></div>';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.service + '</strong><span>' + escHtml(props.service_name) + '</span></div>';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.staff + '</strong><span>' + escHtml(props.staff_name) + '</span></div>';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.status + '</strong><span><span class="unbsb-status unbsb-status-' + props.status + '">' + (statusLabels[props.status] || props.status) + '</span></span></div>';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.price + '</strong><span>' + formatPrice(props.price) + '</span></div>';
				html += '</div>';

				detail.innerHTML = html;
				openModal('unbsb-cal-booking-modal');
			}
		});

		calendar.render();

		// Staff filter refetch.
		if (staffSelect) {
			staffSelect.addEventListener('change', function() {
				calendar.refetchEvents();
			});
		}
	}

	/**
	 * Copy buttons
	 */
	function initCopyButtons() {
		document.querySelectorAll('.unbsb-copy-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const text = this.dataset.copy;

				if (navigator.clipboard) {
					navigator.clipboard.writeText(text).then(function() {
						showToast(unbsbAdmin.strings.copied);
					});
				} else {
					// Fallback
					const textarea = document.createElement('textarea');
					textarea.value = text;
					document.body.appendChild(textarea);
					textarea.select();
					document.execCommand('copy');
					document.body.removeChild(textarea);
					showToast(unbsbAdmin.strings.copied);
				}
			});
		});
	}

	/**
	 * Email Templates Page
	 */
	function initEmailTemplates() {
		const templateList = document.querySelector('.unbsb-template-list');
		if (!templateList) return;

		// Template selection
		const templateItems = document.querySelectorAll('.unbsb-template-item');
		const templateEditors = document.querySelectorAll('.unbsb-template-editor');

		templateItems.forEach(function(item) {
			item.addEventListener('click', function() {
				const templateId = this.dataset.templateId;

				// Update active states
				templateItems.forEach(function(i) { i.classList.remove('active'); });
				templateEditors.forEach(function(e) { e.classList.remove('active'); });

				this.classList.add('active');
				document.getElementById('editor-' + templateId).classList.add('active');
			});
		});

		// Toolbar buttons
		document.querySelectorAll('.unbsb-toolbar-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const command = this.dataset.command;
				const editor = this.closest('.unbsb-template-editor');
				const textarea = editor.querySelector('.unbsb-template-content');

				let insertText = '';
				switch(command) {
					case 'bold':
						insertText = '<strong>' + unbsbAdmin.strings.bold_text + '</strong>';
						break;
					case 'italic':
						insertText = '<em>' + unbsbAdmin.strings.bold_text + '</em>';
						break;
					case 'link':
						insertText = '<a href="URL">' + unbsbAdmin.strings.link_text + '</a>';
						break;
					case 'h3':
						insertText = '<h3>' + unbsbAdmin.strings.heading + '</h3>';
						break;
					case 'p':
						insertText = '<p>' + unbsbAdmin.strings.paragraph_text + '</p>';
						break;
					case 'table':
						insertText = '<table>\n<tr><td><strong>Service(s):</strong></td><td>{services_list}</td></tr>\n<tr><td><strong>Staff:</strong></td><td>{staff_name}</td></tr>\n<tr><td><strong>Date:</strong></td><td>{booking_date}</td></tr>\n<tr><td><strong>Time:</strong></td><td>{booking_time}</td></tr>\n<tr><td><strong>Duration:</strong></td><td>{total_duration}</td></tr>\n<tr><td><strong>Price:</strong></td><td>{price}</td></tr>\n</table>';
						break;
					case 'button':
						insertText = '<p style="text-align: center;">\n<a href="{manage_booking_url}" class="button">' + unbsbAdmin.strings.view_booking_btn + '</a>\n</p>';
						break;
				}

				if (insertText && textarea) {
					insertAtCursor(textarea, insertText);
				}
			});
		});

		// Variable dropdown
		document.querySelectorAll('.unbsb-insert-variable').forEach(function(select) {
			select.addEventListener('change', function() {
				if (!this.value) return;

				const editor = this.closest('.unbsb-template-editor');
				const textarea = editor.querySelector('.unbsb-template-content');

				if (textarea) {
					insertAtCursor(textarea, this.value);
				}

				this.value = '';
			});
		});

		// Clickable variable codes
		document.querySelectorAll('.unbsb-variable-group code').forEach(function(code) {
			code.addEventListener('click', function() {
				const editor = this.closest('.unbsb-template-editor');
				const textarea = editor.querySelector('.unbsb-template-content');

				if (textarea) {
					insertAtCursor(textarea, this.textContent);
					showToast(unbsbAdmin.strings.variable_added);
				}
			});
		});

		// Save template
		document.querySelectorAll('.unbsb-save-template').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const templateId = this.dataset.templateId;
				const editor = document.getElementById('editor-' + templateId);

				const subject = editor.querySelector('.unbsb-template-subject').value;
				const content = editor.querySelector('.unbsb-template-content').value;
				const isActive = editor.querySelector('.unbsb-template-active').checked;

				btn.disabled = true;
				btn.innerHTML = '<span class="dashicons dashicons-update"></span> ' + unbsbAdmin.strings.saving;

				ajaxRequest('unbsb_save_email_templates', {
					templates: JSON.stringify([{
						id: templateId,
						subject: subject,
						content: content,
						is_active: isActive
					}])
				}, function(response) {
					btn.disabled = false;
					btn.innerHTML = '<span class="dashicons dashicons-saved"></span> ' + unbsbAdmin.strings.save_template;

					if (response.success) {
						showToast(response.data);
						// Update sidebar status
						updateTemplateStatus(templateId, isActive);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});

		// Preview template
		document.querySelectorAll('.unbsb-preview-template').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const templateType = this.dataset.templateType;

				btn.disabled = true;
				btn.innerHTML = '<span class="dashicons dashicons-update"></span> ' + unbsbAdmin.strings.loading;

				ajaxRequest('unbsb_email_preview', { template_type: templateType }, function(response) {
					btn.disabled = false;
					btn.innerHTML = '<span class="dashicons dashicons-visibility"></span> ' + unbsbAdmin.strings.preview;

					if (response.success) {
						const previewFrame = document.getElementById('unbsb-email-preview-frame');
						previewFrame.srcdoc = response.data.html;
						openModal('unbsb-email-preview-modal');
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});

		// Send test email
		document.querySelectorAll('.unbsb-send-test-email').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const templateType = this.dataset.templateType;
				const editor = this.closest('.unbsb-template-editor');
				const emailInput = editor.querySelector('.unbsb-test-email-input');
				const email = emailInput.value.trim();

				if (!email) {
					showToast(unbsbAdmin.strings.enter_test_email, 'error');
					emailInput.focus();
					return;
				}

				btn.disabled = true;
				btn.innerHTML = '<span class="dashicons dashicons-update"></span> ' + unbsbAdmin.strings.sending;

				ajaxRequest('unbsb_email_send_test', {
					email: email,
					template_type: templateType
				}, function(response) {
					btn.disabled = false;
					btn.innerHTML = '<span class="dashicons dashicons-email"></span> ' + unbsbAdmin.strings.test_send;

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});

		// Device preview buttons
		document.querySelectorAll('.unbsb-device-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const width = this.dataset.width;
				const previewFrame = document.getElementById('unbsb-email-preview-frame');

				document.querySelectorAll('.unbsb-device-btn').forEach(function(b) {
					b.classList.remove('active');
				});
				this.classList.add('active');

				previewFrame.style.width = width;
			});
		});

		// Save email settings
		const saveSettingsBtn = document.getElementById('unbsb-save-email-settings');
		if (saveSettingsBtn) {
			saveSettingsBtn.addEventListener('click', function() {
				const data = {
					unbsb_email_logo_url: document.getElementById('unbsb_email_logo_url').value,
					unbsb_email_primary_color: document.getElementById('unbsb_email_primary_color').value,
					unbsb_email_reminder_enabled: document.getElementById('unbsb_email_reminder_enabled').checked ? 'yes' : 'no',
					unbsb_email_reminder_hours: document.getElementById('unbsb_email_reminder_hours').value
				};

				saveSettingsBtn.disabled = true;
				saveSettingsBtn.innerHTML = '<span class="dashicons dashicons-update"></span> ' + unbsbAdmin.strings.saving;

				ajaxRequest('unbsb_save_email_settings', data, function(response) {
					saveSettingsBtn.disabled = false;
					saveSettingsBtn.innerHTML = '<span class="dashicons dashicons-saved"></span> ' + unbsbAdmin.strings.save_settings;

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Color picker sync
		const colorPicker = document.getElementById('unbsb_email_primary_color');
		const colorText = document.getElementById('unbsb_email_primary_color_text');

		if (colorPicker && colorText) {
			colorPicker.addEventListener('input', function() {
				colorText.value = this.value;
			});

			colorText.addEventListener('input', function() {
				if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
					colorPicker.value = this.value;
				}
			});
		}

		// Helper function to insert at cursor
		function insertAtCursor(textarea, text) {
			const start = textarea.selectionStart;
			const end = textarea.selectionEnd;
			const value = textarea.value;

			textarea.value = value.substring(0, start) + text + value.substring(end);
			textarea.focus();
			textarea.selectionStart = textarea.selectionEnd = start + text.length;
		}

		// Helper function to update template status in sidebar
		function updateTemplateStatus(templateId, isActive) {
			const item = document.querySelector('.unbsb-template-item[data-template-id="' + templateId + '"]');
			if (item) {
				const statusSpan = item.querySelector('.unbsb-template-item-status');
				if (statusSpan) {
					statusSpan.className = 'unbsb-template-item-status ' + (isActive ? 'active' : 'inactive');
					statusSpan.textContent = isActive ? unbsbAdmin.strings.active : unbsbAdmin.strings.inactive;
				}
			}
		}
	}

	/**
	 * Staff Schedule - Working Calendar
	 */
	function initStaffSchedule() {
		const schedulePage = document.querySelector('.unbsb-schedule-page');
		if (!schedulePage) return;

		const staffSelect = document.getElementById('unbsb-schedule-staff');
		const daysList = document.getElementById('unbsb-days-list');
		const calendarDays = document.getElementById('unbsb-calendar-days');
		const holidaysList = document.getElementById('unbsb-holidays-list');
		const saveBtn = document.getElementById('unbsb-save-schedule');
		const holidayModal = document.getElementById('unbsb-holiday-modal');

		if (!staffSelect) return;

		let currentStaffId = staffSelect.value;
		let currentMonth = new Date().getMonth();
		let currentYear = new Date().getFullYear();
		let holidays = [];
		let workingHours = [];
		let breaks = [];

		// Month names
		const monthNames = unbsbAdmin.strings.month_names;

		// Initial load
		loadStaffSchedule(currentStaffId);

		// When staff changes
		staffSelect.addEventListener('change', function() {
			currentStaffId = this.value;
			loadStaffSchedule(currentStaffId);
		});

		// Click on day headers (expand/collapse)
		if (daysList) {
			daysList.addEventListener('click', function(e) {
				const dayHeader = e.target.closest('.unbsb-day-header');
				if (dayHeader) {
					const dayItem = dayHeader.closest('.unbsb-day-item');
					dayItem.classList.toggle('expanded');
				}
			});

			// Working checkbox change
			daysList.addEventListener('change', function(e) {
				if (e.target.classList.contains('unbsb-day-working')) {
					const dayNum = e.target.dataset.day;
					const dayItem = e.target.closest('.unbsb-day-item');
					updateDayStatus(dayItem, e.target.checked);
				}
			});

			// Add break button
			daysList.addEventListener('click', function(e) {
				const addBtn = e.target.closest('.unbsb-add-break-btn');
				if (addBtn) {
					e.stopPropagation();
					const dayNum = addBtn.dataset.day;
					addBreakRow(dayNum);
				}

				// Break delete button
				const removeBtn = e.target.closest('.unbsb-remove-break-btn');
				if (removeBtn) {
					removeBtn.closest('.unbsb-break-item').remove();
				}
			});
		}

		// Time input formatting (24 hour - HH:MM)
		document.addEventListener('input', function(e) {
			if (!e.target.classList.contains('unbsb-time-input')) return;

			let value = e.target.value.replace(/[^\d]/g, ''); // Only digits

			if (value.length >= 2) {
				let hours = parseInt(value.substring(0, 2));
				if (hours > 23) hours = 23;
				value = String(hours).padStart(2, '0') + ':' + value.substring(2);
			}

			if (value.length > 5) {
				value = value.substring(0, 5);
			}

			// Minute validation
			if (value.length === 5) {
				let minutes = parseInt(value.substring(3, 5));
				if (minutes > 59) minutes = 59;
				value = value.substring(0, 3) + String(minutes).padStart(2, '0');
			}

			e.target.value = value;
		});

		// Save button
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				saveStaffSchedule();
			});
		}

		// Takvim navigasyonu
		const prevBtn = document.querySelector('.unbsb-calendar-prev');
		const nextBtn = document.querySelector('.unbsb-calendar-next');

		if (prevBtn) {
			prevBtn.addEventListener('click', function() {
				currentMonth--;
				if (currentMonth < 0) {
					currentMonth = 11;
					currentYear--;
				}
				renderCalendar();
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function() {
				currentMonth++;
				if (currentMonth > 11) {
					currentMonth = 0;
					currentYear++;
				}
				renderCalendar();
			});
		}

		// Click on calendar day - direct toggle
		if (calendarDays) {
			calendarDays.addEventListener('click', function(e) {
				const dayEl = e.target.closest('.unbsb-calendar-day');
				if (!dayEl || dayEl.classList.contains('other-month') || dayEl.classList.contains('past')) return;

				const date = dayEl.dataset.date;
				if (!date) return;

				if (dayEl.classList.contains('holiday')) {
					// Delete holiday (direct, without confirmation)
					deleteHoliday(date);
					dayEl.classList.remove('holiday');
				} else {
					// Add holiday (direct, without popup)
					addHolidayDirect(date);
					dayEl.classList.add('holiday');
				}
			});
		}

		// Modal close
		if (holidayModal) {
			holidayModal.querySelector('.unbsb-modal-close').addEventListener('click', closeHolidayModal);
			holidayModal.querySelector('.unbsb-modal-cancel').addEventListener('click', closeHolidayModal);
			holidayModal.querySelector('.unbsb-modal-overlay').addEventListener('click', closeHolidayModal);

			// Add holiday button
			document.getElementById('unbsb-add-holiday-btn').addEventListener('click', function() {
				const date = document.getElementById('unbsb-holiday-date').value;
				const reason = document.getElementById('unbsb-holiday-reason').value;
				addHoliday(date, reason);
			});
		}

		/**
		 * Load staff schedule
		 */
		function loadStaffSchedule(staffId) {
			ajaxRequest('unbsb_get_staff_schedule', { staff_id: staffId }, function(response) {
				if (response.success) {
					workingHours = response.data.working_hours || [];
					breaks = response.data.breaks || [];
					holidays = response.data.holidays || [];

					renderWorkingHours();
					renderCalendar();
					renderHolidaysList();
				}
			});
		}

		/**
		 * Render working hours
		 */
		function renderWorkingHours() {
			if (!daysList) return;

			// Default values for each day
			const defaultHours = {};
			for (let i = 0; i <= 6; i++) {
				defaultHours[i] = {
					is_working: i !== 0 ? 1 : 0, // Sunday closed
					start_time: '09:00',
					end_time: '18:00'
				};
			}

			// Apply database values
			workingHours.forEach(function(wh) {
				defaultHours[wh.day_of_week] = {
					is_working: parseInt(wh.is_working),
					start_time: wh.start_time.substring(0, 5),
					end_time: wh.end_time.substring(0, 5)
				};
			});

			// Update DOM
			for (let day = 0; day <= 6; day++) {
				const dayItem = daysList.querySelector('.unbsb-day-item[data-day="' + day + '"]');
				if (!dayItem) continue;

				const checkbox = dayItem.querySelector('.unbsb-day-working');
				const startInput = dayItem.querySelector('.unbsb-time-start');
				const endInput = dayItem.querySelector('.unbsb-time-end');
				const breaksList = dayItem.querySelector('.unbsb-breaks-list');

				const hours = defaultHours[day];

				checkbox.checked = hours.is_working === 1;
				startInput.value = hours.start_time;
				endInput.value = hours.end_time;

				updateDayStatus(dayItem, hours.is_working === 1);

				// Clear and re-add breaks
				breaksList.innerHTML = '';
				const dayBreaks = breaks.filter(b => parseInt(b.day_of_week) === day);
				dayBreaks.forEach(function(brk) {
					addBreakRow(day, brk.start_time.substring(0, 5), brk.end_time.substring(0, 5));
				});
			}
		}

		/**
		 * Update day status
		 */
		function updateDayStatus(dayItem, isWorking) {
			const statusSpan = dayItem.querySelector('.unbsb-day-status');
			const content = dayItem.querySelector('.unbsb-day-content');
			const startInput = dayItem.querySelector('.unbsb-time-start');
			const endInput = dayItem.querySelector('.unbsb-time-end');

			if (isWorking) {
				statusSpan.textContent = startInput.value + ' - ' + endInput.value;
				statusSpan.className = 'unbsb-day-status working';
				content.style.display = '';
			} else {
				statusSpan.textContent = unbsbAdmin.strings.not_working;
				statusSpan.className = 'unbsb-day-status closed';
			}
		}

		/**
		 * Add break row
		 */
		function addBreakRow(dayNum, startTime, endTime) {
			const breaksList = daysList.querySelector('.unbsb-breaks-list[data-day="' + dayNum + '"]');
			if (!breaksList) return;

			const breakItem = document.createElement('div');
			breakItem.className = 'unbsb-break-item';
			breakItem.innerHTML = `
				<input type="text" class="unbsb-time-input unbsb-break-start" value="${startTime || '12:00'}" placeholder="12:00" maxlength="5">
				<span class="unbsb-time-separator">-</span>
				<input type="text" class="unbsb-time-input unbsb-break-end" value="${endTime || '13:00'}" placeholder="13:00" maxlength="5">
				<button type="button" class="unbsb-remove-break-btn">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			`;

			breaksList.appendChild(breakItem);
		}

		/**
		 * Save schedule
		 */
		function saveStaffSchedule() {
			const workingHoursData = [];
			const breaksData = [];

			// Collect data for each day
			for (let day = 0; day <= 6; day++) {
				const dayItem = daysList.querySelector('.unbsb-day-item[data-day="' + day + '"]');
				if (!dayItem) continue;

				const checkbox = dayItem.querySelector('.unbsb-day-working');
				const startInput = dayItem.querySelector('.unbsb-time-start');
				const endInput = dayItem.querySelector('.unbsb-time-end');

				workingHoursData.push({
					day_of_week: day,
					is_working: checkbox.checked ? 1 : 0,
					start_time: startInput.value,
					end_time: endInput.value
				});

				// Collect breaks
				const breakItems = dayItem.querySelectorAll('.unbsb-break-item');
				breakItems.forEach(function(breakItem) {
					const breakStart = breakItem.querySelector('.unbsb-break-start').value;
					const breakEnd = breakItem.querySelector('.unbsb-break-end').value;
					if (breakStart && breakEnd) {
						breaksData.push({
							day_of_week: day,
							start_time: breakStart,
							end_time: breakEnd
						});
					}
				});
			}

			ajaxRequest('unbsb_save_staff_schedule', {
				staff_id: currentStaffId,
				working_hours: JSON.stringify(workingHoursData),
				breaks: JSON.stringify(breaksData)
			}, function(response) {
				if (response.success) {
					showToast(response.data);
				} else {
					showToast(response.data, 'error');
				}
			});
		}

		/**
		 * Render calendar
		 */
		function renderCalendar() {
			if (!calendarDays) return;

			// Update month/year header
			const monthYearSpan = document.querySelector('.unbsb-calendar-month-year');
			if (monthYearSpan) {
				monthYearSpan.textContent = monthNames[currentMonth] + ' ' + currentYear;
			}

			// First day of month and number of days
			const firstDay = new Date(currentYear, currentMonth, 1);
			const lastDay = new Date(currentYear, currentMonth + 1, 0);
			const daysInMonth = lastDay.getDate();

			// First day of week (Monday = 0)
			let startDay = firstDay.getDay() - 1;
			if (startDay < 0) startDay = 6;

			// Today
			const today = new Date();
			today.setHours(0, 0, 0, 0);
			const todayStr = formatDate(today);

			// Convert holiday dates to set
			const holidayDates = new Set(holidays.map(h => h.date));

			let html = '';

			// Previous month days
			const prevMonth = new Date(currentYear, currentMonth, 0);
			const prevMonthDays = prevMonth.getDate();
			for (let i = startDay - 1; i >= 0; i--) {
				const day = prevMonthDays - i;
				html += `<div class="unbsb-calendar-day other-month">${day}</div>`;
			}

			// Current month days
			for (let day = 1; day <= daysInMonth; day++) {
				const date = new Date(currentYear, currentMonth, day);
				const dateStr = formatDate(date);
				let classes = 'unbsb-calendar-day';

				if (dateStr === todayStr) {
					classes += ' today';
				}

				if (date < today) {
					classes += ' past';
				}

				if (holidayDates.has(dateStr)) {
					classes += ' holiday';
				}

				html += `<div class="${classes}" data-date="${dateStr}">${day}</div>`;
			}

			// Next month days (to fill 42 cells)
			const totalCells = startDay + daysInMonth;
			const remainingCells = totalCells <= 35 ? 35 - totalCells : 42 - totalCells;
			for (let i = 1; i <= remainingCells; i++) {
				html += `<div class="unbsb-calendar-day other-month">${i}</div>`;
			}

			calendarDays.innerHTML = html;
		}

		/**
		 * Render holiday list
		 */
		function renderHolidaysList() {
			if (!holidaysList) return;

			if (holidays.length === 0) {
				holidaysList.innerHTML = `
					<div class="unbsb-holidays-empty">
						<span class="dashicons dashicons-yes-alt"></span>
						<p>${unbsbAdmin.strings.no_holidays}</p>
					</div>
				`;
				return;
			}

			let html = '';
			holidays.forEach(function(holiday) {
				const date = new Date(holiday.date);
				const formattedDate = date.toLocaleDateString('en-US', {
					day: 'numeric',
					month: 'long',
					year: 'numeric'
				});

				html += `
					<div class="unbsb-holiday-item" data-date="${holiday.date}">
						<span class="unbsb-holiday-date">${formattedDate}</span>
						<span class="unbsb-holiday-reason">${holiday.reason || '-'}</span>
						<button type="button" class="unbsb-holiday-delete" data-date="${holiday.date}">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</div>
				`;
			});

			holidaysList.innerHTML = html;

			// Delete buttons
			holidaysList.querySelectorAll('.unbsb-holiday-delete').forEach(function(btn) {
				btn.addEventListener('click', function() {
					const date = this.dataset.date;
					if (confirm(unbsbAdmin.strings.confirm_delete_holiday)) {
						deleteHoliday(date);
					}
				});
			});
		}

		/**
		 * Open add holiday modal
		 */
		function openHolidayModal(date) {
			if (!holidayModal) return;

			const dateObj = new Date(date);
			const formattedDate = dateObj.toLocaleDateString('en-US', {
				weekday: 'long',
				day: 'numeric',
				month: 'long',
				year: 'numeric'
			});

			document.getElementById('unbsb-holiday-date').value = date;
			document.getElementById('unbsb-holiday-date-text').textContent = formattedDate;
			document.getElementById('unbsb-holiday-reason').value = '';

			holidayModal.classList.add('active');
		}

		/**
		 * Close add holiday modal
		 */
		function closeHolidayModal() {
			if (holidayModal) {
				holidayModal.classList.remove('active');
			}
		}

		/**
		 * Add holiday (with modal)
		 */
		function addHoliday(date, reason) {
			ajaxRequest('unbsb_add_staff_holiday', {
				staff_id: currentStaffId,
				date: date,
				reason: reason
			}, function(response) {
				if (response.success) {
					showToast(response.data.message);
					closeHolidayModal();
					// Listeyi yenile
					loadStaffSchedule(currentStaffId);
				} else {
					showToast(response.data, 'error');
				}
			});
		}

		/**
		 * Add holiday (direct, without popup)
		 */
		function addHolidayDirect(date) {
			// Add to holidays array (for fast UI update)
			holidays.push({ date: date, reason: '' });
			renderHolidaysList();

			ajaxRequest('unbsb_add_staff_holiday', {
				staff_id: currentStaffId,
				date: date,
				reason: ''
			}, function(response) {
				if (response.success) {
					showToast(unbsbAdmin.strings.holiday_added);
				} else {
					// Hata durumunda geri al
					holidays = holidays.filter(h => h.date !== date);
					renderHolidaysList();
					renderCalendar();
					showToast(response.data, 'error');
				}
			});
		}

		/**
		 * Delete holiday
		 */
		function deleteHoliday(date) {
			// Remove from holidays array (for fast UI update)
			const originalHolidays = [...holidays];
			holidays = holidays.filter(h => h.date !== date);
			renderHolidaysList();

			ajaxRequest('unbsb_delete_staff_holiday', {
				staff_id: currentStaffId,
				date: date
			}, function(response) {
				if (response.success) {
					showToast(unbsbAdmin.strings.holiday_deleted);
				} else {
					// Hata durumunda geri al
					holidays = originalHolidays;
					renderHolidaysList();
					renderCalendar();
					showToast(response.data, 'error');
				}
			});
		}

		/**
		 * Tarih formatla (YYYY-MM-DD)
		 */
		function formatDate(date) {
			const year = date.getFullYear();
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const day = String(date.getDate()).padStart(2, '0');
			return `${year}-${month}-${day}`;
		}
	}

	/**
	 * Promo Codes functionality
	 */
	function initPromoCodes() {
		var modal = document.getElementById('unbsb-promo-code-modal');
		if (!modal) return;

		var addBtn = document.getElementById('unbsb-add-promo-code');
		var addBtnEmpty = document.getElementById('unbsb-add-promo-code-empty');
		var saveBtn = document.getElementById('unbsb-save-promo-code');
		var form = document.getElementById('unbsb-promo-code-form');

		var discountTypeSelect = document.getElementById('promo-discount-type');
		var discountValueGroup = document.getElementById('promo-discount-value-group');
		var discountValueSuffix = document.getElementById('promo-discount-value-suffix');
		var allServicesCheckbox = document.getElementById('promo-all-services');
		var servicesList = document.getElementById('promo-services-list');
		var allCategoriesCheckbox = document.getElementById('promo-all-categories');
		var categoriesList = document.getElementById('promo-categories-list');

		// Discount type toggle
		if (discountTypeSelect) {
			discountTypeSelect.addEventListener('change', function() {
				updateDiscountTypeUI(this.value);
			});
		}

		function updateDiscountTypeUI(type) {
			if ('cheapest_free' === type) {
				discountValueGroup.style.display = 'none';
			} else {
				discountValueGroup.style.display = '';
			}

			if (discountValueSuffix) {
				discountValueSuffix.textContent = ('percentage' === type) ? '%' : (unbsbAdmin.currency ? unbsbAdmin.currency.symbol : '€');
			}
		}

		// All services toggle
		if (allServicesCheckbox && servicesList) {
			allServicesCheckbox.addEventListener('change', function() {
				servicesList.style.display = this.checked ? 'none' : 'block';
				if (this.checked) {
					servicesList.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
						cb.checked = false;
					});
				}
			});
		}

		// All categories toggle
		if (allCategoriesCheckbox && categoriesList) {
			allCategoriesCheckbox.addEventListener('change', function() {
				categoriesList.style.display = this.checked ? 'none' : 'block';
				if (this.checked) {
					categoriesList.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
						cb.checked = false;
					});
				}
			});
		}

		// Add promo code button
		function openNewPromoCode() {
			resetPromoCodeForm();
			document.getElementById('unbsb-promo-code-modal-title').textContent = unbsbAdmin.strings.new_promo_code || 'New Promo Code';
			openModal('unbsb-promo-code-modal');
		}

		if (addBtn) addBtn.addEventListener('click', openNewPromoCode);
		if (addBtnEmpty) addBtnEmpty.addEventListener('click', openNewPromoCode);

		// Edit promo code buttons
		document.querySelectorAll('.unbsb-edit-promo-code').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var id = this.dataset.id;
				var promoCode = getPromoCodeById(id);
				if (promoCode) {
					fillPromoCodeForm(promoCode);
					document.getElementById('unbsb-promo-code-modal-title').textContent = unbsbAdmin.strings.edit_promo_code || 'Edit Promo Code';
					openModal('unbsb-promo-code-modal');
				}
			});
		});

		// Delete promo code buttons
		document.querySelectorAll('.unbsb-delete-promo-code').forEach(function(btn) {
			btn.addEventListener('click', function() {
				if (!confirm(unbsbAdmin.strings.confirm_delete)) return;

				var id = this.dataset.id;
				ajaxRequest('unbsb_delete_promo_code', { id: id }, function(response) {
					if (response.success) {
						showToast(response.data);
						location.reload();
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});

		// Copy code buttons
		document.querySelectorAll('.unbsb-copy-code').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var code = this.dataset.code;
				if (navigator.clipboard) {
					navigator.clipboard.writeText(code).then(function() {
						showToast(unbsbAdmin.strings.copied || 'Copied!');
					});
				}
			});
		});

		// Save promo code
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				var formData = new FormData(form);
				var data = {};
				var applicableServices = [];
				var applicableCategories = [];

				formData.forEach(function(value, key) {
					if ('applicable_services[]' === key) {
						applicableServices.push(value);
					} else if ('applicable_categories[]' === key) {
						applicableCategories.push(value);
					} else {
						data[key] = value;
					}
				});

				// If "All Services" is checked, don't send specific services
				if (!allServicesCheckbox.checked && applicableServices.length > 0) {
					data.applicable_services = applicableServices;
				}

				// If "All Categories" is checked, don't send specific categories
				if (!allCategoriesCheckbox.checked && applicableCategories.length > 0) {
					data.applicable_categories = applicableCategories;
				}

				// Handle first_time_only checkbox
				var firstTimeCheckbox = document.getElementById('promo-first-time-only');
				data.first_time_only = (firstTimeCheckbox && firstTimeCheckbox.checked) ? 1 : 0;

				ajaxRequest('unbsb_save_promo_code', data, function(response) {
					if (response.success) {
						showToast(response.data.message);
						closeModal(modal);
						location.reload();
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		function resetPromoCodeForm() {
			if (form) {
				form.reset();
				document.getElementById('promo-id').value = '';

				// Reset discount type UI
				updateDiscountTypeUI('percentage');

				// Reset "All" checkboxes
				if (allServicesCheckbox) {
					allServicesCheckbox.checked = true;
					servicesList.style.display = 'none';
				}
				if (allCategoriesCheckbox) {
					allCategoriesCheckbox.checked = true;
					categoriesList.style.display = 'none';
				}

				// Uncheck all service/category checkboxes
				if (servicesList) {
					servicesList.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
				}
				if (categoriesList) {
					categoriesList.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
				}

				// Reset status radio
				var activeRadio = form.querySelector('input[name="status"][value="active"]');
				if (activeRadio) activeRadio.checked = true;
			}
		}

		function fillPromoCodeForm(promoCode) {
			document.getElementById('promo-id').value = promoCode.id;
			document.getElementById('promo-code').value = promoCode.code || '';
			document.getElementById('promo-description').value = promoCode.description || '';
			document.getElementById('promo-discount-type').value = promoCode.discount_type || 'percentage';
			document.getElementById('promo-discount-value').value = promoCode.discount_value || 0;
			document.getElementById('promo-first-time-only').checked = !!parseInt(promoCode.first_time_only);
			document.getElementById('promo-min-services').value = promoCode.min_services || 0;
			document.getElementById('promo-min-order-amount').value = promoCode.min_order_amount || 0;
			document.getElementById('promo-max-uses').value = promoCode.max_uses || 0;
			document.getElementById('promo-max-uses-per-customer').value = promoCode.max_uses_per_customer || 0;
			document.getElementById('promo-start-date').value = promoCode.start_date || '';
			document.getElementById('promo-end-date').value = promoCode.end_date || '';

			// Status
			var statusRadio = form.querySelector('input[name="status"][value="' + promoCode.status + '"]');
			if (statusRadio) statusRadio.checked = true;

			// Discount type UI
			updateDiscountTypeUI(promoCode.discount_type || 'percentage');

			// Applicable services
			var appServices = parseJSON(promoCode.applicable_services);
			if (appServices && appServices.length > 0) {
				allServicesCheckbox.checked = false;
				servicesList.style.display = 'block';
				servicesList.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
					cb.checked = appServices.indexOf(parseInt(cb.value)) !== -1;
				});
			} else {
				allServicesCheckbox.checked = true;
				servicesList.style.display = 'none';
				servicesList.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
			}

			// Applicable categories
			var appCategories = parseJSON(promoCode.applicable_categories);
			if (appCategories && appCategories.length > 0) {
				allCategoriesCheckbox.checked = false;
				categoriesList.style.display = 'block';
				categoriesList.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
					cb.checked = appCategories.indexOf(parseInt(cb.value)) !== -1;
				});
			} else {
				allCategoriesCheckbox.checked = true;
				categoriesList.style.display = 'none';
				categoriesList.querySelectorAll('input[type="checkbox"]').forEach(function(cb) { cb.checked = false; });
			}
		}

		function getPromoCodeById(id) {
			if (typeof unbsbPromoCodes !== 'undefined') {
				return unbsbPromoCodes.find(function(p) {
					return p.id == id;
				});
			}
			return null;
		}

		function parseJSON(value) {
			if (!value) return null;
			if (Array.isArray(value)) return value;
			try {
				var parsed = JSON.parse(value);
				return Array.isArray(parsed) ? parsed : null;
			} catch (e) {
				return null;
			}
		}
	}

	/**
	 * Export / Import
	 */
	/**
	 * Escape HTML entities to prevent XSS
	 */
	function escHtml(str) {
		var div = document.createElement('div');
		div.textContent = String(str);
		return div.innerHTML;
	}

	function initExportImport() {
		var exportBtn = document.getElementById('unbsb-export-btn');
		if (!exportBtn) {
			return;
		}

		var importBtn = document.getElementById('unbsb-import-btn');
		var dropzone = document.getElementById('unbsb-import-dropzone');
		var fileInput = document.getElementById('unbsb-import-file');
		var fileInfo = document.getElementById('unbsb-import-file-info');
		var fileName = document.getElementById('unbsb-import-file-name');
		var fileSize = document.getElementById('unbsb-import-file-size');
		var fileRemove = document.getElementById('unbsb-import-file-remove');
		var modeInputs = document.querySelectorAll('input[name="unbsb_import_mode"]');
		var replaceWarning = document.getElementById('unbsb-replace-warning');
		var progressOverlay = document.getElementById('unbsb-import-progress-overlay');
		var progressFill = document.getElementById('unbsb-import-progress-fill');
		var progressPercent = document.getElementById('unbsb-import-progress-percent');
		var progressStatus = document.getElementById('unbsb-import-progress-status');
		var resultOverlay = document.getElementById('unbsb-import-result-overlay');
		var resultIcon = document.getElementById('unbsb-import-result-icon');
		var resultTitle = document.getElementById('unbsb-import-result-title');
		var resultMessage = document.getElementById('unbsb-import-result-message');
		var resultDetails = document.getElementById('unbsb-import-result-details');
		var resultClose = document.getElementById('unbsb-import-result-close');
		var summaryContainer = document.getElementById('unbsb-export-summary');
		var selectedFile = null;

		// Load export summary.
		loadExportSummary();

		// Export button.
		exportBtn.addEventListener('click', handleExport);

		// Dropzone events.
		dropzone.addEventListener('click', function() {
			fileInput.click();
		});

		dropzone.addEventListener('dragover', function(e) {
			e.preventDefault();
			dropzone.classList.add('unbsb-import-dropzone-active');
		});

		dropzone.addEventListener('dragleave', function() {
			dropzone.classList.remove('unbsb-import-dropzone-active');
		});

		dropzone.addEventListener('drop', function(e) {
			e.preventDefault();
			dropzone.classList.remove('unbsb-import-dropzone-active');
			if (e.dataTransfer.files.length) {
				handleFileSelect(e.dataTransfer.files[0]);
			}
		});

		fileInput.addEventListener('change', function() {
			if (fileInput.files.length) {
				handleFileSelect(fileInput.files[0]);
			}
		});

		// File remove.
		fileRemove.addEventListener('click', function(e) {
			e.stopPropagation();
			clearFile();
		});

		// Import mode toggle.
		modeInputs.forEach(function(input) {
			input.addEventListener('change', function() {
				document.querySelectorAll('.unbsb-import-mode-option').forEach(function(opt) {
					opt.classList.remove('unbsb-import-mode-selected');
				});
				input.closest('.unbsb-import-mode-option').classList.add('unbsb-import-mode-selected');
				replaceWarning.style.display = ('replace' === input.value) ? 'flex' : 'none';
			});
		});

		// Import button.
		importBtn.addEventListener('click', handleImport);

		// Result close.
		resultClose.addEventListener('click', function() {
			resultOverlay.style.display = 'none';
		});

		function loadExportSummary() {
			fetch(unbsbAdmin.restUrl + 'admin/export/summary', {
				method: 'GET',
				headers: {
					'X-WP-Nonce': unbsbAdmin.restNonce
				}
			})
			.then(function(response) {
				return response.json();
			})
			.then(function(data) {
				if (data && data.data) {
					renderSummary(data.data);
				} else if (data && !data.success) {
					summaryContainer.innerHTML = '';
				}
			})
			.catch(function() {
				summaryContainer.innerHTML = '';
			});
		}

		function renderSummary(summary) {
			var items = [
				{ icon: 'dashicons-category', label: unbsbAdmin.strings.export_categories, count: summary.categories || 0 },
				{ icon: 'dashicons-admin-tools', label: unbsbAdmin.strings.export_services, count: summary.services || 0 },
				{ icon: 'dashicons-groups', label: unbsbAdmin.strings.export_staff, count: summary.staff || 0 },
				{ icon: 'dashicons-id', label: unbsbAdmin.strings.export_customers, count: summary.customers || 0 },
				{ icon: 'dashicons-calendar-alt', label: unbsbAdmin.strings.export_bookings, count: summary.bookings || 0 },
				{ icon: 'dashicons-tag', label: unbsbAdmin.strings.export_promo_codes, count: summary.promo_codes || 0 }
			];

			var html = '<div class="unbsb-export-summary-grid">';
			items.forEach(function(item) {
				html += '<div class="unbsb-export-summary-item">' +
					'<span class="dashicons ' + escHtml(item.icon) + '"></span>' +
					'<span class="unbsb-export-summary-count">' + escHtml(item.count) + '</span>' +
					'<span class="unbsb-export-summary-label">' + escHtml(item.label) + '</span>' +
					'</div>';
			});
			html += '</div>';
			summaryContainer.innerHTML = html;
		}

		function handleExport() {
			exportBtn.disabled = true;
			exportBtn.innerHTML = '<span class="dashicons dashicons-update unbsb-spin"></span> ' + unbsbAdmin.strings.exporting;

			fetch(unbsbAdmin.restUrl + 'admin/export', {
				method: 'GET',
				headers: {
					'X-WP-Nonce': unbsbAdmin.restNonce
				}
			})
			.then(function(response) {
				if (!response.ok) {
					throw new Error('Export failed');
				}
				return response.json();
			})
			.then(function(data) {
				// Create and download JSON file.
				var exportData = data.data || data;
				var blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
				var url = URL.createObjectURL(blob);
				var a = document.createElement('a');
				var date = new Date().toISOString().slice(0, 10);
				a.href = url;
				a.download = 'unbsb-export-' + date + '.json';
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
				URL.revokeObjectURL(url);

				showToast(unbsbAdmin.strings.export_success, 'success');
			})
			.catch(function() {
				showToast(unbsbAdmin.strings.export_error, 'error');
			})
			.finally(function() {
				exportBtn.disabled = false;
				exportBtn.innerHTML = '<span class="dashicons dashicons-download"></span> ' + (unbsbAdmin.strings.export_all_data || 'Export All Data');
			});
		}

		function handleFileSelect(file) {
			if ('application/json' !== file.type && !file.name.endsWith('.json')) {
				showToast(unbsbAdmin.strings.import_invalid_file, 'error');
				return;
			}

			// 50MB file size limit.
			var maxSize = 50 * 1024 * 1024;
			if (file.size > maxSize) {
				showToast(unbsbAdmin.strings.import_file_too_large, 'error');
				return;
			}

			selectedFile = file;
			dropzone.style.display = 'none';
			fileInfo.style.display = 'flex';
			fileName.textContent = file.name;
			fileSize.textContent = formatFileSize(file.size);
			importBtn.disabled = false;
		}

		function clearFile() {
			selectedFile = null;
			fileInput.value = '';
			dropzone.style.display = '';
			fileInfo.style.display = 'none';
			importBtn.disabled = true;
		}

		function handleImport() {
			if (!selectedFile) {
				showToast(unbsbAdmin.strings.import_no_file, 'error');
				return;
			}

			var mode = document.querySelector('input[name="unbsb_import_mode"]:checked').value;

			// Confirm for replace mode.
			if ('replace' === mode && !confirm(unbsbAdmin.strings.import_confirm_replace)) {
				return;
			}

			// Show progress overlay.
			progressOverlay.style.display = 'flex';
			updateProgress(10, unbsbAdmin.strings.preparing_import);
			importBtn.disabled = true;

			// Read the file.
			var reader = new FileReader();
			reader.onload = function(e) {
				var jsonData;
				try {
					jsonData = JSON.parse(e.target.result);
				} catch (err) {
					hideProgress();
					showResult('error', unbsbAdmin.strings.error, unbsbAdmin.strings.import_invalid_file);
					importBtn.disabled = false;
					return;
				}

				updateProgress(30, unbsbAdmin.strings.importing_data);

				// Send to REST API.
				fetch(unbsbAdmin.restUrl + 'admin/import', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': unbsbAdmin.restNonce
					},
					body: JSON.stringify({
						data: jsonData,
						mode: mode
					})
				})
				.then(function(response) {
					updateProgress(70, unbsbAdmin.strings.importing_data);
					return response.json();
				})
				.then(function(response) {
					updateProgress(100, unbsbAdmin.strings.import_complete);

					setTimeout(function() {
						hideProgress();

						if (response.success || (response.data && response.data.imported)) {
							var details = response.data || response;
							showResult('success', unbsbAdmin.strings.import_success, '', details.imported);
							clearFile();
							loadExportSummary();
						} else {
							var errorMsg = (response.data && response.data.message) || response.message || unbsbAdmin.strings.import_error;
							showResult('error', unbsbAdmin.strings.error, errorMsg);
						}

						importBtn.disabled = false;
					}, 500);
				})
				.catch(function() {
					hideProgress();
					showResult('error', unbsbAdmin.strings.error, unbsbAdmin.strings.import_error);
					importBtn.disabled = false;
				});
			};

			reader.readAsText(selectedFile);
		}

		function updateProgress(percent, status) {
			progressFill.style.width = percent + '%';
			progressPercent.textContent = percent + '%';
			if (status) {
				progressStatus.textContent = status;
			}
		}

		function hideProgress() {
			progressOverlay.style.display = 'none';
			updateProgress(0, '');
		}

		function showResult(type, title, message, imported) {
			resultIcon.className = 'unbsb-import-result-icon unbsb-import-result-' + escHtml(type);
			resultIcon.innerHTML = 'success' === type
				? '<span class="dashicons dashicons-yes-alt"></span>'
				: '<span class="dashicons dashicons-dismiss"></span>';
			resultTitle.textContent = title;
			resultMessage.textContent = message;

			// Show imported counts.
			if (imported && 'object' === typeof imported) {
				var html = '<table class="unbsb-import-result-table">';
				for (var key in imported) {
					if (imported.hasOwnProperty(key)) {
						var label = key.replace(/_/g, ' ');
						label = label.charAt(0).toUpperCase() + label.slice(1);
						html += '<tr><td>' + escHtml(label) + '</td><td><strong>' + escHtml(imported[key]) + '</strong> ' + escHtml(unbsbAdmin.strings.records_imported) + '</td></tr>';
					}
				}
				html += '</table>';
				resultDetails.innerHTML = html;
				resultDetails.style.display = 'block';
			} else {
				resultDetails.innerHTML = '';
				resultDetails.style.display = 'none';
			}

			resultOverlay.style.display = 'flex';
		}

		function formatFileSize(bytes) {
			if (bytes < 1024) {
				return bytes + ' B';
			} else if (bytes < 1048576) {
				return (bytes / 1024).toFixed(1) + ' KB';
			}
			return (bytes / 1048576).toFixed(1) + ' MB';
		}
	}

	/**
	 * New Booking Page functionality
	 */
	function initNewBookingPage() {
		var form = document.getElementById('unbsb-new-booking-form');
		if (!form) return;

		var searchTimeout = null;
		var selectedCustomer = null;
		var selectedServices = [];
		var selectedStaffId = null;
		var selectedSlot = null;

		// ---- Customer Search ----
		var searchInput = document.getElementById('unbsb-nb-customer-query');
		var searchSpinner = document.getElementById('unbsb-nb-search-spinner');
		var searchResults = document.getElementById('unbsb-nb-search-results');
		var selectedCustomerDiv = document.getElementById('unbsb-nb-selected-customer');
		var customerSearchDiv = document.getElementById('unbsb-nb-customer-search');
		var newCustomerForm = document.getElementById('unbsb-nb-new-customer-form');

		if (searchInput) {
			searchInput.addEventListener('input', function() {
				var query = this.value.trim();

				if (searchTimeout) {
					clearTimeout(searchTimeout);
				}

				if (query.length < 2) {
					searchResults.style.display = 'none';
					return;
				}

				searchTimeout = setTimeout(function() {
					searchSpinner.style.display = '';
					ajaxRequest('unbsb_search_customers', { query: query }, function(response) {
						searchSpinner.style.display = 'none';
						if (response.success && response.data.length > 0) {
							renderSearchResults(response.data);
						} else {
							searchResults.innerHTML = '<div class="unbsb-nb-search-results-empty">' + unbsbAdmin.strings.nb_no_results + '</div>';
							searchResults.style.display = 'block';
						}
					});
				}, 350);
			});

			// Close dropdown on outside click
			document.addEventListener('click', function(e) {
				if (!e.target.closest('#unbsb-nb-customer-search')) {
					searchResults.style.display = 'none';
				}
			});
		}

		function renderSearchResults(customers) {
			var html = '';
			customers.forEach(function(c) {
				html += '<div class="unbsb-nb-search-result-item" data-id="' + c.id + '" data-name="' + escAttr(c.name) + '" data-phone="' + escAttr(c.phone || '') + '" data-email="' + escAttr(c.email || '') + '">';
				html += '<div class="unbsb-nb-result-avatar"><span class="dashicons dashicons-admin-users"></span></div>';
				html += '<div class="unbsb-nb-result-info">';
				html += '<span class="unbsb-nb-result-name">' + escHtml(c.name) + '</span>';
				html += '<span class="unbsb-nb-result-meta">';
				if (c.phone) html += '<span>' + escHtml(c.phone) + '</span>';
				if (c.email) html += '<span>' + escHtml(c.email) + '</span>';
				html += '</span></div></div>';
			});
			searchResults.innerHTML = html;
			searchResults.style.display = 'block';

			// Bind click
			searchResults.querySelectorAll('.unbsb-nb-search-result-item').forEach(function(item) {
				item.addEventListener('click', function() {
					selectCustomer({
						id: this.dataset.id,
						name: this.dataset.name,
						phone: this.dataset.phone,
						email: this.dataset.email
					});
				});
			});
		}

		function selectCustomer(customer) {
			selectedCustomer = customer;
			document.getElementById('unbsb-nb-customer-id').value = customer.id;
			document.getElementById('unbsb-nb-customer-name').textContent = customer.name;
			var phoneSpan = document.querySelector('#unbsb-nb-customer-phone span:last-child');
			var emailSpan = document.querySelector('#unbsb-nb-customer-email span:last-child');
			if (phoneSpan) phoneSpan.textContent = customer.phone || '-';
			if (emailSpan) emailSpan.textContent = customer.email || '-';

			customerSearchDiv.style.display = 'none';
			newCustomerForm.style.display = 'none';
			selectedCustomerDiv.style.display = 'block';
			searchResults.style.display = 'none';
			if (searchInput) searchInput.value = '';

			updateSummary();
		}

		// Change customer button
		var changeCustomerBtn = document.getElementById('unbsb-nb-change-customer');
		if (changeCustomerBtn) {
			changeCustomerBtn.addEventListener('click', function() {
				selectedCustomer = null;
				document.getElementById('unbsb-nb-customer-id').value = '';
				selectedCustomerDiv.style.display = 'none';
				customerSearchDiv.style.display = 'block';
				if (searchInput) searchInput.focus();
				updateSummary();
			});
		}

		// New Customer button
		var newCustomerBtn = document.getElementById('unbsb-nb-new-customer-btn');
		if (newCustomerBtn) {
			newCustomerBtn.addEventListener('click', function() {
				customerSearchDiv.style.display = 'none';
				selectedCustomerDiv.style.display = 'none';
				newCustomerForm.style.display = 'block';
				document.getElementById('unbsb-nb-new-name').focus();
			});
		}

		// Cancel new customer
		var cancelNewCustomerBtn = document.getElementById('unbsb-nb-cancel-new-customer');
		if (cancelNewCustomerBtn) {
			cancelNewCustomerBtn.addEventListener('click', function() {
				newCustomerForm.style.display = 'none';
				if (selectedCustomer) {
					selectedCustomerDiv.style.display = 'block';
				} else {
					customerSearchDiv.style.display = 'block';
				}
			});
		}

		// Save new customer
		var saveCustomerBtn = document.getElementById('unbsb-nb-save-customer');
		if (saveCustomerBtn) {
			saveCustomerBtn.addEventListener('click', function() {
				var name = document.getElementById('unbsb-nb-new-name').value.trim();
				var phone = document.getElementById('unbsb-nb-new-phone').value.trim();
				var email = document.getElementById('unbsb-nb-new-email').value.trim();
				var notes = document.getElementById('unbsb-nb-new-notes').value.trim();

				if (!name || !phone) {
					showToast(unbsbAdmin.strings.nb_name_phone_required, 'error');
					return;
				}

				saveCustomerBtn.disabled = true;
				saveCustomerBtn.innerHTML = '<span class="dashicons dashicons-update-alt unbsb-spin"></span> ' + unbsbAdmin.strings.saving;

				ajaxRequest('unbsb_admin_create_customer', {
					name: name,
					phone: phone,
					email: email,
					notes: notes
				}, function(response) {
					saveCustomerBtn.disabled = false;
					saveCustomerBtn.innerHTML = '<span class="dashicons dashicons-saved"></span> ' + unbsbAdmin.strings.nb_save_customer;

					if (response.success) {
						selectCustomer({
							id: response.data.id,
							name: name,
							phone: phone,
							email: email
						});
						// Clear form
						document.getElementById('unbsb-nb-new-name').value = '';
						document.getElementById('unbsb-nb-new-phone').value = '';
						document.getElementById('unbsb-nb-new-email').value = '';
						document.getElementById('unbsb-nb-new-notes').value = '';
						showToast(response.data.message || unbsbAdmin.strings.saved);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// ---- Category Filter ----
		var catFilter = document.getElementById('unbsb-nb-category-filter');
		if (catFilter) {
			catFilter.querySelectorAll('.unbsb-nb-cat-btn').forEach(function(btn) {
				btn.addEventListener('click', function() {
					catFilter.querySelectorAll('.unbsb-nb-cat-btn').forEach(function(b) {
						b.classList.remove('active');
					});
					this.classList.add('active');

					var category = this.dataset.category;
					var catGroups = document.querySelectorAll('.unbsb-nb-cat-group');
					catGroups.forEach(function(group) {
						if ('all' === category || group.dataset.category === category) {
							group.style.display = '';
							group.classList.remove('collapsed');
						} else {
							group.style.display = 'none';
						}
					});
				});
			});
		}

		// ---- Category Accordion ----
		document.querySelectorAll('.unbsb-nb-cat-group-header').forEach(function(header) {
			header.addEventListener('click', function(e) {
				// Don't toggle if clicking select all button
				if (e.target.closest('.unbsb-nb-cat-select-all')) return;
				var group = this.closest('.unbsb-nb-cat-group');
				group.classList.toggle('collapsed');
			});
		});

		// ---- Select All per Category ----
		document.querySelectorAll('.unbsb-nb-cat-select-all').forEach(function(btn) {
			btn.addEventListener('click', function(e) {
				e.stopPropagation();
				var catId = this.dataset.catId;
				var group = this.closest('.unbsb-nb-cat-group');
				var checkboxes = group.querySelectorAll('input[type="checkbox"]');
				var allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });

				checkboxes.forEach(function(cb) {
					cb.checked = !allChecked;
				});

				updateSelectedServices();
				updateStaffList();
				updateSummary();
			});
		});

		// ---- Service Search ----
		var serviceSearchInput = document.getElementById('unbsb-nb-service-search');
		var serviceNoResults = document.getElementById('unbsb-nb-service-no-results');
		if (serviceSearchInput) {
			serviceSearchInput.addEventListener('input', function() {
				var query = this.value.trim().toLowerCase();
				var items = document.querySelectorAll('.unbsb-nb-service-item');
				var groups = document.querySelectorAll('.unbsb-nb-cat-group');
				var totalVisible = 0;

				items.forEach(function(item) {
					var name = item.dataset.serviceName || '';
					if (!query || name.indexOf(query) !== -1) {
						item.style.display = '';
						totalVisible++;
					} else {
						item.style.display = 'none';
					}
				});

				// Show/hide groups based on visible children
				groups.forEach(function(group) {
					var visibleInGroup = group.querySelectorAll('.unbsb-nb-service-item:not([style*="display: none"])').length;
					if (0 === visibleInGroup) {
						group.style.display = 'none';
					} else {
						group.style.display = '';
						if (query) {
							group.classList.remove('collapsed');
						}
					}
				});

				if (serviceNoResults) {
					serviceNoResults.style.display = (0 === totalVisible && query) ? 'block' : 'none';
				}
			});
		}

		// ---- Services Selection ----
		var servicesList = document.getElementById('unbsb-nb-services-list');
		if (servicesList) {
			servicesList.addEventListener('change', function(e) {
				if ('checkbox' === e.target.type) {
					updateSelectedServices();
					updateStaffList();
					updateSummary();
				}
			});
		}

		function updateSelectedServices() {
			selectedServices = [];
			var totalDuration = 0;
			var totalPrice = 0;

			document.querySelectorAll('#unbsb-nb-services-list input[type="checkbox"]:checked').forEach(function(cb) {
				var duration = parseInt(cb.dataset.duration, 10) || 0;
				var price = parseFloat(cb.dataset.price) || 0;

				selectedServices.push({
					id: cb.value,
					name: cb.dataset.name,
					duration: duration,
					price: price
				});

				totalDuration += duration;
				totalPrice += price;
			});

			var summaryDiv = document.getElementById('unbsb-nb-services-summary');
			if (selectedServices.length > 0) {
				document.getElementById('unbsb-nb-total-duration').textContent = totalDuration;
				document.getElementById('unbsb-nb-total-price').textContent = totalPrice.toFixed(2);
				summaryDiv.style.display = 'block';
			} else {
				summaryDiv.style.display = 'none';
			}

			// Reset staff and slot when services change
			selectedStaffId = null;
			selectedSlot = null;
			document.getElementById('unbsb-nb-start-time').value = '';
			resetSlotsUI();
		}

		// ---- Staff List ----
		function updateStaffList() {
			var staffListDiv = document.getElementById('unbsb-nb-staff-list');
			if (!staffListDiv || typeof unbsbNewBookingData === 'undefined') return;

			var serviceIds = selectedServices.map(function(s) { return parseInt(s.id, 10); });

			if (0 === serviceIds.length) {
				staffListDiv.innerHTML = '<p class="unbsb-text-muted">' + unbsbAdmin.strings.nb_select_service_first + '</p>';
				return;
			}

			// Filter staff who provide ALL selected services
			var availableStaff = unbsbNewBookingData.staff.filter(function(staff) {
				var staffServiceIds = (staff.service_ids || []).map(function(id) { return parseInt(id, 10); });
				return serviceIds.every(function(sid) {
					return staffServiceIds.indexOf(sid) !== -1;
				});
			});

			var html = '';

			availableStaff.forEach(function(staff) {
				html += '<label class="unbsb-nb-staff-item">';
				html += '<input type="radio" name="staff_id" value="' + staff.id + '">';
				html += '<span class="unbsb-nb-staff-radio"></span>';
				html += '<div class="unbsb-nb-staff-avatar">';
				if (staff.avatar_url) {
					html += '<img src="' + escAttr(staff.avatar_url) + '" alt="' + escAttr(staff.name) + '">';
				} else {
					html += '<span class="dashicons dashicons-admin-users"></span>';
				}
				html += '</div>';
				html += '<span class="unbsb-nb-staff-name">' + escHtml(staff.name) + '</span>';
				html += '</label>';
			});

			if (0 === availableStaff.length) {
				html = '<p class="unbsb-text-muted">' + unbsbAdmin.strings.nb_no_staff_available + '</p>';
			}

			staffListDiv.innerHTML = html;

			// Bind radio change
			staffListDiv.querySelectorAll('input[type="radio"]').forEach(function(radio) {
				radio.addEventListener('change', function() {
					selectedStaffId = this.value;
					selectedSlot = null;
					document.getElementById('unbsb-nb-start-time').value = '';
					loadSlots();
					updateSummary();
				});
			});

			// Pre-select staff if set (staff portal).
			if (unbsbNewBookingData.preselectedStaffId) {
				var preselectedRadio = staffListDiv.querySelector('input[type="radio"][value="' + unbsbNewBookingData.preselectedStaffId + '"]');
				if (preselectedRadio) {
					preselectedRadio.checked = true;
					preselectedRadio.dispatchEvent(new Event('change'));
				}
			}
		}

		// ---- Date & Time Slots ----
		var dateInput = document.getElementById('unbsb-nb-date');
		if (dateInput) {
			// Set default to today
			dateInput.value = new Date().toISOString().split('T')[0];

			dateInput.addEventListener('change', function() {
				selectedSlot = null;
				document.getElementById('unbsb-nb-start-time').value = '';
				loadSlots();
				updateSummary();
			});
		}

		function loadSlots() {
			var slotsWrap = document.getElementById('unbsb-nb-slots-wrap');
			var date = dateInput ? dateInput.value : '';

			if (!selectedStaffId || !date || 0 === selectedServices.length) {
				slotsWrap.innerHTML = '<p class="unbsb-text-muted">' + unbsbAdmin.strings.nb_select_staff_date + '</p>';
				return;
			}

			var totalDuration = selectedServices.reduce(function(sum, s) { return sum + s.duration; }, 0);
			var staffId = 'any' === selectedStaffId ? '' : selectedStaffId;

			slotsWrap.innerHTML = '<p class="unbsb-text-muted"><span class="dashicons dashicons-update-alt unbsb-spin"></span> ' + unbsbAdmin.strings.loading + '</p>';

			// Use REST API for available slots
			var serviceId = selectedServices[0].id;
			var url = unbsbAdmin.restUrl + 'slots?service_id=' + serviceId + '&date=' + date + '&duration=' + totalDuration;
			if (staffId) {
				url += '&staff_id=' + staffId;
			}

			fetch(url, {
				headers: { 'X-WP-Nonce': unbsbAdmin.restNonce }
			})
			.then(function(r) { return r.json(); })
			.then(function(data) {
				var slots = data.slots || data || [];
				if (Array.isArray(slots) && slots.length > 0) {
					var html = '<div class="unbsb-nb-slots-grid">';
					slots.forEach(function(slot) {
						var time = typeof slot === 'string' ? slot : slot.start || slot.time || slot.start_time || '';
						var endTime = typeof slot === 'object' ? (slot.end || '') : '';
						var label = endTime ? (time + ' - ' + endTime) : time;
						html += '<button type="button" class="unbsb-nb-slot-btn" data-time="' + escAttr(time) + '">' + escHtml(label) + '</button>';
					});
					html += '</div>';
					slotsWrap.innerHTML = html;

					// Bind slot click
					slotsWrap.querySelectorAll('.unbsb-nb-slot-btn').forEach(function(btn) {
						btn.addEventListener('click', function() {
							slotsWrap.querySelectorAll('.unbsb-nb-slot-btn').forEach(function(b) {
								b.classList.remove('active');
							});
							this.classList.add('active');
							selectedSlot = this.dataset.time;
							document.getElementById('unbsb-nb-start-time').value = selectedSlot;
							updateSummary();
						});
					});
				} else {
					slotsWrap.innerHTML = '<p class="unbsb-text-muted">' + unbsbAdmin.strings.nb_no_slots + '</p>';
				}
			})
			.catch(function() {
				slotsWrap.innerHTML = '<p class="unbsb-text-muted">' + unbsbAdmin.strings.error + '</p>';
			});
		}

		function resetSlotsUI() {
			var slotsWrap = document.getElementById('unbsb-nb-slots-wrap');
			if (slotsWrap) {
				slotsWrap.innerHTML = '<p class="unbsb-text-muted">' + unbsbAdmin.strings.nb_select_staff_date + '</p>';
			}
		}

		// ---- Summary Update ----
		function updateSummary() {
			// Customer
			var sumCustomer = document.getElementById('unbsb-nb-sum-customer-val');
			if (selectedCustomer) {
				sumCustomer.textContent = selectedCustomer.name;
				sumCustomer.classList.remove('unbsb-text-muted');
			} else {
				sumCustomer.textContent = unbsbAdmin.strings.nb_not_selected;
				sumCustomer.classList.add('unbsb-text-muted');
			}

			// Services
			var sumServices = document.getElementById('unbsb-nb-sum-services-val');
			if (selectedServices.length > 0) {
				var listHtml = '<ul class="unbsb-nb-summary-services-list">';
				selectedServices.forEach(function(s) {
					listHtml += '<li>' + escHtml(s.name) + '</li>';
				});
				listHtml += '</ul>';
				sumServices.innerHTML = listHtml;
				sumServices.classList.remove('unbsb-text-muted');
			} else {
				sumServices.textContent = unbsbAdmin.strings.nb_not_selected;
				sumServices.classList.add('unbsb-text-muted');
			}

			// Staff
			var sumStaff = document.getElementById('unbsb-nb-sum-staff-val');
			if (selectedStaffId) {
				if ('any' === selectedStaffId) {
					sumStaff.textContent = unbsbAdmin.strings.nb_any_staff;
				} else if (typeof unbsbNewBookingData !== 'undefined') {
					var staffObj = unbsbNewBookingData.staff.find(function(s) {
						return String(s.id) === String(selectedStaffId);
					});
					sumStaff.textContent = staffObj ? staffObj.name : selectedStaffId;
				}
				sumStaff.classList.remove('unbsb-text-muted');
			} else {
				sumStaff.textContent = unbsbAdmin.strings.nb_not_selected;
				sumStaff.classList.add('unbsb-text-muted');
			}

			// Date & Time
			var sumDatetime = document.getElementById('unbsb-nb-sum-datetime-val');
			var date = dateInput ? dateInput.value : '';
			if (date && selectedSlot) {
				sumDatetime.textContent = date + ' ' + selectedSlot;
				sumDatetime.classList.remove('unbsb-text-muted');
			} else if (date) {
				sumDatetime.textContent = date;
				sumDatetime.classList.remove('unbsb-text-muted');
			} else {
				sumDatetime.textContent = unbsbAdmin.strings.nb_not_selected;
				sumDatetime.classList.add('unbsb-text-muted');
			}

			// Totals
			var totalDuration = selectedServices.reduce(function(sum, s) { return sum + s.duration; }, 0);
			var totalPrice = selectedServices.reduce(function(sum, s) { return sum + s.price; }, 0);
			document.getElementById('unbsb-nb-sum-duration').textContent = totalDuration;
			document.getElementById('unbsb-nb-sum-price').textContent = totalPrice.toFixed(2);
		}

		// ---- Create Booking ----
		var createBtn = document.getElementById('unbsb-nb-create-booking');
		if (createBtn) {
			createBtn.addEventListener('click', function() {
				// Validate
				if (!selectedCustomer) {
					showToast(unbsbAdmin.strings.nb_select_customer, 'error');
					return;
				}
				if (0 === selectedServices.length) {
					showToast(unbsbAdmin.strings.nb_select_service, 'error');
					return;
				}
				if (!selectedStaffId) {
					showToast(unbsbAdmin.strings.nb_select_staff, 'error');
					return;
				}
				var bookingDate = dateInput ? dateInput.value : '';
				if (!bookingDate) {
					showToast(unbsbAdmin.strings.nb_select_date, 'error');
					return;
				}
				if (!selectedSlot) {
					showToast(unbsbAdmin.strings.nb_select_time, 'error');
					return;
				}

				var data = {
					customer_id: selectedCustomer.id,
					customer_name: selectedCustomer.name,
					customer_email: selectedCustomer.email || '',
					customer_phone: selectedCustomer.phone || '',
					service_ids: selectedServices.map(function(s) { return s.id; }),
					staff_id: 'any' === selectedStaffId ? '' : selectedStaffId,
					booking_date: bookingDate,
					start_time: selectedSlot,
					status: form.querySelector('input[name="status"]:checked').value,
					notes: document.getElementById('unbsb-nb-notes').value,
					internal_notes: document.getElementById('unbsb-nb-internal-notes').value
				};

				createBtn.disabled = true;
				createBtn.innerHTML = '<span class="dashicons dashicons-update-alt unbsb-spin"></span> ' + unbsbAdmin.strings.saving;

				ajaxRequest('unbsb_admin_create_booking', data, function(response) {
					createBtn.disabled = false;
					createBtn.innerHTML = '<span class="dashicons dashicons-saved"></span> ' + unbsbAdmin.strings.create_booking;

					if (response.success) {
						showToast(response.data.message || unbsbAdmin.strings.saved);
						window.location.href = unbsbAdmin.ajaxUrl.replace('admin-ajax.php', 'admin.php?page=unbsb-bookings');
					} else {
						showToast(response.data || unbsbAdmin.strings.error, 'error');
					}
				});
			});
		}

		// ---- Utility ----
		function escHtml(str) {
			var div = document.createElement('div');
			div.textContent = str || '';
			return div.innerHTML;
		}

		function escAttr(str) {
			return (str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}
	}

	/**
	 * Staff Portal - My Bookings
	 */
	function initStaffBookings() {
		var container = document.querySelector('.unbsb-sp-date-filter');
		if (!container) return;

		// View toggle (List / Calendar).
		var viewToggle = document.getElementById('unbsb-sp-view-toggle');
		if (viewToggle) {
			var listView = document.getElementById('unbsb-sp-list-view');
			var calendarView = document.getElementById('unbsb-sp-calendar-view');
			var toggleBtns = viewToggle.querySelectorAll('.unbsb-view-toggle-btn');

			toggleBtns.forEach(function(btn) {
				btn.addEventListener('click', function() {
					var view = this.dataset.view;
					toggleBtns.forEach(function(b) { b.classList.remove('active'); });
					this.classList.add('active');

					if ('calendar' === view) {
						if (listView) listView.style.display = 'none';
						if (calendarView) calendarView.style.display = '';
					} else {
						if (listView) listView.style.display = '';
						if (calendarView) calendarView.style.display = 'none';
					}
				});
			});
		}

		// Confirm booking.
		document.querySelectorAll('.unbsb-sp-confirm-booking').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var bookingId = this.dataset.id;
				if (!confirm(unbsbAdmin.strings.sp_confirm_booking)) return;

				var row = this.closest('tr');
				btn.disabled = true;

				ajaxRequest('unbsb_staff_confirm_booking', { booking_id: bookingId }, function(response) {
					btn.disabled = false;
					if (response.success) {
						showToast(unbsbAdmin.strings.sp_booking_confirmed);
						// Update row status.
						var statusEl = row.querySelector('.unbsb-status');
						if (statusEl) {
							statusEl.className = 'unbsb-status unbsb-status-confirmed';
							statusEl.textContent = unbsbAdmin.strings.active;
						}
						// Remove action buttons.
						var actions = row.querySelector('.unbsb-actions');
						if (actions) {
							var confirmBtn = actions.querySelector('.unbsb-sp-confirm-booking');
							var rejectBtn = actions.querySelector('.unbsb-sp-reject-booking');
							if (confirmBtn) confirmBtn.remove();
							if (rejectBtn) rejectBtn.remove();
						}
					} else {
						showToast(response.data || unbsbAdmin.strings.error, 'error');
					}
				});
			});
		});

		// Reject booking.
		document.querySelectorAll('.unbsb-sp-reject-booking').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var bookingId = this.dataset.id;
				if (!confirm(unbsbAdmin.strings.sp_reject_booking)) return;

				var row = this.closest('tr');
				btn.disabled = true;

				ajaxRequest('unbsb_staff_reject_booking', { booking_id: bookingId }, function(response) {
					btn.disabled = false;
					if (response.success) {
						showToast(unbsbAdmin.strings.sp_booking_rejected);
						var statusEl = row.querySelector('.unbsb-status');
						if (statusEl) {
							statusEl.className = 'unbsb-status unbsb-status-cancelled';
							statusEl.textContent = statusEl.textContent;
						}
						var actions = row.querySelector('.unbsb-actions');
						if (actions) {
							var confirmBtn = actions.querySelector('.unbsb-sp-confirm-booking');
							var rejectBtn = actions.querySelector('.unbsb-sp-reject-booking');
							if (confirmBtn) confirmBtn.remove();
							if (rejectBtn) rejectBtn.remove();
						}
					} else {
						showToast(response.data || unbsbAdmin.strings.error, 'error');
					}
				});
			});
		});

		// View booking detail.
		document.querySelectorAll('.unbsb-sp-view-booking').forEach(function(btn) {
			btn.addEventListener('click', function() {
				var row = this.closest('tr');
				var bookingId = this.dataset.id;
				var modal = document.getElementById('unbsb-sp-booking-modal');
				if (!modal) return;

				var detailEl = document.getElementById('unbsb-sp-booking-detail');
				var idEl = document.getElementById('unbsb-sp-booking-id');

				if (idEl) {
					idEl.textContent = '#' + bookingId;
				}

				// Build detail from row data.
				var dateCell = row.cells[0];
				var customerCell = row.cells[1];
				var serviceCell = row.cells[2];
				var priceCell = row.cells[3];
				var statusCell = row.cells[4];

				var html = '<div class="unbsb-sp-detail-grid">';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.customer + '</strong><span>' + customerCell.innerHTML + '</span></div>';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.service + '</strong><span>' + serviceCell.innerHTML + '</span></div>';
				html += '<div class="unbsb-sp-detail-item"><strong>Date/Time</strong><span>' + dateCell.innerHTML + '</span></div>';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.price + '</strong><span>' + priceCell.textContent.trim() + '</span></div>';
				html += '<div class="unbsb-sp-detail-item"><strong>' + unbsbAdmin.strings.status + '</strong><span>' + statusCell.innerHTML + '</span></div>';
				html += '</div>';

				if (detailEl) {
					detailEl.innerHTML = html;
				}

				openModal('unbsb-sp-booking-modal');
			});
		});
	}

	/**
	 * Staff Portal - My Schedule (Own)
	 */
	function initStaffScheduleOwn() {
		var hoursList = document.getElementById('unbsb-sp-hours-list');
		var offdaysList = document.getElementById('unbsb-sp-offdays-list');
		if (!hoursList || !offdaysList) return;

		var staffId = typeof unbsbStaffPortal !== 'undefined' ? unbsbStaffPortal.staffId : null;
		if (!staffId) return;

		var dayNames = unbsbAdmin.strings.day_names || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
		// Map day_of_week (0=Sun,1=Mon..6=Sat) to dayNames index (0=Mon..6=Sun).
		var dayMap = { 1: 0, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 0: 6 };

		// Load schedule data.
		loadSchedule();

		// Add off day toggle.
		var addBtn = document.getElementById('unbsb-sp-add-offday-btn');
		var offForm = document.getElementById('unbsb-sp-offday-form');
		var cancelBtn = document.getElementById('unbsb-sp-cancel-offday');
		var saveBtn = document.getElementById('unbsb-sp-save-offday');

		if (addBtn && offForm) {
			addBtn.addEventListener('click', function() {
				offForm.style.display = offForm.style.display === 'none' ? 'block' : 'none';
			});
		}

		if (cancelBtn && offForm) {
			cancelBtn.addEventListener('click', function() {
				offForm.style.display = 'none';
				document.getElementById('unbsb-sp-offday-date').value = '';
				document.getElementById('unbsb-sp-offday-reason').value = '';
			});
		}

		// Save off day.
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				var dateInput = document.getElementById('unbsb-sp-offday-date');
				var reasonInput = document.getElementById('unbsb-sp-offday-reason');
				var date = dateInput.value;
				var reason = reasonInput.value;

				if (!date) {
					showToast(unbsbAdmin.strings.sp_date_required, 'error');
					return;
				}

				saveBtn.disabled = true;

				ajaxRequest('unbsb_staff_add_holiday', { date: date, reason: reason }, function(response) {
					saveBtn.disabled = false;
					if (response.success) {
						showToast(unbsbAdmin.strings.sp_holiday_added);
						offForm.style.display = 'none';
						dateInput.value = '';
						reasonInput.value = '';
						loadSchedule();
					} else {
						showToast(response.data || unbsbAdmin.strings.error, 'error');
					}
				});
			});
		}

		// Delete off day (delegated).
		offdaysList.addEventListener('click', function(e) {
			var deleteBtn = e.target.closest('.unbsb-sp-delete-offday');
			if (!deleteBtn) return;

			if (!confirm(unbsbAdmin.strings.sp_confirm_remove_holiday)) return;

			var holidayId = deleteBtn.dataset.id;
			deleteBtn.disabled = true;

			ajaxRequest('unbsb_staff_remove_holiday', { holiday_id: holidayId }, function(response) {
				deleteBtn.disabled = false;
				if (response.success) {
					showToast(unbsbAdmin.strings.sp_holiday_removed);
					loadSchedule();
				} else {
					showToast(response.data || unbsbAdmin.strings.error, 'error');
				}
			});
		});

		/**
		 * Load staff schedule and holidays.
		 */
		function loadSchedule() {
			hoursList.innerHTML = '<div class="unbsb-sp-hours-loading"><span class="dashicons dashicons-update-alt unbsb-spin"></span> ' + unbsbAdmin.strings.loading + '</div>';
			offdaysList.innerHTML = '<div class="unbsb-sp-hours-loading"><span class="dashicons dashicons-update-alt unbsb-spin"></span> ' + unbsbAdmin.strings.loading + '</div>';

			ajaxRequest('unbsb_get_staff_schedule', { staff_id: staffId }, function(response) {
				if (response.success) {
					renderWorkingHours(response.data.working_hours || []);
					renderHolidays(response.data.holidays || []);
				} else {
					hoursList.innerHTML = '<div class="unbsb-sp-empty"><span class="dashicons dashicons-warning"></span><p>' + (response.data || unbsbAdmin.strings.error) + '</p></div>';
					offdaysList.innerHTML = '';
					showToast(response.data || unbsbAdmin.strings.error, 'error');
				}
			});
		}

		/**
		 * Render working hours list (read-only for staff).
		 */
		function renderWorkingHours(workingHours) {
			var defaultHours = {};
			var dayOrder = [1, 2, 3, 4, 5, 6, 0]; // Mon-Sun

			for (var i = 0; i <= 6; i++) {
				defaultHours[i] = {
					is_working: i !== 0 ? 1 : 0,
					start_time: '09:00',
					end_time: '18:00'
				};
			}

			workingHours.forEach(function(wh) {
				defaultHours[wh.day_of_week] = {
					is_working: parseInt(wh.is_working),
					start_time: wh.start_time.substring(0, 5),
					end_time: wh.end_time.substring(0, 5)
				};
			});

			var html = '';
			dayOrder.forEach(function(dayNum) {
				var day = defaultHours[dayNum];
				var dayIndex = dayMap[dayNum];
				var dayName = dayNames[dayIndex] || '';
				var isWorking = parseInt(day.is_working);

				html += '<div class="unbsb-sp-hours-item' + (isWorking ? '' : ' unbsb-sp-hours-off') + '">';
				html += '<span class="unbsb-sp-hours-day">' + dayName + '</span>';
				if (isWorking) {
					html += '<span class="unbsb-sp-hours-time">' + day.start_time + ' - ' + day.end_time + '</span>';
				} else {
					html += '<span class="unbsb-sp-hours-closed">' + unbsbAdmin.strings.not_working + '</span>';
				}
				html += '</div>';
			});

			hoursList.innerHTML = html;
		}

		/**
		 * Render holidays list.
		 */
		function renderHolidays(holidays) {
			if (!holidays.length) {
				offdaysList.innerHTML = '<div class="unbsb-sp-empty">' +
					'<span class="dashicons dashicons-palmtree"></span>' +
					'<p>' + unbsbAdmin.strings.sp_no_holidays + '</p></div>';
				return;
			}

			var html = '';
			holidays.forEach(function(h) {
				html += '<div class="unbsb-sp-offday-item">';
				html += '<div class="unbsb-sp-offday-info">';
				html += '<span class="unbsb-sp-offday-date">' + h.date + '</span>';
				if (h.reason) {
					html += '<span class="unbsb-sp-offday-reason">' + h.reason + '</span>';
				}
				html += '</div>';
				html += '<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-danger unbsb-sp-delete-offday" data-id="' + h.id + '" title="Delete">';
				html += '<span class="dashicons dashicons-trash"></span>';
				html += '</button>';
				html += '</div>';
			});

			offdaysList.innerHTML = html;
		}
	}

})();
