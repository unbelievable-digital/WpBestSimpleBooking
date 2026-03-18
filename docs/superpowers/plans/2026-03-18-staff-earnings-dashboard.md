# Staff Earnings Dashboard & Self-Booking — Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add staff earnings/payment tracking, performance metrics, and self-booking to the existing staff portal.

**Architecture:** Extend existing staff portal with 2 new admin pages (My Earnings, My Performance), a new "Earnings & Payments" tab in admin staff edit modal, a new `unbsb_staff_payments` database table, 6 new REST API endpoints, and staff self-booking restrictions on the existing New Booking page.

**Tech Stack:** PHP 8.0+, WordPress $wpdb, vanilla JS (fetch API), WordPress REST API, existing CSS variable system.

**Spec:** `docs/superpowers/specs/2026-03-18-staff-earnings-dashboard-design.md`

---

## File Structure

### New Files
| File | Responsibility |
|------|---------------|
| `admin/partials/admin-staff-earnings.php` | Staff portal "My Earnings" page template |
| `admin/partials/admin-staff-performance.php` | Staff portal "My Performance" page template |

### Modified Files
| File | Changes |
|------|---------|
| `includes/class-unbsb-activator.php` | Add `unbsb_staff_payments` table + migration 2.2.0 |
| `includes/models/class-unbsb-staff.php` | Add payment CRUD methods, earnings summary, performance queries |
| `includes/class-unbsb-rest-api.php` | Add 6 new REST endpoints for earnings/payments/performance |
| `admin/class-unbsb-admin.php` | Register 2 new staff portal menu items, render methods, localize new strings |
| `admin/partials/admin-staff.php` | Add "Remaining Balance" to staff cards |
| `admin/partials/admin-new-booking.php` | Add staff self-booking conditional logic |
| `admin/js/unbsb-admin.js` | Add earnings tab JS, payment modal, performance page, self-booking filter |
| `admin/css/unbsb-admin.css` | Add earnings/performance page styles |
| `unbelievable-salon-booking.php` | Bump version to 2.2.0 |
| `readme.txt` | Add changelog entry |

---

## Task 1: Database — New `unbsb_staff_payments` Table + Migration

**Files:**
- Modify: `includes/class-unbsb-activator.php`
- Modify: `unbelievable-salon-booking.php` (version bump)

- [ ] **Step 1: Add `unbsb_staff_payments` to `create_tables()`**

In `includes/class-unbsb-activator.php`, find the `staff_earnings` table creation block (around line 99-113). After it, add:

```php
// Staff payments table.
$sql = "CREATE TABLE {$prefix}staff_payments (
	id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	staff_id BIGINT(20) UNSIGNED NOT NULL,
	amount DECIMAL(10,2) NOT NULL,
	payment_date DATE NOT NULL,
	payment_method VARCHAR(50) DEFAULT NULL,
	notes TEXT,
	recorded_by BIGINT(20) UNSIGNED NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY staff_id (staff_id),
	KEY payment_date (payment_date)
) $charset_collate;";
dbDelta( $sql );
```

- [ ] **Step 2: Add `migration_2_2_0()` method**

After the last migration method in the file, add:

```php
/**
 * Migration 2.2.0 - Staff payments table
 */
private static function migration_2_2_0() {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	$prefix          = $wpdb->prefix . 'unbsb_';

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$sql = "CREATE TABLE {$prefix}staff_payments (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		staff_id BIGINT(20) UNSIGNED NOT NULL,
		amount DECIMAL(10,2) NOT NULL,
		payment_date DATE NOT NULL,
		payment_method VARCHAR(50) DEFAULT NULL,
		notes TEXT,
		recorded_by BIGINT(20) UNSIGNED NOT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY staff_id (staff_id),
		KEY payment_date (payment_date)
	) $charset_collate;";
	dbDelta( $sql );
}
```

- [ ] **Step 3: Register migration in `run_migrations()`**

In `run_migrations()`, after the last `version_compare` block, add:

```php
// v2.2.0 - Staff payments table.
if ( version_compare( $current_db_version, '2.2.0', '<' ) ) {
	self::migration_2_2_0();
}
```

- [ ] **Step 4: Bump db_version constant**

Change the `$db_version` property (line ~28) from `'2.1.0'` to `'2.2.0'`.

- [ ] **Step 5: Bump plugin version**

In `unbelievable-salon-booking.php`, update the Version header and the `UNBSB_VERSION` constant to `2.2.0`.

- [ ] **Step 6: Test migration**

Deactivate and reactivate the plugin. Verify `unbsb_staff_payments` table exists:

```
wp db query "SHOW TABLES LIKE '%unbsb_staff_payments%'"
```

- [ ] **Step 7: Commit**

```bash
git add includes/class-unbsb-activator.php unbelievable-salon-booking.php
git commit -m "feat: add unbsb_staff_payments table and migration 2.2.0"
```

---

## Task 2: Staff Model — Payment & Earnings Methods

**Files:**
- Modify: `includes/models/class-unbsb-staff.php`

- [ ] **Step 1: Add `record_payment()` method**

After the `record_salary()` method (around line 681), add:

```php
/**
 * Record a payment to staff
 *
 * @param int    $staff_id       Staff ID.
 * @param float  $amount         Payment amount.
 * @param string $payment_date   Payment date (Y-m-d).
 * @param string $payment_method Payment method.
 * @param string $notes          Notes.
 * @param int    $recorded_by    Admin user ID.
 *
 * @return int|false
 */
public function record_payment( $staff_id, $amount, $payment_date, $payment_method, $notes, $recorded_by ) {
	if ( $amount <= 0 ) {
		return false;
	}

	return $this->db->insert(
		'staff_payments',
		array(
			'staff_id'       => absint( $staff_id ),
			'amount'         => floatval( $amount ),
			'payment_date'   => sanitize_text_field( $payment_date ),
			'payment_method' => sanitize_text_field( $payment_method ),
			'notes'          => sanitize_textarea_field( $notes ),
			'recorded_by'    => absint( $recorded_by ),
		)
	);
}
```

- [ ] **Step 2: Add `delete_payment()` method**

```php
/**
 * Delete a payment record
 *
 * @param int $payment_id Payment ID.
 * @param int $staff_id   Staff ID (for ownership check).
 *
 * @return bool
 */
public function delete_payment( $payment_id, $staff_id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'unbsb_staff_payments';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$deleted = $wpdb->delete(
		$table,
		array(
			'id'       => absint( $payment_id ),
			'staff_id' => absint( $staff_id ),
		),
		array( '%d', '%d' )
	);

	return (bool) $deleted;
}
```

- [ ] **Step 3: Add `get_payments()` method**

```php
/**
 * Get staff payments
 *
 * @param int    $staff_id   Staff ID.
 * @param string $date_from  Start date (Y-m-d). Optional.
 * @param string $date_to    End date (Y-m-d). Optional.
 *
 * @return array
 */
public function get_payments( $staff_id, $date_from = null, $date_to = null ) {
	global $wpdb;
	$table = $wpdb->prefix . 'unbsb_staff_payments';

	$where = $wpdb->prepare( 'WHERE staff_id = %d', absint( $staff_id ) );

	if ( $date_from && $date_to ) {
		$where .= $wpdb->prepare( ' AND payment_date BETWEEN %s AND %s', $date_from, $date_to );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return $wpdb->get_results(
		"SELECT p.*, u.display_name AS recorded_by_name
		FROM {$table} p
		LEFT JOIN {$wpdb->users} u ON p.recorded_by = u.ID
		{$where}
		ORDER BY p.payment_date DESC, p.id DESC"
	);
}
```

- [ ] **Step 4: Add `get_earnings_summary()` method**

```php
/**
 * Get earnings summary for a staff member
 *
 * @param int    $staff_id  Staff ID.
 * @param string $period    Period in Y-m format. Null for all time.
 *
 * @return array
 */
public function get_earnings_summary( $staff_id ) {
	global $wpdb;
	$earnings_table = $wpdb->prefix . 'unbsb_staff_earnings';
	$payments_table = $wpdb->prefix . 'unbsb_staff_payments';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$total_earnings = floatval( $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(amount), 0) FROM {$earnings_table} WHERE staff_id = %d",
			absint( $staff_id )
		)
	) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$total_paid = floatval( $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(amount), 0) FROM {$payments_table} WHERE staff_id = %d",
			absint( $staff_id )
		)
	) );

	$current_period = current_time( 'Y-m' );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$this_month_earnings = floatval( $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(amount), 0) FROM {$earnings_table} WHERE staff_id = %d AND period = %s",
			absint( $staff_id ),
			$current_period
		)
	) );

	return array(
		'total_earnings'      => $total_earnings,
		'total_paid'          => $total_paid,
		'remaining_balance'   => $total_earnings - $total_paid,
		'this_month_earnings' => $this_month_earnings,
	);
}
```

- [ ] **Step 5: Add `get_earnings_detail()` method**

```php
/**
 * Get detailed earnings records for a staff member
 *
 * @param int    $staff_id  Staff ID.
 * @param string $period    Period in Y-m format. Null for all.
 *
 * @return array
 */
public function get_earnings_detail( $staff_id, $period = null ) {
	global $wpdb;
	$table          = $wpdb->prefix . 'unbsb_staff_earnings';
	$bookings_table = $wpdb->prefix . 'unbsb_bookings';
	$services_table = $wpdb->prefix . 'unbsb_services';

	$where = $wpdb->prepare( 'WHERE e.staff_id = %d', absint( $staff_id ) );

	if ( $period ) {
		$where .= $wpdb->prepare( ' AND e.period = %s', $period );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return $wpdb->get_results(
		"SELECT e.*, b.booking_date, b.customer_name, b.price AS booking_price, s.name AS service_name
		FROM {$table} e
		LEFT JOIN {$bookings_table} b ON e.booking_id = b.id
		LEFT JOIN {$services_table} s ON b.service_id = s.id
		{$where}
		ORDER BY e.created_at DESC"
	);
}
```

- [ ] **Step 6: Add `get_performance_metrics()` method**

```php
/**
 * Get performance metrics for a staff member
 *
 * @param int    $staff_id  Staff ID.
 * @param string $date_from Start date (Y-m-d).
 * @param string $date_to   End date (Y-m-d).
 *
 * @return array
 */
public function get_performance_metrics( $staff_id, $date_from, $date_to ) {
	global $wpdb;
	$bookings_table = $wpdb->prefix . 'unbsb_bookings';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$stats = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				COUNT(*) AS total_bookings,
				SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
				SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
				SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
				COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) AS total_revenue
			FROM {$bookings_table}
			WHERE staff_id = %d AND booking_date BETWEEN %s AND %s",
			absint( $staff_id ),
			$date_from,
			$date_to
		)
	);

	$denominator = intval( $stats->confirmed ) + intval( $stats->completed ) + intval( $stats->cancelled );
	$cancel_rate = $denominator > 0 ? round( ( intval( $stats->cancelled ) / $denominator ) * 100, 1 ) : 0;

	return array(
		'total_bookings' => intval( $stats->total_bookings ),
		'completed'      => intval( $stats->completed ),
		'cancelled'      => intval( $stats->cancelled ),
		'cancel_rate'    => $cancel_rate,
		'total_revenue'  => floatval( $stats->total_revenue ),
	);
}
```

- [ ] **Step 7: Add `get_top_services()` method**

```php
/**
 * Get top services for a staff member
 *
 * @param int    $staff_id  Staff ID.
 * @param string $date_from Start date (Y-m-d).
 * @param string $date_to   End date (Y-m-d).
 * @param int    $limit     Number of results.
 *
 * @return array
 */
public function get_top_services( $staff_id, $date_from, $date_to, $limit = 5 ) {
	global $wpdb;
	$bookings_table = $wpdb->prefix . 'unbsb_bookings';
	$services_table = $wpdb->prefix . 'unbsb_services';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT s.name, COUNT(*) AS booking_count, COALESCE(SUM(b.price), 0) AS total_revenue
			FROM {$bookings_table} b
			INNER JOIN {$services_table} s ON b.service_id = s.id
			WHERE b.staff_id = %d AND b.booking_date BETWEEN %s AND %s AND b.status IN ('completed', 'confirmed')
			GROUP BY b.service_id, s.name
			ORDER BY booking_count DESC
			LIMIT %d",
			absint( $staff_id ),
			$date_from,
			$date_to,
			$limit
		)
	);
}
```

- [ ] **Step 8: Add `get_monthly_trend()` method**

```php
/**
 * Get monthly trend for a staff member (last 6 months)
 *
 * @param int $staff_id Staff ID.
 *
 * @return array
 */
public function get_monthly_trend( $staff_id ) {
	global $wpdb;
	$bookings_table = $wpdb->prefix . 'unbsb_bookings';
	$earnings_table = $wpdb->prefix . 'unbsb_staff_earnings';

	$six_months_ago = wp_date( 'Y-m-01', strtotime( '-5 months' ) );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$booking_data = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT DATE_FORMAT(booking_date, '%%Y-%%m') AS month,
				COUNT(*) AS bookings,
				COALESCE(SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END), 0) AS revenue
			FROM {$bookings_table}
			WHERE staff_id = %d AND booking_date >= %s
			GROUP BY month
			ORDER BY month ASC",
			absint( $staff_id ),
			$six_months_ago
		)
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$earnings_data = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT period AS month, COALESCE(SUM(amount), 0) AS commission
			FROM {$earnings_table}
			WHERE staff_id = %d AND period >= %s
			GROUP BY period
			ORDER BY period ASC",
			absint( $staff_id ),
			wp_date( 'Y-m', strtotime( '-5 months' ) )
		)
	);

	$earnings_map = array();
	foreach ( $earnings_data as $row ) {
		$earnings_map[ $row->month ] = floatval( $row->commission );
	}

	$result = array();
	foreach ( $booking_data as $row ) {
		$result[] = array(
			'month'      => $row->month,
			'bookings'   => intval( $row->bookings ),
			'revenue'    => floatval( $row->revenue ),
			'commission' => isset( $earnings_map[ $row->month ] ) ? $earnings_map[ $row->month ] : 0,
		);
	}

	return $result;
}
```

- [ ] **Step 9: Add `get_all_with_balance()` method**

For the admin staff list "Remaining Balance" column — single aggregated query:

```php
/**
 * Get all staff with remaining balance (single query, no N+1)
 *
 * @return array
 */
public function get_all_with_balance() {
	global $wpdb;
	$staff_table    = $wpdb->prefix . 'unbsb_staff';
	$earnings_table = $wpdb->prefix . 'unbsb_staff_earnings';
	$payments_table = $wpdb->prefix . 'unbsb_staff_payments';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	return $wpdb->get_results(
		"SELECT s.*,
			COALESCE(e.total_earnings, 0) AS total_earnings,
			COALESCE(p.total_paid, 0) AS total_paid,
			COALESCE(e.total_earnings, 0) - COALESCE(p.total_paid, 0) AS remaining_balance
		FROM {$staff_table} s
		LEFT JOIN (
			SELECT staff_id, SUM(amount) AS total_earnings
			FROM {$earnings_table}
			GROUP BY staff_id
		) e ON s.id = e.staff_id
		LEFT JOIN (
			SELECT staff_id, SUM(amount) AS total_paid
			FROM {$payments_table}
			GROUP BY staff_id
		) p ON s.id = p.staff_id
		ORDER BY s.sort_order ASC, s.name ASC"
	);
}
```

- [ ] **Step 10: Commit**

```bash
git add includes/models/class-unbsb-staff.php
git commit -m "feat: add payment CRUD, earnings summary, and performance query methods to staff model"
```

---

## Task 3: REST API — New Endpoints

**Files:**
- Modify: `includes/class-unbsb-rest-api.php`

- [ ] **Step 1: Register 6 new routes in `register_routes()`**

After the last `register_rest_route` call, add:

```php
// Staff portal — earnings.
register_rest_route(
	$this->namespace,
	'/staff-portal/earnings',
	array(
		'methods'             => 'GET',
		'callback'            => array( $this, 'get_staff_portal_earnings' ),
		'permission_callback' => array( $this, 'staff_portal_permission' ),
		'args'                => array(
			'period' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
	)
);

// Staff portal — payments.
register_rest_route(
	$this->namespace,
	'/staff-portal/payments',
	array(
		'methods'             => 'GET',
		'callback'            => array( $this, 'get_staff_portal_payments' ),
		'permission_callback' => array( $this, 'staff_portal_permission' ),
		'args'                => array(
			'date_from' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'date_to' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
	)
);

// Staff portal — performance.
register_rest_route(
	$this->namespace,
	'/staff-portal/performance',
	array(
		'methods'             => 'GET',
		'callback'            => array( $this, 'get_staff_portal_performance' ),
		'permission_callback' => array( $this, 'staff_portal_permission' ),
		'args'                => array(
			'date_from' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'date_to' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
	)
);

// Admin — staff payments CRUD.
register_rest_route(
	$this->namespace,
	'/admin/staff/(?P<staff_id>\d+)/payments',
	array(
		array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_admin_staff_payments' ),
			'permission_callback' => array( $this, 'admin_permission' ),
		),
		array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'create_admin_staff_payment' ),
			'permission_callback' => array( $this, 'admin_permission' ),
		),
	)
);

// Admin — delete staff payment.
register_rest_route(
	$this->namespace,
	'/admin/staff/(?P<staff_id>\d+)/payments/(?P<payment_id>\d+)',
	array(
		'methods'             => 'DELETE',
		'callback'            => array( $this, 'delete_admin_staff_payment' ),
		'permission_callback' => array( $this, 'admin_permission' ),
	)
);

// Admin — staff earnings.
register_rest_route(
	$this->namespace,
	'/admin/staff/(?P<staff_id>\d+)/earnings',
	array(
		'methods'             => 'GET',
		'callback'            => array( $this, 'get_admin_staff_earnings' ),
		'permission_callback' => array( $this, 'admin_permission' ),
	)
);
```

- [ ] **Step 2: Add `staff_portal_permission()` callback**

```php
/**
 * Staff portal permission check
 *
 * @return bool
 */
public function staff_portal_permission() {
	return current_user_can( 'unbsb_view_own_bookings' );
}
```

- [ ] **Step 3: Add `admin_permission()` callback** (if not already existing)

```php
/**
 * Admin permission check
 *
 * @return bool
 */
public function admin_permission() {
	return current_user_can( 'manage_options' );
}
```

- [ ] **Step 4: Add staff portal earnings callback**

```php
/**
 * Get staff portal earnings
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
public function get_staff_portal_earnings( $request ) {
	$staff_model = new UNBSB_Staff();
	$staff       = $staff_model->get_by_user_id( get_current_user_id() );

	if ( ! $staff ) {
		return new WP_Error( 'no_staff', __( 'Staff record not found.', 'unbelievable-salon-booking' ), array( 'status' => 404 ) );
	}

	$period = $request->get_param( 'period' );

	$summary = $staff_model->get_earnings_summary( $staff->id );
	$detail  = $staff_model->get_earnings_detail( $staff->id, $period );

	return rest_ensure_response( array(
		'summary' => $summary,
		'detail'  => $detail,
	) );
}
```

- [ ] **Step 5: Add staff portal payments callback**

```php
/**
 * Get staff portal payments
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
public function get_staff_portal_payments( $request ) {
	$staff_model = new UNBSB_Staff();
	$staff       = $staff_model->get_by_user_id( get_current_user_id() );

	if ( ! $staff ) {
		return new WP_Error( 'no_staff', __( 'Staff record not found.', 'unbelievable-salon-booking' ), array( 'status' => 404 ) );
	}

	$date_from = $request->get_param( 'date_from' );
	$date_to   = $request->get_param( 'date_to' );

	$payments = $staff_model->get_payments( $staff->id, $date_from, $date_to );

	return rest_ensure_response( $payments );
}
```

- [ ] **Step 6: Add staff portal performance callback**

```php
/**
 * Get staff portal performance
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
public function get_staff_portal_performance( $request ) {
	$staff_model = new UNBSB_Staff();
	$staff       = $staff_model->get_by_user_id( get_current_user_id() );

	if ( ! $staff ) {
		return new WP_Error( 'no_staff', __( 'Staff record not found.', 'unbelievable-salon-booking' ), array( 'status' => 404 ) );
	}

	$date_from = $request->get_param( 'date_from' ) ?: wp_date( 'Y-m-01' );
	$date_to   = $request->get_param( 'date_to' ) ?: wp_date( 'Y-m-t' );

	$metrics      = $staff_model->get_performance_metrics( $staff->id, $date_from, $date_to );
	$top_services = $staff_model->get_top_services( $staff->id, $date_from, $date_to );
	$trend        = $staff_model->get_monthly_trend( $staff->id );

	return rest_ensure_response( array(
		'metrics'      => $metrics,
		'top_services' => $top_services,
		'trend'        => $trend,
	) );
}
```

- [ ] **Step 7: Add admin staff payments callbacks**

```php
/**
 * Get admin staff payments
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
public function get_admin_staff_payments( $request ) {
	$staff_model = new UNBSB_Staff();
	$staff_id    = absint( $request->get_param( 'staff_id' ) );
	$date_from   = $request->get_param( 'date_from' );
	$date_to     = $request->get_param( 'date_to' );

	$summary  = $staff_model->get_earnings_summary( $staff_id );
	$payments = $staff_model->get_payments( $staff_id, $date_from, $date_to );

	return rest_ensure_response( array(
		'summary'  => $summary,
		'payments' => $payments,
	) );
}

/**
 * Create admin staff payment
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
public function create_admin_staff_payment( $request ) {
	$staff_model = new UNBSB_Staff();
	$staff_id    = absint( $request->get_param( 'staff_id' ) );

	$amount         = floatval( $request->get_param( 'amount' ) );
	$payment_date   = sanitize_text_field( $request->get_param( 'payment_date' ) ?: wp_date( 'Y-m-d' ) );
	$payment_method = sanitize_text_field( $request->get_param( 'payment_method' ) );
	$notes          = sanitize_textarea_field( $request->get_param( 'notes' ) );

	if ( $amount <= 0 ) {
		return new WP_Error( 'invalid_amount', __( 'Amount must be greater than zero.', 'unbelievable-salon-booking' ), array( 'status' => 400 ) );
	}

	$result = $staff_model->record_payment( $staff_id, $amount, $payment_date, $payment_method, $notes, get_current_user_id() );

	if ( ! $result ) {
		return new WP_Error( 'payment_failed', __( 'Failed to record payment.', 'unbelievable-salon-booking' ), array( 'status' => 500 ) );
	}

	return rest_ensure_response( array( 'success' => true, 'id' => $result ) );
}

/**
 * Delete admin staff payment
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
public function delete_admin_staff_payment( $request ) {
	$staff_model = new UNBSB_Staff();
	$staff_id    = absint( $request->get_param( 'staff_id' ) );
	$payment_id  = absint( $request->get_param( 'payment_id' ) );

	$deleted = $staff_model->delete_payment( $payment_id, $staff_id );

	if ( ! $deleted ) {
		return new WP_Error( 'delete_failed', __( 'Failed to delete payment.', 'unbelievable-salon-booking' ), array( 'status' => 500 ) );
	}

	return rest_ensure_response( array( 'success' => true ) );
}

/**
 * Get admin staff earnings
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
public function get_admin_staff_earnings( $request ) {
	$staff_model = new UNBSB_Staff();
	$staff_id    = absint( $request->get_param( 'staff_id' ) );
	$period      = $request->get_param( 'period' );

	$summary = $staff_model->get_earnings_summary( $staff_id );
	$detail  = $staff_model->get_earnings_detail( $staff_id, $period );

	return rest_ensure_response( array(
		'summary' => $summary,
		'detail'  => $detail,
	) );
}
```

- [ ] **Step 8: Commit**

```bash
git add includes/class-unbsb-rest-api.php
git commit -m "feat: add REST API endpoints for staff earnings, payments, and performance"
```

---

## Task 4: Admin Menu & Render Methods

**Files:**
- Modify: `admin/class-unbsb-admin.php`

- [ ] **Step 1: Add 2 new staff portal menu items**

In `add_admin_menu()`, inside the `if ( ! current_user_can( 'manage_options' ) && current_user_can( 'unbsb_view_own_bookings' ) )` block, after the "My Schedule" submenu, add:

```php
add_submenu_page(
	'unbsb-staff-portal',
	__( 'My Earnings', 'unbelievable-salon-booking' ),
	__( 'My Earnings', 'unbelievable-salon-booking' ),
	'unbsb_view_own_bookings',
	'unbsb-staff-earnings-portal',
	array( $this, 'render_staff_earnings_portal' )
);

add_submenu_page(
	'unbsb-staff-portal',
	__( 'My Performance', 'unbelievable-salon-booking' ),
	__( 'My Performance', 'unbelievable-salon-booking' ),
	'unbsb_view_own_bookings',
	'unbsb-staff-performance-portal',
	array( $this, 'render_staff_performance_portal' )
);
```

Also add the hidden pages in the `else` block (for admin access):

```php
add_submenu_page(
	null,
	__( 'Staff Earnings', 'unbelievable-salon-booking' ),
	__( 'Staff Earnings', 'unbelievable-salon-booking' ),
	'manage_options',
	'unbsb-staff-earnings-portal',
	array( $this, 'render_staff_earnings_portal' )
);

add_submenu_page(
	null,
	__( 'Staff Performance', 'unbelievable-salon-booking' ),
	__( 'Staff Performance', 'unbelievable-salon-booking' ),
	'manage_options',
	'unbsb-staff-performance-portal',
	array( $this, 'render_staff_performance_portal' )
);
```

- [ ] **Step 2: Add render methods**

After the existing `render_staff_schedule_portal()` method, add:

```php
/**
 * Render staff earnings portal page
 */
public function render_staff_earnings_portal() {
	$staff_model = new UNBSB_Staff();

	if ( current_user_can( 'manage_options' ) ) {
		$staff_id = isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0;
		$staff    = $staff_id ? $staff_model->get( $staff_id ) : null;
	} else {
		$staff = $staff_model->get_by_user_id( get_current_user_id() );
	}

	if ( ! $staff ) {
		wp_die( esc_html__( 'Staff record not found.', 'unbelievable-salon-booking' ) );
	}

	$summary         = $staff_model->get_earnings_summary( $staff->id );
	$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
	$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );

	include plugin_dir_path( __FILE__ ) . 'partials/admin-staff-earnings.php';
}

/**
 * Render staff performance portal page
 */
public function render_staff_performance_portal() {
	$staff_model = new UNBSB_Staff();

	if ( current_user_can( 'manage_options' ) ) {
		$staff_id = isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0;
		$staff    = $staff_id ? $staff_model->get( $staff_id ) : null;
	} else {
		$staff = $staff_model->get_by_user_id( get_current_user_id() );
	}

	if ( ! $staff ) {
		wp_die( esc_html__( 'Staff record not found.', 'unbelievable-salon-booking' ) );
	}

	$date_from       = wp_date( 'Y-m-01' );
	$date_to         = wp_date( 'Y-m-t' );
	$metrics         = $staff_model->get_performance_metrics( $staff->id, $date_from, $date_to );
	$top_services    = $staff_model->get_top_services( $staff->id, $date_from, $date_to );
	$trend           = $staff_model->get_monthly_trend( $staff->id );
	$currency_symbol = get_option( 'unbsb_currency_symbol', '₺' );
	$date_format     = get_option( 'unbsb_date_format', 'd.m.Y' );

	include plugin_dir_path( __FILE__ ) . 'partials/admin-staff-performance.php';
}
```

- [ ] **Step 3: Add new localized strings for JS**

In `wp_localize_script` call, add to the `'strings'` array:

```php
'my_earnings'           => __( 'My Earnings', 'unbelievable-salon-booking' ),
'my_performance'        => __( 'My Performance', 'unbelievable-salon-booking' ),
'total_earnings'        => __( 'Total Earnings', 'unbelievable-salon-booking' ),
'total_paid'            => __( 'Total Paid', 'unbelievable-salon-booking' ),
'remaining_balance'     => __( 'Remaining Balance', 'unbelievable-salon-booking' ),
'this_month'            => __( 'This Month', 'unbelievable-salon-booking' ),
'last_month'            => __( 'Last Month', 'unbelievable-salon-booking' ),
'last_3_months'         => __( 'Last 3 Months', 'unbelievable-salon-booking' ),
'custom_range'          => __( 'Custom Range', 'unbelievable-salon-booking' ),
'record_payment'        => __( 'Record Payment', 'unbelievable-salon-booking' ),
'payment_amount'        => __( 'Amount', 'unbelievable-salon-booking' ),
'payment_date'          => __( 'Date', 'unbelievable-salon-booking' ),
'payment_method'        => __( 'Payment Method', 'unbelievable-salon-booking' ),
'payment_notes'         => __( 'Notes', 'unbelievable-salon-booking' ),
'payment_recorded'      => __( 'Payment recorded successfully.', 'unbelievable-salon-booking' ),
'payment_deleted'       => __( 'Payment deleted.', 'unbelievable-salon-booking' ),
'confirm_delete_payment' => __( 'Are you sure you want to delete this payment?', 'unbelievable-salon-booking' ),
'no_earnings'           => __( 'No earnings found for this period.', 'unbelievable-salon-booking' ),
'no_payments'           => __( 'No payments recorded yet.', 'unbelievable-salon-booking' ),
```

- [ ] **Step 4: Commit**

```bash
git add admin/class-unbsb-admin.php
git commit -m "feat: register staff portal earnings and performance menu items with render methods"
```

---

## Task 5: Staff Earnings Template

**Files:**
- Create: `admin/partials/admin-staff-earnings.php`

- [ ] **Step 1: Create the earnings page template**

Create `admin/partials/admin-staff-earnings.php` with summary cards (Total Earnings, Total Paid, Remaining Balance), period filter, earnings detail table, and payments table. Follow the same HTML pattern as `admin-staff-bookings.php` and `admin-dashboard.php` for stat cards.

Key elements:
- `unbsb-stats-grid unbsb-stats-grid-3` for 3 summary cards
- Period filter buttons (this month / last month / last 3 months / custom)
- `unbsb-table unbsb-table-striped` for both tables
- `data-staff-id` attribute on wrapper for JS
- Staff name in page header

The tables will be populated via REST API calls in JS (Task 7).

- [ ] **Step 2: Commit**

```bash
git add admin/partials/admin-staff-earnings.php
git commit -m "feat: add staff earnings portal page template"
```

---

## Task 6: Staff Performance Template

**Files:**
- Create: `admin/partials/admin-staff-performance.php`

- [ ] **Step 1: Create the performance page template**

Create `admin/partials/admin-staff-performance.php` with:
- 4 summary cards: Bookings Count, Cancel Rate, Completed, Revenue
- Period filter
- Top Services table (top 5)
- Monthly Trend table (last 6 months)

Server-side rendered with PHP variables from render method (no AJAX needed for initial load). Period filter changes will reload via JS.

- [ ] **Step 2: Commit**

```bash
git add admin/partials/admin-staff-performance.php
git commit -m "feat: add staff performance portal page template"
```

---

## Task 7: Admin Staff Page — Earnings Tab & Balance Column

**Files:**
- Modify: `admin/partials/admin-staff.php`
- Modify: `admin/class-unbsb-admin.php` (pass balance data)

- [ ] **Step 1: Update staff list to use `get_all_with_balance()`**

In `admin/class-unbsb-admin.php`, find the `render_staff()` method. Change the staff query to use `get_all_with_balance()` instead of `get_all()`:

```php
$staff = $staff_model->get_all_with_balance();
```

- [ ] **Step 2: Add "Remaining Balance" to staff cards**

In `admin/partials/admin-staff.php`, inside the staff card's `.unbsb-staff-info` div (after the phone), add:

```php
<?php if ( isset( $staff_member->remaining_balance ) && floatval( $staff_member->remaining_balance ) > 0 ) : ?>
	<p class="unbsb-staff-balance">
		<span class="dashicons dashicons-money-alt"></span>
		<strong><?php echo esc_html( number_format( $staff_member->remaining_balance, 2 ) ); ?> <?php echo esc_html( $currency_symbol ); ?></strong>
	</p>
<?php endif; ?>
```

- [ ] **Step 3: Add payment modal HTML to staff page**

At the bottom of `admin-staff.php`, add the "Record Payment" modal:

```php
<div id="unbsb-payment-modal" class="unbsb-modal" style="display: none;">
	<div class="unbsb-modal-overlay"></div>
	<div class="unbsb-modal-content">
		<div class="unbsb-modal-header unbsb-modal-header-gradient">
			<div class="unbsb-modal-header-content">
				<div class="unbsb-modal-icon">
					<span class="dashicons dashicons-money-alt"></span>
				</div>
				<div>
					<h3><?php esc_html_e( 'Record Payment', 'unbelievable-salon-booking' ); ?></h3>
					<p class="unbsb-modal-subtitle" id="unbsb-payment-staff-name"></p>
				</div>
			</div>
			<button type="button" class="unbsb-modal-close">&times;</button>
		</div>
		<div class="unbsb-modal-body">
			<form id="unbsb-payment-form">
				<input type="hidden" id="payment-staff-id" value="">
				<div class="unbsb-form-group">
					<label for="payment-amount"><?php esc_html_e( 'Amount', 'unbelievable-salon-booking' ); ?> <span class="required">*</span></label>
					<input type="number" id="payment-amount" step="0.01" min="0.01" required>
				</div>
				<div class="unbsb-form-group">
					<label for="payment-date"><?php esc_html_e( 'Date', 'unbelievable-salon-booking' ); ?></label>
					<input type="date" id="payment-date">
				</div>
				<div class="unbsb-form-group">
					<label for="payment-method"><?php esc_html_e( 'Payment Method', 'unbelievable-salon-booking' ); ?></label>
					<input type="text" id="payment-method" placeholder="<?php esc_attr_e( 'Cash, Transfer, etc.', 'unbelievable-salon-booking' ); ?>">
				</div>
				<div class="unbsb-form-group">
					<label for="payment-notes"><?php esc_html_e( 'Notes', 'unbelievable-salon-booking' ); ?></label>
					<textarea id="payment-notes" rows="2"></textarea>
				</div>
			</form>
		</div>
		<div class="unbsb-modal-footer">
			<button type="button" class="unbsb-btn unbsb-btn-secondary unbsb-modal-close"><?php esc_html_e( 'Cancel', 'unbelievable-salon-booking' ); ?></button>
			<button type="button" class="unbsb-btn unbsb-btn-primary" id="unbsb-save-payment"><?php esc_html_e( 'Save Payment', 'unbelievable-salon-booking' ); ?></button>
		</div>
	</div>
</div>
```

- [ ] **Step 4: Add "Payments" button to staff card actions**

In the `.unbsb-staff-actions` div, add before the edit button:

```php
<button type="button" class="unbsb-btn unbsb-btn-sm unbsb-btn-secondary unbsb-staff-payment" data-id="<?php echo esc_attr( $staff_member->id ); ?>" data-name="<?php echo esc_attr( $staff_member->name ); ?>">
	<span class="dashicons dashicons-money-alt"></span>
	<?php esc_html_e( 'Pay', 'unbelievable-salon-booking' ); ?>
</button>
```

- [ ] **Step 5: Commit**

```bash
git add admin/partials/admin-staff.php admin/class-unbsb-admin.php
git commit -m "feat: add remaining balance to staff cards and payment recording modal"
```

---

## Task 8: JavaScript — Earnings, Performance, Payments

**Files:**
- Modify: `admin/js/unbsb-admin.js`

- [ ] **Step 1: Add `initStaffEarnings()` function**

Add to DOMContentLoaded init list and implement. This function:
- Detects if we're on the staff earnings page (check for `#unbsb-staff-earnings-page`)
- Loads earnings and payments via REST API
- Renders earnings table and payments table
- Handles period filter changes
- All REST calls use `fetch()` with `unbsbAdmin.restNonce` header

- [ ] **Step 2: Add `initStaffPerformance()` function**

Add to DOMContentLoaded init list and implement. This function:
- Detects if we're on the staff performance page
- Handles period filter to reload metrics via REST API
- Re-renders summary cards and tables on filter change

- [ ] **Step 3: Add payment modal JS in `initStaff()`**

In the existing `initStaff()` function, add handlers for:
- `.unbsb-staff-payment` click → open payment modal with staff ID/name
- `#unbsb-save-payment` click → POST to `/admin/staff/{id}/payments`
- Show toast on success, reload staff list to update balance

- [ ] **Step 4: Commit**

```bash
git add admin/js/unbsb-admin.js
git commit -m "feat: add JS for staff earnings, performance, and payment modal"
```

---

## Task 9: CSS — Earnings & Performance Styles

**Files:**
- Modify: `admin/css/unbsb-admin.css`

- [ ] **Step 1: Add styles for earnings and performance pages**

Add styles for:
- `.unbsb-staff-balance` — balance text on staff card (color: var(--unbsb-danger) for positive balance)
- `.unbsb-period-filter` — filter button group
- `.unbsb-period-filter .active` — active filter button
- `.unbsb-earnings-section` — section wrapper
- `.unbsb-stat-gradient-danger` — red gradient for remaining balance card
- Reuse existing `.unbsb-stats-grid`, `.unbsb-stat-card`, `.unbsb-table` classes

- [ ] **Step 2: Commit**

```bash
git add admin/css/unbsb-admin.css
git commit -m "style: add CSS for staff earnings and performance pages"
```

---

## Task 10: Staff Self-Booking

**Files:**
- Modify: `admin/class-unbsb-admin.php`
- Modify: `admin/partials/admin-new-booking.php`
- Modify: `admin/js/unbsb-admin.js`

- [ ] **Step 1: Pass self-booking context to template**

In `admin/class-unbsb-admin.php`, find `render_new_booking()`. Add logic to detect if current user is staff:

```php
$is_staff_self_booking = false;
$self_staff            = null;

if ( ! current_user_can( 'manage_options' ) && current_user_can( 'unbsb_view_own_bookings' ) ) {
	$staff_model           = new UNBSB_Staff();
	$self_staff            = $staff_model->get_by_user_id( get_current_user_id() );
	$is_staff_self_booking = (bool) $self_staff;
}
```

Pass `$is_staff_self_booking` and `$self_staff` to the template (they'll be available as local vars in the included file).

Also add `'is_staff_self_booking' => $is_staff_self_booking` and `'self_staff_id' => $self_staff ? $self_staff->id : 0` to `unbsbNewBookingData` in `wp_localize_script`.

- [ ] **Step 2: Update template for self-booking**

In `admin/partials/admin-new-booking.php`, in the staff section:

```php
<?php if ( $is_staff_self_booking ) : ?>
	<div class="unbsb-nb-staff-selected">
		<strong><?php echo esc_html( $self_staff->name ); ?></strong>
		<input type="hidden" name="staff_id" value="<?php echo esc_attr( $self_staff->id ); ?>">
	</div>
<?php else : ?>
	<!-- existing staff selection HTML -->
<?php endif; ?>
```

For services, filter the list to only show services the staff provides:

```php
<?php
$display_services = $services;
if ( $is_staff_self_booking && $self_staff ) {
	$staff_service_ids = array_map( 'intval', $self_staff->service_ids ?? array() );
	$display_services  = array_filter( $services, function( $s ) use ( $staff_service_ids ) {
		return in_array( intval( $s->id ), $staff_service_ids, true );
	} );
}
?>
```

- [ ] **Step 3: Update JS for self-booking**

In `initNewBookingPage()`, check `unbsbNewBookingData.is_staff_self_booking`:
- If true, set `selectedStaffId` to `unbsbNewBookingData.self_staff_id` immediately
- Skip staff list rendering
- Load calendar directly after service selection

- [ ] **Step 4: Add backend enforcement to booking creation AJAX**

In the AJAX handler for creating bookings (in `admin/class-unbsb-admin.php`), add:

```php
// Staff self-booking enforcement.
if ( ! current_user_can( 'manage_options' ) && current_user_can( 'unbsb_view_own_bookings' ) ) {
	$staff_model  = new UNBSB_Staff();
	$linked_staff = $staff_model->get_by_user_id( get_current_user_id() );
	if ( $linked_staff && absint( $staff_id ) !== absint( $linked_staff->id ) ) {
		wp_send_json_error( __( 'You can only create bookings for yourself.', 'unbelievable-salon-booking' ) );
	}
}
```

- [ ] **Step 5: Commit**

```bash
git add admin/class-unbsb-admin.php admin/partials/admin-new-booking.php admin/js/unbsb-admin.js
git commit -m "feat: add staff self-booking with backend enforcement"
```

---

## Task 11: Version Bump & Changelog

**Files:**
- Modify: `readme.txt`
- Modify: `unbelievable-salon-booking.php` (if not already bumped in Task 1)

- [ ] **Step 1: Update readme.txt changelog**

Add under the existing changelog:

```
= 2.2.0 =
* New: Staff Earnings Dashboard — view commission, salary, and payment history
* New: Staff Performance Metrics — booking stats, cancellation rate, top services, monthly trends
* New: Admin payment recording — track payments to staff with remaining balance
* New: Remaining balance column on staff list
* New: Staff self-booking — staff can create bookings for themselves with filtered services
* Database: Added unbsb_staff_payments table
```

- [ ] **Step 2: Commit**

```bash
git add readme.txt
git commit -m "docs: add v2.2.0 changelog entry"
```

---

## Execution Order & Dependencies

```
Task 1 (Database) ─────────────────────────────────┐
Task 2 (Staff Model) ──────────────────────────────┤
                                                     ├─→ Task 3 (REST API)
                                                     │
Task 9 (CSS) ───────────────────────────────────────┤
                                                     ├─→ Task 5 (Earnings Template)
Task 4 (Admin Menu) ───────────────────────────────┤├─→ Task 6 (Performance Template)
                                                     ├─→ Task 7 (Staff Page + Payment Modal)
                                                     ├─→ Task 8 (JavaScript)
                                                     │
                                                     └─→ Task 10 (Self-Booking)
                                                          │
                                                          └─→ Task 11 (Changelog)
```

**Parallel groups:**
- Group A (no deps): Task 1, Task 2, Task 9, Task 4
- Group B (depends on A): Task 3, Task 5, Task 6, Task 7
- Group C (depends on B): Task 8, Task 10
- Group D (depends on all): Task 11
