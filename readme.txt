=== Unbelievable Salon Booking ===
Contributors: zgrkaralar
Tags: appointment, booking, scheduler, reservation, salon
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.6.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional appointment booking system for barbers, beauty salons, spas and service providers.

== Description ==

Unbelievable Salon Booking is a comprehensive appointment management plugin designed for service-based businesses. Perfect for barbers, beauty salons, spas, wellness centers, consultants, and any business that needs to manage appointments efficiently.

= Key Features =

* **Service Management** – Create unlimited services with custom duration and pricing
* **Category Organization** – Group services into categories for better organization
* **Staff Management** – Add team members with individual schedules and services
* **Working Hours** – Set flexible working hours for each staff member
* **Breaks & Holidays** – Define lunch breaks and days off
* **Customer Database** – Track customer history and contact information
* **Email Notifications** – Automatic booking confirmations and reminders
* **SMS Notifications** – Optional SMS alerts via NetGSM integration
* **Calendar View** – Visual calendar for easy appointment management
* **Booking Form** – Clean, responsive booking form via shortcode
* **Booking Management** – Customers can view and cancel their bookings
* **Multi-language** – Translation ready with 6 languages included

= Included Languages =

* English
* Turkish (Türkçe)
* German (Deutsch)
* French (Français)
* Russian (Русский)
* Bulgarian (Български)

= Shortcodes =

* `[unbsb_booking_form]` – Display the booking form
* `[unbsb_services]` – Show available services list
* `[unbsb_staff_list]` – Display staff members
* `[unbsb_manage_booking]` – Booking management page for customers

= Perfect For =

* Barbershops
* Hair salons
* Beauty salons
* Spas & wellness centers
* Massage therapists
* Nail salons
* Tattoo studios
* Consultants
* Any appointment-based business

= Requirements =

* WordPress 5.8 or higher
* PHP 8.0 or higher
* MySQL 5.6 or higher

= External Services =

This plugin connects to the following external services:

**NetGSM SMS API**
When SMS notifications are enabled and configured in the plugin settings, the plugin sends HTTP requests to [NetGSM](https://www.netgsm.com.tr/) API (`https://api.netgsm.com.tr/`) to deliver SMS messages to customers (booking confirmations, reminders, cancellations). No data is sent unless the site administrator explicitly enables SMS notifications and provides their own NetGSM API credentials.

* Service URL: [https://api.netgsm.com.tr/](https://api.netgsm.com.tr/)
* Terms of Service: [https://www.netgsm.com.tr/sozlesmeler](https://www.netgsm.com.tr/sozlesmeler)
* Privacy Policy: [https://www.netgsm.com.tr/gizlilikPolitikasi](https://www.netgsm.com.tr/gizlilikPolitikasi)

Data transmitted to NetGSM: Customer phone number and the SMS message content (booking details such as date, time, service name, and staff name).

== Installation ==

1. Upload the `unbelievable-salon-booking` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Unbelievable Salon Booking' in the admin menu to configure the plugin
4. Add your service categories and services
5. Add your staff members and set their working hours
6. Use the `[unbsb_booking_form]` shortcode to display the booking form on any page

== Frequently Asked Questions ==

= How do I display the booking form? =

Use the shortcode `[unbsb_booking_form]` on any page or post. You can also specify parameters like `[unbsb_booking_form service="1" staff="2"]`.

= Can I have multiple staff members? =

Yes, you can add unlimited staff members, each with their own schedule, services, and custom pricing.

= Does it support service categories? =

Yes, you can organize your services into categories for better presentation.

= Can staff members have different prices for the same service? =

Yes, each staff member can have custom pricing and duration for any service they offer.

= Is it mobile friendly? =

Yes, the booking form is fully responsive and works perfectly on all devices including smartphones and tablets.

= Can I customize email notifications? =

Yes, you can customize email templates for booking confirmations, reminders, and cancellations from the Settings page.

= Does it support SMS notifications? =

Yes, SMS notifications are supported via NetGSM integration. More SMS providers will be added in future updates.

= Can customers manage their bookings? =

Yes, customers receive a unique link to view and cancel their bookings.

= Is it translation ready? =

Yes, the plugin is fully translatable using standard WordPress translation methods. Turkish, English, German, French, Russian, and Bulgarian translations are included.

= Does it work with any theme? =

Yes, Unbelievable Salon Booking is designed to work with any properly coded WordPress theme.

= Is there a Pro version? =

A Pro version with additional features like payment integration, Google Calendar sync, and more SMS providers is planned for future release.

== Screenshots ==

1. Frontend booking form – Clean and responsive design
2. Admin dashboard – Overview of bookings and statistics
3. Calendar view – Visual appointment management
4. Service management – Create and organize services
5. Staff management – Team members and schedules
6. Working hours – Flexible schedule configuration
7. Settings page – Customize plugin behavior

== Changelog ==

= 1.2.0 =
* Added full data export/import functionality
* Export all plugin data (categories, services, staff, customers, bookings, settings) as JSON
* Import data with merge or replace modes
* Admin panel export/import page with drag & drop file upload
* Data validation and security checks for import
* Memory limit and file size checks
* Transaction-based import for data integrity

= 1.1.0 – 2026-03-08 =
* Added: Promo code / coupon system with percentage, fixed amount, and "cheapest service free" discount types
* Added: Per-customer usage limits for promo codes with login requirement
* Added: Discounted price field for services
* Added: Redesigned service selection cards – checkbox on the left, prominent price display on the right with discount badge
* Added: Self-hosted update checker (wp-update-sdk)
* Improved: Booking summary now shows discount details when a promo code is applied

= 1.0.2 – 2026-02-09 =
* Fixed: Text domain changed to match WordPress.org plugin slug (easy-salon-booking)
* Fixed: Removed Plugin URI to avoid duplicate URI with Author URI

= 1.0.1 – 2026-02-09 =
* Fixed: Bundle Chart.js locally instead of CDN
* Fixed: Replaced all inline scripts with wp_localize_script and wp_add_inline_script
* Fixed: Moved inline styles to enqueued CSS file
* Fixed: REST API permission_callback for available-slots endpoint
* Fixed: Plugin URI added to plugin header
* Fixed: readme.txt shortcode references and installation path
* Added: External Services section documenting NetGSM SMS API
* Added: Booking management shortcode [unbsb_manage_booking] to readme

= 1.0.0 – 2025-01-25 =
* Initial release
* Service management with categories, custom duration and pricing
* Staff management with individual schedules
* Working hours configuration per staff member
* Break time and holiday management
* Customer database with booking history
* Email notifications (confirmation, reminder, cancellation)
* SMS notifications via NetGSM
* Interactive calendar view
* Responsive booking form shortcode
* Booking management for customers
* Multi-language support (TR, EN, DE, FR, RU, BG)

== Upgrade Notice ==

= 1.1.0 =
New promo code system, discounted prices, redesigned service cards, and self-hosted update checker.

= 1.0.2 =
Text domain corrected to match WordPress.org slug. No functionality changes.

= 1.0.1 =
Security and coding standards improvements. All assets now bundled locally, inline scripts removed.

= 1.0.0 =
Initial release of Unbelievable Salon Booking. Welcome!
