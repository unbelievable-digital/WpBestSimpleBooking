<?php
/**
 * Booking Manager class - Cancellation and rescheduling operations
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking Manager class
 */
class UNBSB_Booking_Manager {

	/**
	 * Booking model instance
	 *
	 * @var UNBSB_Booking
	 */
	private $booking_model;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->booking_model = new UNBSB_Booking();
	}

	/**
	 * Get booking by token
	 *
	 * @param string $token Booking token.
	 *
	 * @return object|false Booking object or false.
	 */
	public function get_booking_by_token( $token ) {
		if ( empty( $token ) ) {
			return false;
		}

		return $this->booking_model->get_by_token( $token );
	}

	/**
	 * Check if booking can be cancelled
	 *
	 * @param object $booking Booking object.
	 *
	 * @return array ['can_cancel' => bool, 'reason' => string]
	 */
	public function can_cancel( $booking ) {
		// Is cancellation feature enabled?
		if ( 'yes' !== get_option( 'unbsb_allow_cancel', 'yes' ) ) {
			return array(
				'can_cancel' => false,
				'reason'     => __( 'Booking cancellation is disabled.', 'unbelievable-salon-booking' ),
			);
		}

		// Is booking already cancelled?
		if ( 'cancelled' === $booking->status ) {
			return array(
				'can_cancel' => false,
				'reason'     => __( 'This booking has already been cancelled.', 'unbelievable-salon-booking' ),
			);
		}

		// Is booking completed?
		if ( 'completed' === $booking->status ) {
			return array(
				'can_cancel' => false,
				'reason'     => __( 'Completed bookings cannot be cancelled.', 'unbelievable-salon-booking' ),
			);
		}

		// Has cancellation deadline passed?
		$deadline_hours = absint( get_option( 'unbsb_cancel_deadline_hours', 24 ) );
		$deadline_check = $this->check_deadline( $booking, $deadline_hours );

		if ( ! $deadline_check['passed'] ) {
			return array(
				'can_cancel' => false,
				'reason'     => sprintf(
					/* translators: %d: Number of hours */
					__( 'Cannot cancel because less than %d hours remain until the booking.', 'unbelievable-salon-booking' ),
					$deadline_hours
				),
			);
		}

		return array(
			'can_cancel' => true,
			'reason'     => '',
		);
	}

	/**
	 * Check if booking can be rescheduled
	 *
	 * @param object $booking Booking object.
	 *
	 * @return array ['can_reschedule' => bool, 'reason' => string]
	 */
	public function can_reschedule( $booking ) {
		// Is rescheduling feature enabled?
		if ( 'yes' !== get_option( 'unbsb_allow_reschedule', 'yes' ) ) {
			return array(
				'can_reschedule' => false,
				'reason'         => __( 'Booking rescheduling is disabled.', 'unbelievable-salon-booking' ),
			);
		}

		// Is booking cancelled?
		if ( 'cancelled' === $booking->status ) {
			return array(
				'can_reschedule' => false,
				'reason'         => __( 'Cancelled bookings cannot be rescheduled.', 'unbelievable-salon-booking' ),
			);
		}

		// Is booking completed?
		if ( 'completed' === $booking->status ) {
			return array(
				'can_reschedule' => false,
				'reason'         => __( 'Completed bookings cannot be rescheduled.', 'unbelievable-salon-booking' ),
			);
		}

		// Has maximum reschedule count been reached?
		$max_reschedules  = absint( get_option( 'unbsb_max_reschedules', 2 ) );
		$reschedule_count = isset( $booking->reschedule_count ) ? absint( $booking->reschedule_count ) : 0;

		if ( $reschedule_count >= $max_reschedules ) {
			return array(
				'can_reschedule' => false,
				'reason'         => sprintf(
					/* translators: %d: Maximum number of reschedules */
					__( 'A booking can be rescheduled at most %d times.', 'unbelievable-salon-booking' ),
					$max_reschedules
				),
			);
		}

		// Has reschedule deadline passed?
		$deadline_hours = absint( get_option( 'unbsb_reschedule_deadline_hours', 24 ) );
		$deadline_check = $this->check_deadline( $booking, $deadline_hours );

		if ( ! $deadline_check['passed'] ) {
			return array(
				'can_reschedule' => false,
				'reason'         => sprintf(
					/* translators: %d: Number of hours */
					__( 'Cannot reschedule because less than %d hours remain until the booking.', 'unbelievable-salon-booking' ),
					$deadline_hours
				),
			);
		}

		return array(
			'can_reschedule' => true,
			'reason'         => '',
			'remaining'      => $max_reschedules - $reschedule_count,
		);
	}

	/**
	 * Deadline check
	 *
	 * @param object $booking       Booking object.
	 * @param int    $deadline_hours Deadline in hours.
	 *
	 * @return array ['passed' => bool, 'hours_left' => int]
	 */
	private function check_deadline( $booking, $deadline_hours ) {
		$timezone = wp_timezone_string();
		if ( empty( $timezone ) ) {
			$timezone = 'Europe/Istanbul';
		}

		try {
			$tz               = new DateTimeZone( $timezone );
			$booking_datetime = new DateTime( $booking->booking_date . ' ' . $booking->start_time, $tz );
			$now              = new DateTime( 'now', $tz );

			$diff       = $booking_datetime->getTimestamp() - $now->getTimestamp();
			$hours_left = floor( $diff / 3600 );

			return array(
				'passed'     => $hours_left >= $deadline_hours,
				'hours_left' => max( 0, $hours_left ),
			);
		} catch ( Exception $e ) {
			// Allow on error.
			return array(
				'passed'     => true,
				'hours_left' => 999,
			);
		}
	}

	/**
	 * Cancel booking
	 *
	 * @param string $token  Booking token.
	 * @param string $reason Cancellation reason (optional).
	 *
	 * @return array ['success' => bool, 'message' => string]
	 */
	public function cancel_booking( $token, $reason = '' ) {
		$booking = $this->get_booking_by_token( $token );

		if ( ! $booking ) {
			return array(
				'success' => false,
				'message' => __( 'Booking not found.', 'unbelievable-salon-booking' ),
			);
		}

		$can_cancel = $this->can_cancel( $booking );

		if ( ! $can_cancel['can_cancel'] ) {
			return array(
				'success' => false,
				'message' => $can_cancel['reason'],
			);
		}

		// Add cancellation reason to internal notes.
		if ( ! empty( $reason ) ) {
			$internal_notes  = $booking->internal_notes ?? '';
			$internal_notes .= "\n" . sprintf(
				/* translators: 1: Date/time, 2: Cancellation reason */
				__( '[%1$s] Cancelled by customer. Reason: %2$s', 'unbelievable-salon-booking' ),
				current_time( 'mysql' ),
				sanitize_text_field( $reason )
			);

			$this->booking_model->update(
				$booking->id,
				array(
					'internal_notes' => $internal_notes,
				)
			);
		}

		// Update status.
		$result = $this->booking_model->update_status( $booking->id, 'cancelled' );

		if ( $result ) {
			/**
			 * Fired when booking is cancelled by customer.
			 *
			 * @param int    $booking_id Booking ID.
			 * @param object $booking    Booking object.
			 * @param string $reason     Cancellation reason.
			 */
			do_action( 'unbsb_booking_cancelled_by_customer', $booking->id, $booking, $reason );

			return array(
				'success' => true,
				'message' => __( 'Your booking has been cancelled successfully.', 'unbelievable-salon-booking' ),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'An error occurred while cancelling the booking.', 'unbelievable-salon-booking' ),
		);
	}

	/**
	 * Reschedule booking
	 *
	 * @param string $token        Booking token.
	 * @param string $new_date     New date (Y-m-d).
	 * @param string $new_time     New time (H:i).
	 * @param int    $new_staff_id New staff ID (optional).
	 *
	 * @return array ['success' => bool, 'message' => string, 'booking' => object|null]
	 */
	public function reschedule_booking( $token, $new_date, $new_time, $new_staff_id = null ) {
		$booking = $this->get_booking_by_token( $token );

		if ( ! $booking ) {
			return array(
				'success' => false,
				'message' => __( 'Booking not found.', 'unbelievable-salon-booking' ),
				'booking' => null,
			);
		}

		$can_reschedule = $this->can_reschedule( $booking );

		if ( ! $can_reschedule['can_reschedule'] ) {
			return array(
				'success' => false,
				'message' => $can_reschedule['reason'],
				'booking' => null,
			);
		}

		// Date/time validation.
		if ( ! $this->validate_date( $new_date ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid date format.', 'unbelievable-salon-booking' ),
				'booking' => null,
			);
		}

		if ( ! $this->validate_time( $new_time ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid time format.', 'unbelievable-salon-booking' ),
				'booking' => null,
			);
		}

		// Use current staff if not changed.
		$staff_id = $new_staff_id ? absint( $new_staff_id ) : $booking->staff_id;

		// Check if new slot is available.
		$calendar     = new UNBSB_Calendar();
		$is_available = $calendar->is_slot_available(
			$staff_id,
			$booking->service_id,
			$new_date,
			$new_time,
			$booking->id // Exclude current booking.
		);

		if ( ! $is_available ) {
			return array(
				'success' => false,
				'message' => __( 'The selected date and time is not available.', 'unbelievable-salon-booking' ),
				'booking' => null,
			);
		}

		// Calculate service duration.
		$service_model = new UNBSB_Service();
		$service       = $service_model->get( $booking->service_id );

		if ( ! $service ) {
			return array(
				'success' => false,
				'message' => __( 'Service information not found.', 'unbelievable-salon-booking' ),
				'booking' => null,
			);
		}

		$duration   = $service->duration + ( $service->buffer_before ?? 0 ) + ( $service->buffer_after ?? 0 );
		$start_time = new DateTime( $new_time );
		$end_time   = clone $start_time;
		$end_time->add( new DateInterval( 'PT' . $duration . 'M' ) );

		// Save old information.
		$old_date = $booking->booking_date;
		$old_time = $booking->start_time;

		// Update booking.
		$reschedule_count = isset( $booking->reschedule_count ) ? absint( $booking->reschedule_count ) + 1 : 1;
		$original_id      = $booking->original_booking_id ?? $booking->id;

		$update_data = array(
			'staff_id'            => $staff_id,
			'booking_date'        => $new_date,
			'start_time'          => $new_time,
			'end_time'            => $end_time->format( 'H:i:s' ),
			'reschedule_count'    => $reschedule_count,
			'original_booking_id' => $original_id,
		);

		// Update internal notes.
		$internal_notes  = $booking->internal_notes ?? '';
		$internal_notes .= "\n" . sprintf(
			/* translators: 1: Date/time, 2: Old date, 3: Old time, 4: New date, 5: New time */
			__( '[%1$s] Rescheduled: %2$s %3$s -> %4$s %5$s', 'unbelievable-salon-booking' ),
			current_time( 'mysql' ),
			$old_date,
			$old_time,
			$new_date,
			$new_time
		);
		$update_data['internal_notes'] = $internal_notes;

		$result = $this->booking_model->update( $booking->id, $update_data );

		if ( $result ) {
			// Get updated booking information.
			$updated_booking = $this->booking_model->get_with_details( $booking->id );

			/**
			 * Fired when booking is rescheduled by customer.
			 *
			 * @param int    $booking_id      Booking ID.
			 * @param object $updated_booking Updated booking object.
			 * @param string $old_date        Old date.
			 * @param string $old_time        Old time.
			 */
			do_action( 'unbsb_booking_rescheduled_by_customer', $booking->id, $updated_booking, $old_date, $old_time );

			return array(
				'success' => true,
				'message' => __( 'Your booking has been updated successfully.', 'unbelievable-salon-booking' ),
				'booking' => $updated_booking,
			);
		}

		return array(
			'success' => false,
			'message' => __( 'An error occurred while updating the booking.', 'unbelievable-salon-booking' ),
			'booking' => null,
		);
	}

	/**
	 * Date validation
	 *
	 * @param string $date Date (Y-m-d).
	 *
	 * @return bool
	 */
	private function validate_date( $date ) {
		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Time validation
	 *
	 * @param string $time Time (H:i or H:i:s).
	 *
	 * @return bool
	 */
	private function validate_time( $time ) {
		// H:i format.
		$t = DateTime::createFromFormat( 'H:i', $time );
		if ( $t && $t->format( 'H:i' ) === $time ) {
			return true;
		}

		// H:i:s format.
		$t = DateTime::createFromFormat( 'H:i:s', $time );
		return $t && $t->format( 'H:i:s' ) === $time;
	}

	/**
	 * Generate manage booking URL
	 *
	 * @param string $token Booking token.
	 *
	 * @return string URL.
	 */
	public function get_manage_url( $token ) {
		// Shortcode page or custom page.
		$page_id = get_option( 'unbsb_manage_booking_page', 0 );

		if ( $page_id ) {
			$url = get_permalink( $page_id );
		} else {
			// Use home URL as default.
			$url = home_url( '/' );
		}

		return add_query_arg(
			array(
				'unbsb_action' => 'manage_booking',
				'token'        => sanitize_text_field( $token ),
			),
			$url
		);
	}

	/**
	 * Get available slots (for rescheduling)
	 *
	 * @param object $booking Booking object.
	 * @param string $date    Date (Y-m-d).
	 *
	 * @return array Available slots.
	 */
	public function get_available_slots_for_reschedule( $booking, $date ) {
		$calendar = new UNBSB_Calendar();

		// Get slots excluding current booking.
		$slots = $calendar->get_available_slots(
			$booking->staff_id,
			$booking->service_id,
			$date,
			$booking->id
		);

		return $slots;
	}
}
