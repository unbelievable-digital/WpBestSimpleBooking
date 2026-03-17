<?php
/**
 * Calendar class - Slot calculation and calendar operations
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calendar class
 */
class UNBSB_Calendar {

	/**
	 * Staff model
	 *
	 * @var UNBSB_Staff
	 */
	private $staff_model;

	/**
	 * Booking model
	 *
	 * @var UNBSB_Booking
	 */
	private $booking_model;

	/**
	 * Service model
	 *
	 * @var UNBSB_Service
	 */
	private $service_model;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->staff_model   = new UNBSB_Staff();
		$this->booking_model = new UNBSB_Booking();
		$this->service_model = new UNBSB_Service();
	}

	/**
	 * Get available slots
	 *
	 * @param int    $staff_id           Staff ID.
	 * @param int    $service_id         Service ID.
	 * @param string $date               Date (Y-m-d).
	 * @param int    $exclude_booking_id Booking ID to exclude (optional).
	 *
	 * @return array
	 */
	public function get_available_slots( $staff_id, $service_id, $date, $exclude_booking_id = 0 ) {
		// Get service.
		$service = $this->service_model->get( $service_id );

		if ( ! $service ) {
			return array();
		}

		// Past date check.
		$today = current_time( 'Y-m-d' );
		if ( $date < $today ) {
			return array();
		}

		// Holiday check.
		if ( $this->is_holiday( $staff_id, $date ) ) {
			return array();
		}

		// Get day of week.
		$day_of_week = (int) gmdate( 'w', strtotime( $date ) );

		// Get working hours.
		$working_hours = $this->get_working_hours_for_day( $staff_id, $day_of_week );

		if ( empty( $working_hours ) || ! $working_hours->is_working ) {
			return array();
		}

		// Get existing bookings.
		$bookings = $this->booking_model->get_by_date( $date, $staff_id );

		// Filter out excluded booking.
		if ( $exclude_booking_id > 0 ) {
			$bookings = array_filter(
				$bookings,
				function ( $booking ) use ( $exclude_booking_id ) {
					return (int) $booking->id !== (int) $exclude_booking_id;
				}
			);
		}

		// Get breaks.
		$breaks = $this->staff_model->get_breaks( $staff_id, $day_of_week );

		// Calculate slots.
		$slots = $this->calculate_slots(
			$working_hours->start_time,
			$working_hours->end_time,
			$service->duration + $service->buffer_before + $service->buffer_after,
			$bookings,
			$breaks,
			$date
		);

		return apply_filters( 'unbsb_filter_available_slots', $slots, $date, $staff_id, $service_id );
	}

	/**
	 * Get available slots by duration (for multi-service)
	 *
	 * @param int    $staff_id           Staff ID.
	 * @param string $date               Date (Y-m-d).
	 * @param int    $total_duration     Total duration (minutes).
	 * @param int    $exclude_booking_id Booking ID to exclude (optional).
	 *
	 * @return array
	 */
	public function get_available_slots_by_duration( $staff_id, $date, $total_duration, $exclude_booking_id = 0 ) {
		// Past date check.
		$today = current_time( 'Y-m-d' );
		if ( $date < $today ) {
			return array();
		}

		// Holiday check.
		if ( $this->is_holiday( $staff_id, $date ) ) {
			return array();
		}

		// Get day of week.
		$day_of_week = (int) gmdate( 'w', strtotime( $date ) );

		// Get working hours.
		$working_hours = $this->get_working_hours_for_day( $staff_id, $day_of_week );

		if ( empty( $working_hours ) || ! $working_hours->is_working ) {
			return array();
		}

		// Get existing bookings.
		$bookings = $this->booking_model->get_by_date( $date, $staff_id );

		// Filter out excluded booking.
		if ( $exclude_booking_id > 0 ) {
			$bookings = array_filter(
				$bookings,
				function ( $booking ) use ( $exclude_booking_id ) {
					return (int) $booking->id !== (int) $exclude_booking_id;
				}
			);
		}

		// Get breaks.
		$breaks = $this->staff_model->get_breaks( $staff_id, $day_of_week );

		// Calculate slots.
		$slots = $this->calculate_slots(
			$working_hours->start_time,
			$working_hours->end_time,
			$total_duration,
			$bookings,
			$breaks,
			$date
		);

		return apply_filters( 'unbsb_filter_available_slots_by_duration', $slots, $date, $staff_id, $total_duration );
	}

	/**
	 * Calculate slots
	 *
	 * @param string $start_time Start time.
	 * @param string $end_time   End time.
	 * @param int    $duration   Duration (minutes).
	 * @param array  $bookings   Existing bookings.
	 * @param array  $breaks     Breaks.
	 * @param string $date       Date.
	 *
	 * @return array
	 */
	private function calculate_slots( $start_time, $end_time, $duration, $bookings, $breaks, $date ) {
		$slots    = array();
		$interval = (int) get_option( 'unbsb_time_slot_interval', 30 );

		$start = strtotime( $date . ' ' . $start_time );
		$end   = strtotime( $date . ' ' . $end_time );

		// Lead time check (minimum advance booking time).
		$lead_time   = (int) get_option( 'unbsb_booking_lead_time', 60 );
		$current     = current_time( 'timestamp' );
		$min_booking = $current + ( $lead_time * 60 );

		while ( $start + ( $duration * 60 ) <= $end ) {
			$slot_start = gmdate( 'H:i', $start );
			$slot_end   = gmdate( 'H:i', $start + ( $duration * 60 ) );

			// Past time check.
			if ( $start >= $min_booking ) {
				// Conflict check.
				if ( ! $this->is_slot_booked( $slot_start, $slot_end, $bookings ) &&
					! $this->is_slot_in_break( $slot_start, $slot_end, $breaks ) ) {
					$slots[] = array(
						'start'     => $slot_start,
						'end'       => $slot_end,
						'available' => true,
					);
				}
			}

			$start += $interval * 60;
		}

		return $slots;
	}

	/**
	 * Check if slot is booked
	 *
	 * @param string $slot_start Slot start.
	 * @param string $slot_end   Slot end.
	 * @param array  $bookings   Existing bookings.
	 *
	 * @return bool
	 */
	private function is_slot_booked( $slot_start, $slot_end, $bookings ) {
		foreach ( $bookings as $booking ) {
			if ( 'cancelled' === $booking->status ) {
				continue;
			}

			$booking_start = substr( $booking->start_time, 0, 5 );
			$booking_end   = substr( $booking->end_time, 0, 5 );

			// Conflict check.
			if ( $slot_start < $booking_end && $slot_end > $booking_start ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if slot is in break time
	 *
	 * @param string $slot_start Slot start.
	 * @param string $slot_end   Slot end.
	 * @param array  $breaks     Breaks.
	 *
	 * @return bool
	 */
	private function is_slot_in_break( $slot_start, $slot_end, $breaks ) {
		foreach ( $breaks as $break ) {
			$break_start = substr( $break->start_time, 0, 5 );
			$break_end   = substr( $break->end_time, 0, 5 );

			if ( $slot_start < $break_end && $slot_end > $break_start ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get working hours for the day
	 *
	 * @param int $staff_id    Staff ID.
	 * @param int $day_of_week Day of week.
	 *
	 * @return object|null
	 */
	private function get_working_hours_for_day( $staff_id, $day_of_week ) {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_working_hours';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$hours = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $table . ' WHERE staff_id = %d AND day_of_week = %d',
				$staff_id,
				$day_of_week
			)
		);

		return apply_filters( 'unbsb_filter_working_hours', $hours, $staff_id, $day_of_week );
	}

	/**
	 * Check if it is a holiday
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $date     Date.
	 *
	 * @return bool
	 */
	public function is_holiday( $staff_id, $date ) {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_holidays';

		// Staff-specific or general holiday check.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . $table . '
				WHERE date = %s AND (staff_id = %d OR staff_id IS NULL)',
				$date,
				$staff_id
			)
		) > 0;
	}

	/**
	 * Get available days
	 *
	 * @param int    $staff_id   Staff ID.
	 * @param int    $service_id Service ID.
	 * @param string $month      Month (Y-m).
	 *
	 * @return array
	 */
	public function get_available_days( $staff_id, $service_id, $month ) {
		$days       = array();
		$year_month = explode( '-', $month );
		$year       = (int) $year_month[0];
		$month_num  = (int) $year_month[1];

		$days_in_month = cal_days_in_month( CAL_GREGORIAN, $month_num, $year );
		$today         = current_time( 'Y-m-d' );
		$max_future    = (int) get_option( 'unbsb_booking_future_days', 30 );
		$max_date      = gmdate( 'Y-m-d', strtotime( "+{$max_future} days" ) );

		for ( $day = 1; $day <= $days_in_month; $day++ ) {
			$date = sprintf( '%04d-%02d-%02d', $year, $month_num, $day );

			// Past and too far future date check.
			if ( $date < $today || $date > $max_date ) {
				$days[ $date ] = false;
				continue;
			}

			// Check if available slots exist.
			$slots         = $this->get_available_slots( $staff_id, $service_id, $date );
			$days[ $date ] = ! empty( $slots );
		}

		return $days;
	}

	/**
	 * Get calendar data (for admin)
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param int    $staff_id   Staff ID (optional).
	 *
	 * @return array
	 */
	public function get_calendar_events( $start_date, $end_date, $staff_id = 0 ) {
		$args = array(
			'date_from' => $start_date,
			'date_to'   => $end_date,
		);

		if ( $staff_id ) {
			$args['staff_id'] = $staff_id;
		}

		$bookings = $this->booking_model->get_all( $args );
		$events   = array();

		foreach ( $bookings as $booking ) {
			$events[] = array(
				'id'              => $booking->id,
				'title'           => $booking->customer_name . ' - ' . $booking->service_name,
				'start'           => $booking->booking_date . 'T' . $booking->start_time,
				'end'             => $booking->booking_date . 'T' . $booking->end_time,
				'backgroundColor' => $this->get_status_color( $booking->status ),
				'borderColor'     => $booking->service_color ?? '#3788d8',
				'extendedProps'   => array(
					'status'         => $booking->status,
					'customer_name'  => $booking->customer_name,
					'customer_email' => $booking->customer_email,
					'customer_phone' => $booking->customer_phone,
					'service_name'   => $booking->service_name,
					'staff_name'     => $booking->staff_name,
					'price'          => $booking->price,
				),
			);
		}

		return $events;
	}

	/**
	 * Get status color
	 *
	 * @param string $status Status.
	 *
	 * @return string
	 */
	private function get_status_color( $status ) {
		$colors = array(
			'pending'   => '#f59e0b',
			'confirmed' => '#10b981',
			'cancelled' => '#ef4444',
			'completed' => '#6b7280',
			'no_show'   => '#8b5cf6',
		);

		return $colors[ $status ] ?? '#3788d8';
	}

	/**
	 * Check if a specific slot is available
	 *
	 * @param int    $staff_id           Staff ID.
	 * @param int    $service_id         Service ID.
	 * @param string $date               Date (Y-m-d).
	 * @param string $time               Time (H:i).
	 * @param int    $exclude_booking_id Booking ID to exclude (optional).
	 *
	 * @return bool
	 */
	public function is_slot_available( $staff_id, $service_id, $date, $time, $exclude_booking_id = 0 ) {
		$available_slots = $this->get_available_slots( $staff_id, $service_id, $date, $exclude_booking_id );

		// Normalize to H:i format.
		$time_normalized = substr( $time, 0, 5 );

		foreach ( $available_slots as $slot ) {
			if ( $slot['start'] === $time_normalized ) {
				return true;
			}
		}

		return false;
	}
}
