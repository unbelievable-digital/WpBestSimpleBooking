/**
 * Booking Management Page
 *
 * @package Unbelievable_Salon_Booking
 */

/* global unbsbManageBooking */

(function() {
	'use strict';

	if (typeof unbsbManageBooking === 'undefined') return;

	var token = unbsbManageBooking.token;
	var restUrl = unbsbManageBooking.restUrl;
	var strings = unbsbManageBooking.strings;

	/**
	 * Format time for display based on user's time format setting.
	 */
	function formatTime(time24) {
		if (!time24) return '';
		var timeFormat = unbsbManageBooking.timeFormat || 'H:i';
		var parts = time24.split(':');
		var hours = parseInt(parts[0], 10);
		var minutes = parts[1] || '00';

		if ('g:i A' === timeFormat || 'g:i a' === timeFormat) {
			var ampm = hours >= 12 ? 'PM' : 'AM';
			var h = hours % 12;
			if (0 === h) h = 12;
			return h + ':' + minutes + ' ' + ampm;
		}

		return parts[0] + ':' + minutes;
	}

	// Modal open/close.
	var rescheduleBtn = document.getElementById('unbsb-open-reschedule');
	var cancelBtn = document.getElementById('unbsb-open-cancel');
	var rescheduleModal = document.getElementById('unbsb-reschedule-modal');
	var cancelModal = document.getElementById('unbsb-cancel-modal');

	function openModal(modal) {
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

	if (rescheduleBtn) {
		rescheduleBtn.addEventListener('click', function() {
			openModal(rescheduleModal);
		});
	}

	if (cancelBtn) {
		cancelBtn.addEventListener('click', function() {
			openModal(cancelModal);
		});
	}

	// Modal close buttons.
	document.querySelectorAll('.unbsb-modal-close, .unbsb-modal-cancel, .unbsb-modal-overlay').forEach(function(el) {
		el.addEventListener('click', function() {
			closeModal(rescheduleModal);
			closeModal(cancelModal);
		});
	});

	// ESC to close modal.
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') {
			closeModal(rescheduleModal);
			closeModal(cancelModal);
		}
	});

	// Load available slots on date change.
	var dateInput = document.getElementById('unbsb-reschedule-date');
	var timeSelect = document.getElementById('unbsb-reschedule-time');

	if (dateInput && timeSelect) {
		dateInput.addEventListener('change', function() {
			var date = this.value;
			if (!date) return;

			timeSelect.disabled = true;
			timeSelect.innerHTML = '<option value="">' + strings.loading + '</option>';

			fetch(restUrl + 'bookings/' + token + '/available-slots?date=' + date)
				.then(function(response) { return response.json(); })
				.then(function(data) {
					if (data.success && data.data.length > 0) {
						timeSelect.innerHTML = '<option value="">' + strings.selectTime + '</option>';
						data.data.forEach(function(slot) {
							var option = document.createElement('option');
							option.value = slot.start;
							option.textContent = formatTime(slot.start) + ' - ' + formatTime(slot.end);
							timeSelect.appendChild(option);
						});
						timeSelect.disabled = false;
					} else {
						timeSelect.innerHTML = '<option value="">' + strings.noSlots + '</option>';
					}
				})
				.catch(function() {
					timeSelect.innerHTML = '<option value="">' + strings.error + '</option>';
				});
		});
	}

	// Reschedule form.
	var rescheduleForm = document.getElementById('unbsb-reschedule-form');
	if (rescheduleForm) {
		rescheduleForm.addEventListener('submit', function(e) {
			e.preventDefault();

			var submitBtn = document.getElementById('unbsb-submit-reschedule');
			submitBtn.classList.add('loading');

			var formData = {
				new_date: dateInput.value,
				new_time: timeSelect.value
			};

			fetch(restUrl + 'bookings/' + token + '/reschedule', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(formData)
			})
			.then(function(response) { return response.json(); })
			.then(function(data) {
				submitBtn.classList.remove('loading');
				if (data.success) {
					alert(data.message || strings.rescheduled);
					window.location.reload();
				} else {
					alert(data.message || strings.error);
				}
			})
			.catch(function() {
				submitBtn.classList.remove('loading');
				alert(strings.error);
			});
		});
	}

	// Cancel form.
	var cancelForm = document.getElementById('unbsb-cancel-form');
	if (cancelForm) {
		cancelForm.addEventListener('submit', function(e) {
			e.preventDefault();

			var submitBtn = document.getElementById('unbsb-submit-cancel');
			submitBtn.classList.add('loading');

			var reason = document.getElementById('unbsb-cancel-reason').value;

			fetch(restUrl + 'bookings/' + token + '/cancel', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({ reason: reason })
			})
			.then(function(response) { return response.json(); })
			.then(function(data) {
				submitBtn.classList.remove('loading');
				if (data.success) {
					alert(data.message || strings.cancelled);
					window.location.reload();
				} else {
					alert(data.message || strings.error);
				}
			})
			.catch(function() {
				submitBtn.classList.remove('loading');
				alert(strings.error);
			});
		});
	}
})();
