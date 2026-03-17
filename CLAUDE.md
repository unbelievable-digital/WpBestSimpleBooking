# Unbelievable Salon Booking - WordPress Booking Plugin

## About
A WordPress booking/appointment system designed for barbershops, beauty salons, and service providers.

**Plugin URI:** https://unbelievable.digital

## Technology Stack
- **Backend:** PHP 8.0+
- **Frontend:** Vanilla JS, Alpine.js (optional)
- **Database:** WordPress $wpdb (MySQL)
- **CSS:** Tailwind CSS or vanilla CSS

## WordPress Coding Standards

### PHP Standards
- Follow WordPress PHP Coding Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/
- Use **tabs** for indentation (not spaces)
- Opening brace should be on the same line
- Use Yoda conditions: `if ( 'value' === $variable )`
- Array syntax: use `array()`, not `[]` (optional for PHP 5.4+ compatibility)

### File Naming
- Class files: `class-{class-name}.php`
- Admin files: `admin-{feature}.php`
- Public files: `public-{feature}.php`
- Template files: `template-{name}.php`

### Class Naming
- Use PascalCase: `UNBSB_Booking`
- Use prefix: `UNBSB_`

### Function Naming
- Use snake_case
- Use prefix: `unbsb_`
- Example: `unbsb_get_available_slots()`

### Hook Naming
- Actions: `unbsb_{action_name}`
- Filters: `unbsb_filter_{filter_name}`

## Directory Structure

```
unbelievable-salon-booking/
├── unbelievable-salon-booking.php   # Main plugin file
├── uninstall.php                    # Uninstall hook
├── readme.txt                       # WordPress.org readme
├── CLAUDE.md                        # This file
│
├── includes/                        # Core PHP classes
│   ├── class-unbsb-loader.php       # Hook and filter loader
│   ├── class-unbsb-activator.php    # Activation operations
│   ├── class-unbsb-deactivator.php  # Deactivation operations
│   ├── class-unbsb-i18n.php         # Internationalization
│   ├── class-unbsb-core.php         # Core plugin class
│   ├── class-unbsb-database.php     # Database operations
│   ├── class-unbsb-calendar.php     # Calendar operations
│   ├── class-unbsb-notification.php # Email/SMS notifications
│   ├── class-unbsb-sms-manager.php  # SMS management
│   ├── class-unbsb-seo.php          # SEO features
│   ├── class-unbsb-rest-api.php     # REST API
│   ├── class-unbsb-booking-manager.php # Booking management
│   ├── class-unbsb-ics-generator.php   # ICS calendar export
│   ├── class-unbsb-encryption.php   # Data encryption
│   ├── class-unbsb-rate-limiter.php # Rate limiting
│   ├── class-unbsb-security-logger.php # Security logging
│   ├── class-unbsb-captcha.php      # CAPTCHA integration
│   ├── class-unbsb-export-import.php # Data export/import
│   ├── models/
│   │   ├── class-unbsb-booking.php
│   │   ├── class-unbsb-booking-service.php
│   │   ├── class-unbsb-category.php
│   │   ├── class-unbsb-customer.php
│   │   ├── class-unbsb-promo-code.php
│   │   ├── class-unbsb-service.php
│   │   └── class-unbsb-staff.php
│   └── sms/
│       ├── class-unbsb-sms-provider.php
│       └── class-unbsb-sms-netgsm.php
│
├── admin/                           # Admin panel
│   ├── class-unbsb-admin.php        # Admin main class
│   ├── partials/                    # Admin template parts
│   │   ├── admin-dashboard.php
│   │   ├── admin-bookings.php
│   │   ├── admin-new-booking.php
│   │   ├── admin-calendar.php
│   │   ├── admin-categories.php
│   │   ├── admin-services.php
│   │   ├── admin-staff.php
│   │   ├── admin-staff-schedule.php
│   │   ├── admin-staff-bookings.php
│   │   ├── admin-staff-schedule-own.php
│   │   ├── admin-customers.php
│   │   ├── admin-settings.php
│   │   ├── admin-email-templates.php
│   │   ├── admin-export-import.php
│   │   └── admin-promo-codes.php
│   ├── css/
│   │   └── unbsb-admin.css
│   └── js/
│       └── unbsb-admin.js
│
├── public/                          # Frontend
│   ├── class-unbsb-public.php       # Public main class
│   ├── partials/                    # Public template parts
│   │   ├── booking-form.php
│   │   ├── booking-manage.php
│   │   ├── account.php
│   │   ├── services-list.php
│   │   ├── service-card-inner.php
│   │   └── staff-list.php
│   ├── css/
│   │   └── unbsb-public.css
│   └── js/
│       └── unbsb-public.js
│
└── languages/                       # Translation files
    ├── unbelievable-salon-booking.pot       # POT template (English source)
    ├── unbelievable-salon-booking-tr_TR.po  # Turkish translation
    ├── unbelievable-salon-booking-tr_TR.mo  # Turkish compiled
    ├── unbelievable-salon-booking-bg_BG.po  # Bulgarian translation
    └── unbelievable-salon-booking-bg_BG.mo  # Bulgarian compiled
```

## Database Schema

### Tables
Prefix: `{wp_prefix}unbsb_`

#### unbsb_categories (Categories)
```sql
CREATE TABLE {prefix}unbsb_categories (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3788d8',
    icon VARCHAR(100) DEFAULT '',
    status VARCHAR(20) DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### unbsb_services (Services)
```sql
CREATE TABLE {prefix}unbsb_services (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id BIGINT(20) UNSIGNED,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL DEFAULT 30,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    discounted_price DECIMAL(10,2) DEFAULT NULL,
    buffer_before INT DEFAULT 0,
    buffer_after INT DEFAULT 0,
    color VARCHAR(7) DEFAULT '#3788d8',
    status VARCHAR(20) DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY category_id (category_id)
);
```

#### unbsb_staff (Staff)
```sql
CREATE TABLE {prefix}unbsb_staff (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    bio TEXT,
    avatar_url VARCHAR(500),
    status VARCHAR(20) DEFAULT 'active',
    sort_order INT DEFAULT 0,
    salary_type VARCHAR(20) DEFAULT 'percentage',
    salary_percentage DECIMAL(5,2) DEFAULT 0,
    salary_fixed DECIMAL(10,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id)
);
```

#### unbsb_staff_services (Staff-Service Relationship)
```sql
CREATE TABLE {prefix}unbsb_staff_services (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED NOT NULL,
    service_id BIGINT(20) UNSIGNED NOT NULL,
    custom_price DECIMAL(10,2),
    custom_duration INT,
    PRIMARY KEY (id),
    UNIQUE KEY staff_service (staff_id, service_id)
);
```

#### unbsb_working_hours (Working Hours)
```sql
CREATE TABLE {prefix}unbsb_working_hours (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_working TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    KEY staff_day (staff_id, day_of_week)
);
```

#### unbsb_breaks (Breaks)
```sql
CREATE TABLE {prefix}unbsb_breaks (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    PRIMARY KEY (id)
);
```

#### unbsb_holidays (Holidays/Closed Days)
```sql
CREATE TABLE {prefix}unbsb_holidays (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED,
    date DATE NOT NULL,
    reason VARCHAR(255),
    PRIMARY KEY (id),
    KEY staff_date (staff_id, date)
);
```

#### unbsb_bookings (Bookings)
```sql
CREATE TABLE {prefix}unbsb_bookings (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    service_id BIGINT(20) UNSIGNED NOT NULL,
    staff_id BIGINT(20) UNSIGNED NOT NULL,
    customer_id BIGINT(20) UNSIGNED,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50),
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_duration INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'pending',
    notes TEXT,
    internal_notes TEXT,
    promo_code_id BIGINT(20) UNSIGNED DEFAULT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    paid_amount DECIMAL(10,2) DEFAULT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    token VARCHAR(64),
    reschedule_count INT DEFAULT 0,
    original_booking_id BIGINT(20) UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY service_id (service_id),
    KEY staff_id (staff_id),
    KEY customer_id (customer_id),
    KEY booking_date (booking_date),
    KEY status (status),
    KEY original_booking_id (original_booking_id),
    UNIQUE KEY token (token)
);
```

#### unbsb_customers (Customers)
```sql
CREATE TABLE {prefix}unbsb_customers (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY email (email),
    KEY user_id (user_id)
);
```

#### unbsb_booking_services (Multi-Service Support)
```sql
CREATE TABLE {prefix}unbsb_booking_services (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id BIGINT(20) UNSIGNED NOT NULL,
    service_id BIGINT(20) UNSIGNED NOT NULL,
    staff_id BIGINT(20) UNSIGNED,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL,
    sort_order INT DEFAULT 0,
    PRIMARY KEY (id),
    KEY booking_id (booking_id),
    KEY service_id (service_id)
);
```

#### unbsb_staff_earnings (Staff Earnings/Commission)
```sql
CREATE TABLE {prefix}unbsb_staff_earnings (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED NOT NULL,
    booking_id BIGINT(20) UNSIGNED,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    type VARCHAR(20) DEFAULT 'commission',
    period VARCHAR(7),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY staff_id (staff_id),
    KEY booking_id (booking_id),
    KEY period (period)
);
```

#### unbsb_email_templates (Email Templates)
```sql
CREATE TABLE {prefix}unbsb_email_templates (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY type (type)
);
```

#### unbsb_sms_queue (SMS Queue)
```sql
CREATE TABLE {prefix}unbsb_sms_queue (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id BIGINT(20) UNSIGNED NOT NULL,
    phone VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    scheduled_at DATETIME NOT NULL,
    sent_at DATETIME,
    status VARCHAR(20) DEFAULT 'pending',
    attempts INT DEFAULT 0,
    provider_response TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY booking_id (booking_id),
    KEY scheduled_at (scheduled_at),
    KEY status (status)
);
```

#### unbsb_sms_templates (SMS Templates)
```sql
CREATE TABLE {prefix}unbsb_sms_templates (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY type (type)
);
```

#### unbsb_promo_codes (Promo Codes)
```sql
CREATE TABLE {prefix}unbsb_promo_codes (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL,
    description TEXT,
    discount_type VARCHAR(20) NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    first_time_only TINYINT(1) DEFAULT 0,
    min_services INT DEFAULT 0,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_uses INT DEFAULT 0,
    max_uses_per_customer INT DEFAULT 0,
    applicable_services TEXT,
    applicable_categories TEXT,
    start_date DATE,
    end_date DATE,
    status VARCHAR(20) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY code (code),
    KEY status (status)
);
```

#### unbsb_promo_code_usage (Promo Code Usage Tracking)
```sql
CREATE TABLE {prefix}unbsb_promo_code_usage (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    promo_code_id BIGINT(20) UNSIGNED NOT NULL,
    booking_id BIGINT(20) UNSIGNED NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY promo_code_id (promo_code_id),
    KEY booking_id (booking_id),
    KEY customer_email (customer_email)
);
```

## Security Rules

### Data Validation
- Use `sanitize_*` functions for all inputs
- `sanitize_text_field()` - text inputs
- `sanitize_email()` - email addresses
- `absint()` - positive integers
- `sanitize_textarea_field()` - textarea
- `wp_kses_post()` - HTML content

### SQL Security
- **NEVER** write raw SQL queries
- Always use `$wpdb->prepare()`
```php
$wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}unbsb_bookings WHERE id = %d",
    $booking_id
);
```

### Nonce Verification
- Use nonce for all form operations
```php
// Create
wp_nonce_field( 'unbsb_booking_action', 'unbsb_booking_nonce' );

// Verify
if ( ! wp_verify_nonce( $_POST['unbsb_booking_nonce'], 'unbsb_booking_action' ) ) {
    die( 'Security check failed' );
}
```

### Permission Check
- Use `current_user_can()` for admin operations
```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized access' );
}
```

### Escape Outputs
- `esc_html()` - text within HTML
- `esc_attr()` - HTML attributes
- `esc_url()` - URLs
- `esc_js()` - within JavaScript
- `wp_kses()` - controlled HTML

## AJAX Operations

### Admin AJAX
```php
// Hook registration
add_action( 'wp_ajax_unbsb_create_booking', array( $this, 'ajax_create_booking' ) );
add_action( 'wp_ajax_nopriv_unbsb_create_booking', array( $this, 'ajax_create_booking' ) );

// Handler
public function ajax_create_booking() {
    check_ajax_referer( 'unbsb_ajax_nonce', 'nonce' );

    // Process...

    wp_send_json_success( $data );
    // or
    wp_send_json_error( $message );
}
```

### REST API (Preferred)
```php
// Endpoint registration
add_action( 'rest_api_init', function() {
    register_rest_route( 'unbsb/v1', '/bookings', array(
        'methods'             => 'POST',
        'callback'            => 'unbsb_create_booking',
        'permission_callback' => function() {
            return true; // or permission check
        },
    ) );
} );
```

## Shortcode Usage

### Booking Form
```
[unbsb_booking_form]
[unbsb_booking_form service="1" staff="2"]
[unbsb_booking_form category="haircut"]
```

### Staff List
```
[unbsb_staff_list]
[unbsb_staff_list show_services="yes"]
```

### Service List
```
[unbsb_services]
[unbsb_services category="massage"]
```

## Hooks (Extensibility)

### Actions
```php
do_action( 'unbsb_before_booking_created', $booking_data );
do_action( 'unbsb_after_booking_created', $booking_id, $booking_data );
do_action( 'unbsb_booking_status_changed', $booking_id, $new_status, $old_status );
do_action( 'unbsb_before_send_notification', $booking_id, $type );
```

### Filters
```php
apply_filters( 'unbsb_filter_available_slots', $slots, $date, $staff_id );
apply_filters( 'unbsb_filter_booking_price', $price, $service_id, $staff_id );
apply_filters( 'unbsb_filter_email_template', $template, $type );
apply_filters( 'unbsb_filter_working_hours', $hours, $staff_id );
```

## Settings API Usage

Options prefix: `unbsb_`

### General Settings
- `unbsb_time_slot_interval` - Slot interval (minutes)
- `unbsb_booking_lead_time` - Minimum booking lead time
- `unbsb_booking_future_days` - How many days ahead for booking
- `unbsb_currency` - Currency
- `unbsb_date_format` - Date format
- `unbsb_time_format` - Time format

### Notification Settings
- `unbsb_admin_email` - Admin email
- `unbsb_email_from_name` - Sender name
- `unbsb_email_from_address` - Sender email
- `unbsb_sms_enabled` - SMS enabled
- `unbsb_sms_provider` - SMS provider

## Testing Requirements

### Unit Tests
- Use PHPUnit
- WordPress test framework integration
- Target minimum 60% code coverage

### Test Coverage
- Booking CRUD operations
- Slot calculation algorithm
- Conflict detection
- Email delivery
- Nonce/permission checks

## Performance Guidelines

- Use Transient API for infrequently changing data
- Add object caching support
- Avoid unnecessary queries
- Load assets only on required pages
- Use lazy loading

## i18n (Internationalization)

### Default Language
**IMPORTANT:** The plugin's default language is **English**. All source code strings must be written in English.

### Text Domain
- Text domain: `unbelievable-salon-booking`
- Load text domain in main plugin file

### Writing Translatable Strings
All user-facing strings must be wrapped in translation functions:

```php
// Simple strings
__( 'Book Now', 'unbelievable-salon-booking' );
_e( 'Select Service', 'unbelievable-salon-booking' );

// Strings with placeholders (add translators comment)
/* translators: %d: booking ID number */
sprintf( __( 'Booking #%d confirmed', 'unbelievable-salon-booking' ), $id );

// Plural strings
/* translators: %d: number of bookings */
sprintf( _n( '%d booking', '%d bookings', $count, 'unbelievable-salon-booking' ), $count );

// Escape and translate
esc_html__( 'Settings', 'unbelievable-salon-booking' );
esc_html_e( 'Save Changes', 'unbelievable-salon-booking' );
esc_attr__( 'Enter name', 'unbelievable-salon-booking' );
```

### JavaScript Localization
Pass translatable strings to JavaScript via `wp_localize_script()`:

```php
wp_localize_script( 'unbsb-admin', 'unbsbAdmin', array(
    'strings' => array(
        'confirm_delete' => __( 'Are you sure?', 'unbelievable-salon-booking' ),
        'saving'         => __( 'Saving...', 'unbelievable-salon-booking' ),
        'saved'          => __( 'Saved!', 'unbelievable-salon-booking' ),
    ),
) );
```

Then use in JavaScript:
```javascript
alert( unbsbAdmin.strings.confirm_delete );
```

### Translation Files

#### Supported Languages
- **English** - Default/source language (in source code)
- **Turkish (tr_TR)** - Translation
- **Bulgarian (bg_BG)** - Translation

#### File Structure
```
languages/
├── unbelievable-salon-booking.pot       # POT template (English source, generated)
├── unbelievable-salon-booking-tr_TR.po  # Turkish translation (source)
├── unbelievable-salon-booking-tr_TR.mo  # Turkish translation (compiled)
├── unbelievable-salon-booking-bg_BG.po  # Bulgarian translation (source)
└── unbelievable-salon-booking-bg_BG.mo  # Bulgarian translation (compiled)
```

#### Generating POT File
Use WP-CLI to generate/update the POT template:

```bash
# Navigate to plugin directory
cd wp-content/plugins/unbelievable-salon-booking

# Generate POT file
wp i18n make-pot . languages/unbelievable-salon-booking.pot --domain=unbelievable-salon-booking --exclude=vendor,node_modules
```

#### Creating Translations
1. Copy POT file and rename: `unbelievable-salon-booking-{locale}.po`
2. Translate strings using Poedit or similar tool
3. Generate MO file: `wp i18n make-mo languages/`

#### Locale Codes
- Turkish: `tr_TR`
- Bulgarian: `bg_BG`

### Best Practices
1. **Never hard-code strings** - Always use translation functions
2. **Use context when needed** - `_x( 'Post', 'noun', 'unbelievable-salon-booking' )`
3. **Add translators comments** - For placeholders and ambiguous strings
4. **Keep sentences together** - Don't split sentences across multiple translation calls
5. **Avoid HTML in strings** - Use placeholders instead
6. **Update POT file** - Regenerate after adding new strings

## Version History

### v1.7.0
- FullCalendar integration (admin + staff portal)
- Staff portal (My Bookings + My Schedule)
- Staff WordPress user management (create/link/unlink)
- Staff salary/commission system (percentage/fixed/mix)
- Booking completion with payment recording
- Email notification fix
- Full data export/import
- Admin new booking page with customer search
- Multi-service category grouping
- Staff custom pricing per service
- Promo codes
- 24h time format fix
- Staff data leak fix (public API)

### v1.0.0
- Initial release
- Basic booking functionality
- Admin panel
- Email notifications
- SMS notifications
- SEO features
- Security features (CAPTCHA, rate limiting, encryption)

---

**Important Notes:**
1. Update changelog with each commit
2. Use semantic versioning
3. Follow WordPress coding standards
4. Perform security audits regularly
5. Maintain backward compatibility
