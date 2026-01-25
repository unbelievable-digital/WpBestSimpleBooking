/**
 * Appointment General - Admin JavaScript
 *
 * @package Appointment_General
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
	});

	/**
	 * Toast notification
	 */
	function showToast(message, type) {
		type = type || 'success';

		const toast = document.createElement('div');
		toast.className = 'ag-toast ag-toast-' + type;
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
		data.nonce = agAdmin.nonce;

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

		fetch(agAdmin.ajaxUrl, {
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
			showToast(agAdmin.strings.error, 'error');
		});
	}

	/**
	 * Modal functionality
	 */
	function initModals() {
		// Close modal on overlay click
		document.querySelectorAll('.ag-modal-overlay').forEach(function(overlay) {
			overlay.addEventListener('click', function() {
				closeModal(this.closest('.ag-modal'));
			});
		});

		// Close modal on close button click
		document.querySelectorAll('.ag-modal-close').forEach(function(btn) {
			btn.addEventListener('click', function() {
				closeModal(this.closest('.ag-modal'));
			});
		});

		// Close modal on Escape key
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				const modal = document.querySelector('.ag-modal[style*="block"]');
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
		const addBtn = document.getElementById('ag-add-category');
		const addBtnEmpty = document.getElementById('ag-add-category-empty');
		const saveBtn = document.getElementById('ag-save-category');
		const modal = document.getElementById('ag-category-modal');
		const form = document.getElementById('ag-category-form');

		if (!modal) return;

		// Add category button
		[addBtn, addBtnEmpty].forEach(function(btn) {
			if (btn) {
				btn.addEventListener('click', function() {
					resetCategoryForm();
					document.getElementById('ag-category-modal-title').textContent = 'Yeni Kategori';
					openModal('ag-category-modal');
				});
			}
		});

		// Edit category buttons
		document.querySelectorAll('.ag-edit-category').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const id = this.dataset.id;
				const category = getCategoryById(id);
				if (category) {
					fillCategoryForm(category);
					document.getElementById('ag-category-modal-title').textContent = 'Kategoriyi Düzenle';
					openModal('ag-category-modal');
				}
			});
		});

		// Delete category buttons
		document.querySelectorAll('.ag-delete-category').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const serviceCount = parseInt(this.dataset.serviceCount) || 0;
				let confirmMsg = agAdmin.strings.confirm_delete;

				if (serviceCount > 0) {
					confirmMsg = 'Bu kategoride ' + serviceCount + ' hizmet var. Silmek istediğinize emin misiniz? (Hizmetler kategorisiz kalacak)';
				}

				if (confirm(confirmMsg)) {
					const id = this.dataset.id;
					ajaxRequest('ag_delete_category', { id: id }, function(response) {
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

				ajaxRequest('ag_save_category', data, function(response) {
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
		const form = document.getElementById('ag-category-form');
		if (form) {
			form.reset();
			document.getElementById('category-id').value = '';
			document.getElementById('category-color').value = '#3788d8';
		}
	}

	function fillCategoryForm(category) {
		document.getElementById('category-id').value = category.id;
		document.getElementById('category-name').value = category.name;
		document.getElementById('category-description').value = category.description || '';
		document.getElementById('category-color').value = category.color || '#3788d8';
		document.getElementById('category-sort-order').value = category.sort_order || 0;
		document.getElementById('category-status').value = category.status;
	}

	function getCategoryById(id) {
		if (typeof agCategories !== 'undefined') {
			return agCategories.find(function(c) {
				return c.id == id;
			});
		}
		return null;
	}

	/**
	 * Services functionality
	 */
	function initServices() {
		const addBtn = document.getElementById('ag-add-service');
		const addBtnEmpty = document.getElementById('ag-add-service-empty');
		const saveBtn = document.getElementById('ag-save-service');
		const modal = document.getElementById('ag-service-modal');
		const form = document.getElementById('ag-service-form');

		if (!modal) return;

		// Add service button
		[addBtn, addBtnEmpty].forEach(function(btn) {
			if (btn) {
				btn.addEventListener('click', function() {
					resetServiceForm();
					document.getElementById('ag-service-modal-title').textContent = agAdmin.strings.saving || 'Yeni Hizmet';
					openModal('ag-service-modal');
				});
			}
		});

		// Edit service buttons
		document.querySelectorAll('.ag-edit-service').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const id = this.dataset.id;
				const service = getServiceById(id);
				if (service) {
					fillServiceForm(service);
					document.getElementById('ag-service-modal-title').textContent = 'Hizmeti Düzenle';
					openModal('ag-service-modal');
				}
			});
		});

		// Delete service buttons
		document.querySelectorAll('.ag-delete-service').forEach(function(btn) {
			btn.addEventListener('click', function() {
				if (confirm(agAdmin.strings.confirm_delete)) {
					const id = this.dataset.id;
					ajaxRequest('ag_delete_service', { id: id }, function(response) {
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

				ajaxRequest('ag_save_service', data, function(response) {
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

	function resetServiceForm() {
		const form = document.getElementById('ag-service-form');
		if (form) {
			form.reset();
			document.getElementById('service-id').value = '';
			document.getElementById('service-color').value = '#3788d8';

			const categorySelect = document.getElementById('service-category');
			if (categorySelect) {
				categorySelect.value = '';
			}
		}
	}

	function fillServiceForm(service) {
		document.getElementById('service-id').value = service.id;
		document.getElementById('service-name').value = service.name;
		document.getElementById('service-description').value = service.description || '';
		document.getElementById('service-duration').value = service.duration;
		document.getElementById('service-price').value = service.price;
		document.getElementById('service-buffer-before').value = service.buffer_before || 0;
		document.getElementById('service-buffer-after').value = service.buffer_after || 0;
		document.getElementById('service-color').value = service.color || '#3788d8';
		document.getElementById('service-status').value = service.status;

		// Kategori
		const categorySelect = document.getElementById('service-category');
		if (categorySelect) {
			categorySelect.value = service.category_id || '';
		}
	}

	function getServiceById(id) {
		if (typeof agServices !== 'undefined') {
			return agServices.find(function(s) {
				return s.id == id;
			});
		}
		return null;
	}

	/**
	 * Staff functionality
	 */
	function initStaff() {
		const addBtn = document.getElementById('ag-add-staff');
		const addBtnEmpty = document.getElementById('ag-add-staff-empty');
		const saveBtn = document.getElementById('ag-save-staff');
		const saveHoursBtn = document.getElementById('ag-save-hours');
		const modal = document.getElementById('ag-staff-modal');
		const hoursModal = document.getElementById('ag-hours-modal');
		const form = document.getElementById('ag-staff-form');

		if (!modal) return;

		// Add staff button
		[addBtn, addBtnEmpty].forEach(function(btn) {
			if (btn) {
				btn.addEventListener('click', function() {
					resetStaffForm();
					document.getElementById('ag-staff-modal-title').textContent = 'Yeni Personel';
					openModal('ag-staff-modal');
				});
			}
		});

		// Edit staff buttons
		document.querySelectorAll('.ag-edit-staff').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const id = this.dataset.id;
				const staff = getStaffById(id);
				if (staff) {
					fillStaffForm(staff);
					document.getElementById('ag-staff-modal-title').textContent = 'Personeli Düzenle';
					openModal('ag-staff-modal');
				}
			});
		});

		// Edit hours buttons
		document.querySelectorAll('.ag-edit-hours').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const id = this.dataset.id;
				const name = this.dataset.name;
				document.getElementById('hours-staff-id').value = id;
				document.getElementById('ag-hours-modal-title').textContent = name + ' - Çalışma Saatleri';
				openModal('ag-hours-modal');
			});
		});

		// Delete staff buttons
		document.querySelectorAll('.ag-delete-staff').forEach(function(btn) {
			btn.addEventListener('click', function() {
				if (confirm(agAdmin.strings.confirm_delete)) {
					const id = this.dataset.id;
					ajaxRequest('ag_delete_staff', { id: id }, function(response) {
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

		// Save staff
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				const formData = new FormData(form);
				const data = {};
				const services = [];

				formData.forEach(function(value, key) {
					if (key === 'services[]') {
						services.push(value);
					} else {
						data[key] = value;
					}
				});

				data.services = services;

				ajaxRequest('ag_save_staff', data, function(response) {
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
				const hoursForm = document.getElementById('ag-hours-form');
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

				ajaxRequest('ag_save_working_hours', {
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

	function resetStaffForm() {
		const form = document.getElementById('ag-staff-form');
		if (form) {
			form.reset();
			document.getElementById('staff-id').value = '';
		}
	}

	function fillStaffForm(staff) {
		document.getElementById('staff-id').value = staff.id;
		document.getElementById('staff-name').value = staff.name;
		document.getElementById('staff-email').value = staff.email || '';
		document.getElementById('staff-phone').value = staff.phone || '';
		document.getElementById('staff-bio').value = staff.bio || '';
		document.getElementById('staff-status').value = staff.status;

		// Check services
		document.querySelectorAll('input[name="services[]"]').forEach(function(checkbox) {
			checkbox.checked = staff.services && staff.services.includes(checkbox.value);
		});
	}

	function getStaffById(id) {
		if (typeof agStaff !== 'undefined') {
			return agStaff.find(function(s) {
				return s.id == id;
			});
		}
		return null;
	}

	/**
	 * Bookings functionality
	 */
	function initBookings() {
		// Status change
		document.querySelectorAll('.ag-status-select').forEach(function(select) {
			select.addEventListener('change', function() {
				const id = this.dataset.id;
				const status = this.value;

				ajaxRequest('ag_update_booking_status', {
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

		// Add booking button
		const addBookingBtn = document.getElementById('ag-add-booking');
		const addBookingModal = document.getElementById('ag-add-booking-modal');
		const saveBookingBtn = document.getElementById('ag-save-booking');
		const addBookingForm = document.getElementById('ag-add-booking-form');

		if (addBookingBtn && addBookingModal) {
			addBookingBtn.addEventListener('click', function() {
				resetAddBookingForm();
				openModal('ag-add-booking-modal');
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
					showToast('Lütfen tüm zorunlu alanları doldurun.', 'error');
					return;
				}

				saveBookingBtn.disabled = true;
				saveBookingBtn.textContent = agAdmin.strings.saving || 'Kaydediliyor...';

				ajaxRequest('ag_admin_create_booking', data, function(response) {
					saveBookingBtn.disabled = false;
					saveBookingBtn.textContent = 'Randevu Oluştur';

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
		const form = document.getElementById('ag-add-booking-form');
		if (form) {
			form.reset();
			// Set today's date as default
			const dateInput = document.getElementById('booking-date');
			if (dateInput) {
				dateInput.value = new Date().toISOString().split('T')[0];
			}
		}
	}

	/**
	 * Settings functionality
	 */
	function initSettings() {
		const form = document.getElementById('ag-settings-form');
		const saveBtn = document.getElementById('ag-save-settings');

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

			ajaxRequest('ag_save_settings', data, function(response) {
				if (response.success) {
					showToast(response.data);
				} else {
					showToast(response.data, 'error');
				}
			});
		});

		// SMS Enable/Disable toggle
		const smsEnabledCheckbox = document.getElementById('ag_sms_enabled');
		const smsSettings = document.getElementById('ag-sms-settings');

		if (smsEnabledCheckbox && smsSettings) {
			smsEnabledCheckbox.addEventListener('change', function() {
				smsSettings.style.display = this.checked ? 'block' : 'none';
			});
		}

		// SMS Balance Check
		const balanceBtn = document.getElementById('ag-sms-check-balance');
		const balanceResult = document.getElementById('ag-sms-balance-result');

		if (balanceBtn) {
			balanceBtn.addEventListener('click', function() {
				balanceResult.textContent = 'Sorgulanıyor...';
				balanceBtn.disabled = true;

				ajaxRequest('ag_sms_get_balance', {}, function(response) {
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
		const testBtn = document.getElementById('ag-sms-send-test');
		const testPhone = document.getElementById('ag_sms_test_phone');

		if (testBtn && testPhone) {
			testBtn.addEventListener('click', function() {
				const phone = testPhone.value.trim();

				if (!phone) {
					showToast('Lütfen test telefon numarası girin', 'error');
					return;
				}

				testBtn.disabled = true;
				testBtn.textContent = 'Gönderiliyor...';

				ajaxRequest('ag_sms_send_test', { phone: phone }, function(response) {
					testBtn.disabled = false;
					testBtn.innerHTML = '<span class="dashicons dashicons-email"></span> Test SMS Gönder';

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// SMS Templates Save
		const saveTemplatesBtn = document.getElementById('ag-save-sms-templates');

		if (saveTemplatesBtn) {
			saveTemplatesBtn.addEventListener('click', function() {
				const templates = [];
				const templateItems = document.querySelectorAll('.ag-sms-template-item');

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

				ajaxRequest('ag_save_sms_templates', { templates: JSON.stringify(templates) }, function(response) {
					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Email Template Tabs
		const emailTabBtns = document.querySelectorAll('.ag-tab-btn');
		const emailTabContents = document.querySelectorAll('.ag-tab-content');

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
		document.querySelectorAll('.ag-editor-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const command = this.dataset.command;
				const tabContent = this.closest('.ag-tab-content');
				const textarea = tabContent.querySelector('textarea');

				let insertText = '';
				switch(command) {
					case 'bold':
						insertText = '<strong>metin</strong>';
						break;
					case 'italic':
						insertText = '<em>metin</em>';
						break;
					case 'h3':
						insertText = '<h3>Başlık</h3>';
						break;
					case 'p':
						insertText = '<p>Paragraf metni</p>';
						break;
					case 'table':
						insertText = '<table>\n<tr><td><strong>Alan:</strong></td><td>Değer</td></tr>\n</table>';
						break;
					case 'button':
						insertText = '<p style="text-align: center;">\n<a href="{manage_booking_url}" class="button">Buton Metni</a>\n</p>';
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
		document.querySelectorAll('.ag-editor-insert-var').forEach(function(select) {
			select.addEventListener('change', function() {
				if (!this.value) return;

				const tabContent = this.closest('.ag-tab-content');
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
		const saveEmailTemplatesBtn = document.getElementById('ag-save-email-templates');

		if (saveEmailTemplatesBtn) {
			saveEmailTemplatesBtn.addEventListener('click', function() {
				const templates = [];

				document.querySelectorAll('.ag-tab-content').forEach(function(tabContent) {
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
				saveEmailTemplatesBtn.textContent = 'Kaydediliyor...';

				ajaxRequest('ag_save_email_templates', { templates: JSON.stringify(templates) }, function(response) {
					saveEmailTemplatesBtn.disabled = false;
					saveEmailTemplatesBtn.innerHTML = '<span class="dashicons dashicons-saved"></span> Şablonları Kaydet';

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Send Test Email
		const sendTestEmailBtn = document.getElementById('ag-email-send-test');
		const testEmailInput = document.getElementById('ag_email_test_address');

		if (sendTestEmailBtn && testEmailInput) {
			sendTestEmailBtn.addEventListener('click', function() {
				const email = testEmailInput.value.trim();
				const activeTab = document.querySelector('.ag-tab-content.active');
				const templateType = activeTab ? activeTab.dataset.templateType : '';

				if (!email) {
					showToast('Lütfen test e-posta adresi girin', 'error');
					return;
				}

				if (!templateType) {
					showToast('Lütfen bir şablon seçin', 'error');
					return;
				}

				sendTestEmailBtn.disabled = true;
				sendTestEmailBtn.textContent = 'Gönderiliyor...';

				ajaxRequest('ag_email_send_test', { email: email, template_type: templateType }, function(response) {
					sendTestEmailBtn.disabled = false;
					sendTestEmailBtn.innerHTML = '<span class="dashicons dashicons-email"></span> Test Gönder';

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Preview Email Template
		const previewBtn = document.getElementById('ag-preview-email-template');
		const previewModal = document.getElementById('ag-email-preview-modal');
		const previewFrame = document.getElementById('ag-email-preview-frame');

		if (previewBtn && previewModal && previewFrame) {
			previewBtn.addEventListener('click', function() {
				const activeTab = document.querySelector('.ag-tab-content.active');
				const templateType = activeTab ? activeTab.dataset.templateType : '';

				if (!templateType) {
					showToast('Lütfen bir şablon seçin', 'error');
					return;
				}

				previewBtn.disabled = true;
				previewBtn.textContent = 'Yükleniyor...';

				ajaxRequest('ag_email_preview', { template_type: templateType }, function(response) {
					previewBtn.disabled = false;
					previewBtn.innerHTML = '<span class="dashicons dashicons-visibility"></span> Önizleme';

					if (response.success) {
						previewFrame.srcdoc = response.data.html;
						openModal('ag-email-preview-modal');
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
		const calendarEl = document.getElementById('ag-calendar');
		if (!calendarEl) return;

		let currentDate = new Date();
		let currentView = 'month';
		let currentStaff = '';

		const titleEl = document.getElementById('ag-cal-title');
		const prevBtn = document.getElementById('ag-cal-prev');
		const nextBtn = document.getElementById('ag-cal-next');
		const todayBtn = document.getElementById('ag-cal-today');
		const staffSelect = document.getElementById('ag-cal-staff');
		const viewBtns = document.querySelectorAll('.ag-cal-view');

		function renderCalendar() {
			const year = currentDate.getFullYear();
			const month = currentDate.getMonth();

			// Update title
			const monthNames = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
				'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
			titleEl.textContent = monthNames[month] + ' ' + year;

			// Get first day of month
			const firstDay = new Date(year, month, 1);
			const lastDay = new Date(year, month + 1, 0);
			const startDay = firstDay.getDay() || 7; // Monday = 1

			// Build calendar grid
			let html = '<div class="ag-calendar-grid">';

			// Header
			const dayNames = ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'];
			dayNames.forEach(function(day) {
				html += '<div class="ag-calendar-header-cell">' + day + '</div>';
			});

			// Calculate start date (previous month days)
			const startDate = new Date(firstDay);
			startDate.setDate(startDate.getDate() - (startDay - 1));

			// Generate 6 weeks
			for (let i = 0; i < 42; i++) {
				const cellDate = new Date(startDate);
				cellDate.setDate(startDate.getDate() + i);

				const isOtherMonth = cellDate.getMonth() !== month;
				const isToday = cellDate.toDateString() === new Date().toDateString();

				let cellClass = 'ag-calendar-cell';
				if (isOtherMonth) cellClass += ' other-month';
				if (isToday) cellClass += ' today';

				html += '<div class="' + cellClass + '" data-date="' + formatDate(cellDate) + '">';
				html += '<div class="ag-calendar-day">' + cellDate.getDate() + '</div>';
				html += '</div>';
			}

			html += '</div>';
			calendarEl.innerHTML = html;

			// Load events
			loadEvents(year, month);
		}

		function loadEvents(year, month) {
			const startDate = new Date(year, month, 1);
			const endDate = new Date(year, month + 1, 0);

			ajaxRequest('ag_get_bookings', {
				start: formatDate(startDate),
				end: formatDate(endDate),
				staff_id: currentStaff
			}, function(response) {
				if (response.success && response.data) {
					renderEvents(response.data);
				}
			});
		}

		function renderEvents(events) {
			events.forEach(function(event) {
				const date = event.start.split('T')[0];
				const cell = document.querySelector('.ag-calendar-cell[data-date="' + date + '"]');

				if (cell) {
					const eventEl = document.createElement('div');
					eventEl.className = 'ag-calendar-event';
					eventEl.style.backgroundColor = event.backgroundColor;
					eventEl.textContent = event.title;
					eventEl.dataset.id = event.id;

					eventEl.addEventListener('click', function() {
						// Show booking detail modal
						showBookingDetail(event);
					});

					cell.appendChild(eventEl);
				}
			});
		}

		function showBookingDetail(event) {
			const modal = document.getElementById('ag-booking-modal');
			const detail = document.getElementById('ag-booking-detail');

			if (modal && detail) {
				const props = event.extendedProps;
				detail.innerHTML = `
					<div class="ag-form-group">
						<label>Müşteri</label>
						<p><strong>${props.customer_name}</strong><br>
						${props.customer_email}<br>
						${props.customer_phone || '-'}</p>
					</div>
					<div class="ag-form-group">
						<label>Hizmet</label>
						<p>${props.service_name}</p>
					</div>
					<div class="ag-form-group">
						<label>Personel</label>
						<p>${props.staff_name}</p>
					</div>
					<div class="ag-form-group">
						<label>Durum</label>
						<p><span class="ag-status ag-status-${props.status}">${props.status}</span></p>
					</div>
					<div class="ag-form-group">
						<label>Ücret</label>
						<p>${props.price} ${agAdmin.currency.symbol}</p>
					</div>
				`;

				openModal('ag-booking-modal');
			}
		}

		function formatDate(date) {
			const year = date.getFullYear();
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const day = String(date.getDate()).padStart(2, '0');
			return year + '-' + month + '-' + day;
		}

		// Navigation
		if (prevBtn) {
			prevBtn.addEventListener('click', function() {
				currentDate.setMonth(currentDate.getMonth() - 1);
				renderCalendar();
			});
		}

		if (nextBtn) {
			nextBtn.addEventListener('click', function() {
				currentDate.setMonth(currentDate.getMonth() + 1);
				renderCalendar();
			});
		}

		if (todayBtn) {
			todayBtn.addEventListener('click', function() {
				currentDate = new Date();
				renderCalendar();
			});
		}

		if (staffSelect) {
			staffSelect.addEventListener('change', function() {
				currentStaff = this.value;
				renderCalendar();
			});
		}

		// View buttons
		viewBtns.forEach(function(btn) {
			btn.addEventListener('click', function() {
				viewBtns.forEach(function(b) {
					b.classList.remove('active');
				});
				this.classList.add('active');
				currentView = this.dataset.view;
				renderCalendar();
			});
		});

		// Initial render
		renderCalendar();
	}

	/**
	 * Copy buttons
	 */
	function initCopyButtons() {
		document.querySelectorAll('.ag-copy-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const text = this.dataset.copy;

				if (navigator.clipboard) {
					navigator.clipboard.writeText(text).then(function() {
						showToast('Kopyalandı!');
					});
				} else {
					// Fallback
					const textarea = document.createElement('textarea');
					textarea.value = text;
					document.body.appendChild(textarea);
					textarea.select();
					document.execCommand('copy');
					document.body.removeChild(textarea);
					showToast('Kopyalandı!');
				}
			});
		});
	}

	/**
	 * Email Templates Page
	 */
	function initEmailTemplates() {
		const templateList = document.querySelector('.ag-template-list');
		if (!templateList) return;

		// Template selection
		const templateItems = document.querySelectorAll('.ag-template-item');
		const templateEditors = document.querySelectorAll('.ag-template-editor');

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
		document.querySelectorAll('.ag-toolbar-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const command = this.dataset.command;
				const editor = this.closest('.ag-template-editor');
				const textarea = editor.querySelector('.ag-template-content');

				let insertText = '';
				switch(command) {
					case 'bold':
						insertText = '<strong>metin</strong>';
						break;
					case 'italic':
						insertText = '<em>metin</em>';
						break;
					case 'link':
						insertText = '<a href="URL">link metni</a>';
						break;
					case 'h3':
						insertText = '<h3>Başlık</h3>';
						break;
					case 'p':
						insertText = '<p>Paragraf metni</p>';
						break;
					case 'table':
						insertText = '<table>\n<tr><td><strong>Hizmet(ler):</strong></td><td>{services_list}</td></tr>\n<tr><td><strong>Personel:</strong></td><td>{staff_name}</td></tr>\n<tr><td><strong>Tarih:</strong></td><td>{booking_date}</td></tr>\n<tr><td><strong>Saat:</strong></td><td>{booking_time}</td></tr>\n<tr><td><strong>Süre:</strong></td><td>{total_duration}</td></tr>\n<tr><td><strong>Ücret:</strong></td><td>{price}</td></tr>\n</table>';
						break;
					case 'button':
						insertText = '<p style="text-align: center;">\n<a href="{manage_booking_url}" class="button">Randevumu Görüntüle</a>\n</p>';
						break;
				}

				if (insertText && textarea) {
					insertAtCursor(textarea, insertText);
				}
			});
		});

		// Variable dropdown
		document.querySelectorAll('.ag-insert-variable').forEach(function(select) {
			select.addEventListener('change', function() {
				if (!this.value) return;

				const editor = this.closest('.ag-template-editor');
				const textarea = editor.querySelector('.ag-template-content');

				if (textarea) {
					insertAtCursor(textarea, this.value);
				}

				this.value = '';
			});
		});

		// Clickable variable codes
		document.querySelectorAll('.ag-variable-group code').forEach(function(code) {
			code.addEventListener('click', function() {
				const editor = this.closest('.ag-template-editor');
				const textarea = editor.querySelector('.ag-template-content');

				if (textarea) {
					insertAtCursor(textarea, this.textContent);
					showToast('Değişken eklendi');
				}
			});
		});

		// Save template
		document.querySelectorAll('.ag-save-template').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const templateId = this.dataset.templateId;
				const editor = document.getElementById('editor-' + templateId);

				const subject = editor.querySelector('.ag-template-subject').value;
				const content = editor.querySelector('.ag-template-content').value;
				const isActive = editor.querySelector('.ag-template-active').checked;

				btn.disabled = true;
				btn.innerHTML = '<span class="dashicons dashicons-update"></span> Kaydediliyor...';

				ajaxRequest('ag_save_email_templates', {
					templates: JSON.stringify([{
						id: templateId,
						subject: subject,
						content: content,
						is_active: isActive
					}])
				}, function(response) {
					btn.disabled = false;
					btn.innerHTML = '<span class="dashicons dashicons-saved"></span> Şablonu Kaydet';

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
		document.querySelectorAll('.ag-preview-template').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const templateType = this.dataset.templateType;

				btn.disabled = true;
				btn.innerHTML = '<span class="dashicons dashicons-update"></span> Yükleniyor...';

				ajaxRequest('ag_email_preview', { template_type: templateType }, function(response) {
					btn.disabled = false;
					btn.innerHTML = '<span class="dashicons dashicons-visibility"></span> Önizleme';

					if (response.success) {
						const previewFrame = document.getElementById('ag-email-preview-frame');
						previewFrame.srcdoc = response.data.html;
						openModal('ag-email-preview-modal');
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});

		// Send test email
		document.querySelectorAll('.ag-send-test-email').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const templateType = this.dataset.templateType;
				const editor = this.closest('.ag-template-editor');
				const emailInput = editor.querySelector('.ag-test-email-input');
				const email = emailInput.value.trim();

				if (!email) {
					showToast('Lütfen test e-posta adresi girin', 'error');
					emailInput.focus();
					return;
				}

				btn.disabled = true;
				btn.innerHTML = '<span class="dashicons dashicons-update"></span> Gönderiliyor...';

				ajaxRequest('ag_email_send_test', {
					email: email,
					template_type: templateType
				}, function(response) {
					btn.disabled = false;
					btn.innerHTML = '<span class="dashicons dashicons-email"></span> Test Gönder';

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		});

		// Device preview buttons
		document.querySelectorAll('.ag-device-btn').forEach(function(btn) {
			btn.addEventListener('click', function() {
				const width = this.dataset.width;
				const previewFrame = document.getElementById('ag-email-preview-frame');

				document.querySelectorAll('.ag-device-btn').forEach(function(b) {
					b.classList.remove('active');
				});
				this.classList.add('active');

				previewFrame.style.width = width;
			});
		});

		// Save email settings
		const saveSettingsBtn = document.getElementById('ag-save-email-settings');
		if (saveSettingsBtn) {
			saveSettingsBtn.addEventListener('click', function() {
				const data = {
					ag_email_logo_url: document.getElementById('ag_email_logo_url').value,
					ag_email_primary_color: document.getElementById('ag_email_primary_color').value,
					ag_email_reminder_enabled: document.getElementById('ag_email_reminder_enabled').checked ? 'yes' : 'no',
					ag_email_reminder_hours: document.getElementById('ag_email_reminder_hours').value
				};

				saveSettingsBtn.disabled = true;
				saveSettingsBtn.innerHTML = '<span class="dashicons dashicons-update"></span> Kaydediliyor...';

				ajaxRequest('ag_save_email_settings', data, function(response) {
					saveSettingsBtn.disabled = false;
					saveSettingsBtn.innerHTML = '<span class="dashicons dashicons-saved"></span> Ayarları Kaydet';

					if (response.success) {
						showToast(response.data);
					} else {
						showToast(response.data, 'error');
					}
				});
			});
		}

		// Color picker sync
		const colorPicker = document.getElementById('ag_email_primary_color');
		const colorText = document.getElementById('ag_email_primary_color_text');

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
			const item = document.querySelector('.ag-template-item[data-template-id="' + templateId + '"]');
			if (item) {
				const statusSpan = item.querySelector('.ag-template-item-status');
				if (statusSpan) {
					statusSpan.className = 'ag-template-item-status ' + (isActive ? 'active' : 'inactive');
					statusSpan.textContent = isActive ? 'Aktif' : 'Pasif';
				}
			}
		}
	}

	/**
	 * Staff Schedule - Çalışma Takvimi
	 */
	function initStaffSchedule() {
		const schedulePage = document.querySelector('.ag-schedule-page');
		if (!schedulePage) return;

		const staffSelect = document.getElementById('ag-schedule-staff');
		const daysList = document.getElementById('ag-days-list');
		const calendarDays = document.getElementById('ag-calendar-days');
		const holidaysList = document.getElementById('ag-holidays-list');
		const saveBtn = document.getElementById('ag-save-schedule');
		const holidayModal = document.getElementById('ag-holiday-modal');

		if (!staffSelect) return;

		let currentStaffId = staffSelect.value;
		let currentMonth = new Date().getMonth();
		let currentYear = new Date().getFullYear();
		let holidays = [];
		let workingHours = [];
		let breaks = [];

		// Türkçe ay isimleri
		const monthNames = [
			'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
			'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'
		];

		// İlk yükleme
		loadStaffSchedule(currentStaffId);

		// Personel değiştiğinde
		staffSelect.addEventListener('change', function() {
			currentStaffId = this.value;
			loadStaffSchedule(currentStaffId);
		});

		// Gün başlıklarına tıklama (expand/collapse)
		if (daysList) {
			daysList.addEventListener('click', function(e) {
				const dayHeader = e.target.closest('.ag-day-header');
				if (dayHeader) {
					const dayItem = dayHeader.closest('.ag-day-item');
					dayItem.classList.toggle('expanded');
				}
			});

			// Çalışma checkbox değişikliği
			daysList.addEventListener('change', function(e) {
				if (e.target.classList.contains('ag-day-working')) {
					const dayNum = e.target.dataset.day;
					const dayItem = e.target.closest('.ag-day-item');
					updateDayStatus(dayItem, e.target.checked);
				}
			});

			// Mola ekleme butonu
			daysList.addEventListener('click', function(e) {
				const addBtn = e.target.closest('.ag-add-break-btn');
				if (addBtn) {
					e.stopPropagation();
					const dayNum = addBtn.dataset.day;
					addBreakRow(dayNum);
				}

				// Mola silme butonu
				const removeBtn = e.target.closest('.ag-remove-break-btn');
				if (removeBtn) {
					removeBtn.closest('.ag-break-item').remove();
				}
			});
		}

		// Saat input formatlaması (24 saat - HH:MM)
		document.addEventListener('input', function(e) {
			if (!e.target.classList.contains('ag-time-input')) return;

			let value = e.target.value.replace(/[^\d]/g, ''); // Sadece rakamlar

			if (value.length >= 2) {
				let hours = parseInt(value.substring(0, 2));
				if (hours > 23) hours = 23;
				value = String(hours).padStart(2, '0') + ':' + value.substring(2);
			}

			if (value.length > 5) {
				value = value.substring(0, 5);
			}

			// Dakika kontrolü
			if (value.length === 5) {
				let minutes = parseInt(value.substring(3, 5));
				if (minutes > 59) minutes = 59;
				value = value.substring(0, 3) + String(minutes).padStart(2, '0');
			}

			e.target.value = value;
		});

		// Kaydet butonu
		if (saveBtn) {
			saveBtn.addEventListener('click', function() {
				saveStaffSchedule();
			});
		}

		// Takvim navigasyonu
		const prevBtn = document.querySelector('.ag-calendar-prev');
		const nextBtn = document.querySelector('.ag-calendar-next');

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

		// Takvim gününe tıklama - direkt toggle
		if (calendarDays) {
			calendarDays.addEventListener('click', function(e) {
				const dayEl = e.target.closest('.ag-calendar-day');
				if (!dayEl || dayEl.classList.contains('other-month') || dayEl.classList.contains('past')) return;

				const date = dayEl.dataset.date;
				if (!date) return;

				if (dayEl.classList.contains('holiday')) {
					// İzni sil (direkt, onay sormadan)
					deleteHoliday(date);
					dayEl.classList.remove('holiday');
				} else {
					// İzin ekle (direkt, popup olmadan)
					addHolidayDirect(date);
					dayEl.classList.add('holiday');
				}
			});
		}

		// Modal kapatma
		if (holidayModal) {
			holidayModal.querySelector('.ag-modal-close').addEventListener('click', closeHolidayModal);
			holidayModal.querySelector('.ag-modal-cancel').addEventListener('click', closeHolidayModal);
			holidayModal.querySelector('.ag-modal-overlay').addEventListener('click', closeHolidayModal);

			// İzin ekle butonu
			document.getElementById('ag-add-holiday-btn').addEventListener('click', function() {
				const date = document.getElementById('ag-holiday-date').value;
				const reason = document.getElementById('ag-holiday-reason').value;
				addHoliday(date, reason);
			});
		}

		/**
		 * Personel programını yükle
		 */
		function loadStaffSchedule(staffId) {
			ajaxRequest('ag_get_staff_schedule', { staff_id: staffId }, function(response) {
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
		 * Çalışma saatlerini render et
		 */
		function renderWorkingHours() {
			if (!daysList) return;

			// Her gün için varsayılan değerler
			const defaultHours = {};
			for (let i = 0; i <= 6; i++) {
				defaultHours[i] = {
					is_working: i !== 0 ? 1 : 0, // Pazar kapalı
					start_time: '09:00',
					end_time: '18:00'
				};
			}

			// Veritabanındaki değerleri uygula
			workingHours.forEach(function(wh) {
				defaultHours[wh.day_of_week] = {
					is_working: parseInt(wh.is_working),
					start_time: wh.start_time.substring(0, 5),
					end_time: wh.end_time.substring(0, 5)
				};
			});

			// DOM'u güncelle
			for (let day = 0; day <= 6; day++) {
				const dayItem = daysList.querySelector('.ag-day-item[data-day="' + day + '"]');
				if (!dayItem) continue;

				const checkbox = dayItem.querySelector('.ag-day-working');
				const startInput = dayItem.querySelector('.ag-time-start');
				const endInput = dayItem.querySelector('.ag-time-end');
				const breaksList = dayItem.querySelector('.ag-breaks-list');

				const hours = defaultHours[day];

				checkbox.checked = hours.is_working === 1;
				startInput.value = hours.start_time;
				endInput.value = hours.end_time;

				updateDayStatus(dayItem, hours.is_working === 1);

				// Molaları temizle ve yeniden ekle
				breaksList.innerHTML = '';
				const dayBreaks = breaks.filter(b => parseInt(b.day_of_week) === day);
				dayBreaks.forEach(function(brk) {
					addBreakRow(day, brk.start_time.substring(0, 5), brk.end_time.substring(0, 5));
				});
			}
		}

		/**
		 * Gün durumunu güncelle
		 */
		function updateDayStatus(dayItem, isWorking) {
			const statusSpan = dayItem.querySelector('.ag-day-status');
			const content = dayItem.querySelector('.ag-day-content');
			const startInput = dayItem.querySelector('.ag-time-start');
			const endInput = dayItem.querySelector('.ag-time-end');

			if (isWorking) {
				statusSpan.textContent = startInput.value + ' - ' + endInput.value;
				statusSpan.className = 'ag-day-status working';
				content.style.display = '';
			} else {
				statusSpan.textContent = 'Çalışmıyor';
				statusSpan.className = 'ag-day-status closed';
			}
		}

		/**
		 * Mola satırı ekle
		 */
		function addBreakRow(dayNum, startTime, endTime) {
			const breaksList = daysList.querySelector('.ag-breaks-list[data-day="' + dayNum + '"]');
			if (!breaksList) return;

			const breakItem = document.createElement('div');
			breakItem.className = 'ag-break-item';
			breakItem.innerHTML = `
				<input type="text" class="ag-time-input ag-break-start" value="${startTime || '12:00'}" placeholder="12:00" maxlength="5">
				<span class="ag-time-separator">-</span>
				<input type="text" class="ag-time-input ag-break-end" value="${endTime || '13:00'}" placeholder="13:00" maxlength="5">
				<button type="button" class="ag-remove-break-btn">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			`;

			breaksList.appendChild(breakItem);
		}

		/**
		 * Programı kaydet
		 */
		function saveStaffSchedule() {
			const workingHoursData = [];
			const breaksData = [];

			// Her gün için verileri topla
			for (let day = 0; day <= 6; day++) {
				const dayItem = daysList.querySelector('.ag-day-item[data-day="' + day + '"]');
				if (!dayItem) continue;

				const checkbox = dayItem.querySelector('.ag-day-working');
				const startInput = dayItem.querySelector('.ag-time-start');
				const endInput = dayItem.querySelector('.ag-time-end');

				workingHoursData.push({
					day_of_week: day,
					is_working: checkbox.checked ? 1 : 0,
					start_time: startInput.value,
					end_time: endInput.value
				});

				// Molaları topla
				const breakItems = dayItem.querySelectorAll('.ag-break-item');
				breakItems.forEach(function(breakItem) {
					const breakStart = breakItem.querySelector('.ag-break-start').value;
					const breakEnd = breakItem.querySelector('.ag-break-end').value;
					if (breakStart && breakEnd) {
						breaksData.push({
							day_of_week: day,
							start_time: breakStart,
							end_time: breakEnd
						});
					}
				});
			}

			ajaxRequest('ag_save_staff_schedule', {
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
		 * Takvimi render et
		 */
		function renderCalendar() {
			if (!calendarDays) return;

			// Ay/yıl başlığını güncelle
			const monthYearSpan = document.querySelector('.ag-calendar-month-year');
			if (monthYearSpan) {
				monthYearSpan.textContent = monthNames[currentMonth] + ' ' + currentYear;
			}

			// Ayın ilk günü ve gün sayısı
			const firstDay = new Date(currentYear, currentMonth, 1);
			const lastDay = new Date(currentYear, currentMonth + 1, 0);
			const daysInMonth = lastDay.getDate();

			// İlk günün haftanın hangi günü olduğu (Pazartesi = 0)
			let startDay = firstDay.getDay() - 1;
			if (startDay < 0) startDay = 6;

			// Bugün
			const today = new Date();
			today.setHours(0, 0, 0, 0);
			const todayStr = formatDate(today);

			// Tatil tarihlerini set'e çevir
			const holidayDates = new Set(holidays.map(h => h.date));

			let html = '';

			// Önceki ayın günleri
			const prevMonth = new Date(currentYear, currentMonth, 0);
			const prevMonthDays = prevMonth.getDate();
			for (let i = startDay - 1; i >= 0; i--) {
				const day = prevMonthDays - i;
				html += `<div class="ag-calendar-day other-month">${day}</div>`;
			}

			// Bu ayın günleri
			for (let day = 1; day <= daysInMonth; day++) {
				const date = new Date(currentYear, currentMonth, day);
				const dateStr = formatDate(date);
				let classes = 'ag-calendar-day';

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

			// Sonraki ayın günleri (42 hücreyi doldurmak için)
			const totalCells = startDay + daysInMonth;
			const remainingCells = totalCells <= 35 ? 35 - totalCells : 42 - totalCells;
			for (let i = 1; i <= remainingCells; i++) {
				html += `<div class="ag-calendar-day other-month">${i}</div>`;
			}

			calendarDays.innerHTML = html;
		}

		/**
		 * İzin listesini render et
		 */
		function renderHolidaysList() {
			if (!holidaysList) return;

			if (holidays.length === 0) {
				holidaysList.innerHTML = `
					<div class="ag-holidays-empty">
						<span class="dashicons dashicons-yes-alt"></span>
						<p>Kayıtlı izin bulunmuyor</p>
					</div>
				`;
				return;
			}

			let html = '';
			holidays.forEach(function(holiday) {
				const date = new Date(holiday.date);
				const formattedDate = date.toLocaleDateString('tr-TR', {
					day: 'numeric',
					month: 'long',
					year: 'numeric'
				});

				html += `
					<div class="ag-holiday-item" data-date="${holiday.date}">
						<span class="ag-holiday-date">${formattedDate}</span>
						<span class="ag-holiday-reason">${holiday.reason || '-'}</span>
						<button type="button" class="ag-holiday-delete" data-date="${holiday.date}">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</div>
				`;
			});

			holidaysList.innerHTML = html;

			// Silme butonları
			holidaysList.querySelectorAll('.ag-holiday-delete').forEach(function(btn) {
				btn.addEventListener('click', function() {
					const date = this.dataset.date;
					if (confirm('Bu izni silmek istiyor musunuz?')) {
						deleteHoliday(date);
					}
				});
			});
		}

		/**
		 * İzin ekleme modal'ını aç
		 */
		function openHolidayModal(date) {
			if (!holidayModal) return;

			const dateObj = new Date(date);
			const formattedDate = dateObj.toLocaleDateString('tr-TR', {
				weekday: 'long',
				day: 'numeric',
				month: 'long',
				year: 'numeric'
			});

			document.getElementById('ag-holiday-date').value = date;
			document.getElementById('ag-holiday-date-text').textContent = formattedDate;
			document.getElementById('ag-holiday-reason').value = '';

			holidayModal.classList.add('active');
		}

		/**
		 * İzin ekleme modal'ını kapat
		 */
		function closeHolidayModal() {
			if (holidayModal) {
				holidayModal.classList.remove('active');
			}
		}

		/**
		 * İzin ekle (modal ile)
		 */
		function addHoliday(date, reason) {
			ajaxRequest('ag_add_staff_holiday', {
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
		 * İzin ekle (direkt, popup olmadan)
		 */
		function addHolidayDirect(date) {
			// Holidays array'e ekle (UI hızlı güncelleme için)
			holidays.push({ date: date, reason: '' });
			renderHolidaysList();

			ajaxRequest('ag_add_staff_holiday', {
				staff_id: currentStaffId,
				date: date,
				reason: ''
			}, function(response) {
				if (response.success) {
					showToast('İzin eklendi');
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
		 * İzin sil
		 */
		function deleteHoliday(date) {
			// Holidays array'den çıkar (UI hızlı güncelleme için)
			const originalHolidays = [...holidays];
			holidays = holidays.filter(h => h.date !== date);
			renderHolidaysList();

			ajaxRequest('ag_delete_staff_holiday', {
				staff_id: currentStaffId,
				date: date
			}, function(response) {
				if (response.success) {
					showToast('İzin silindi');
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

})();
