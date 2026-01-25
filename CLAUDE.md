# Appointment General - WordPress Booking Plugin

## Proje Hakkında
Berberler, güzellik salonları ve servis sağlayıcılar için geliştirilmiş WordPress randevu/booking sistemi.

## Teknoloji Stack
- **Backend:** PHP 8.0+
- **Frontend:** Vanilla JS, Alpine.js (opsiyonel)
- **Veritabanı:** WordPress $wpdb (MySQL)
- **CSS:** Tailwind CSS veya vanilla CSS

## WordPress Kodlama Standartları

### PHP Standartları
- WordPress PHP Coding Standards'a uy: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/
- Girintiler için **tab** kullan (space değil)
- Açılış parantezi aynı satırda olmalı
- Yoda conditions kullan: `if ( 'value' === $variable )`
- Array syntax: `array()` kullan, `[]` değil (PHP 5.4+ uyumluluk için opsiyonel)

### Dosya İsimlendirme
- Sınıf dosyaları: `class-{class-name}.php`
- Admin dosyaları: `admin-{feature}.php`
- Public dosyaları: `public-{feature}.php`
- Template dosyaları: `template-{name}.php`

### Sınıf İsimlendirme
- PascalCase kullan: `Appointment_General_Booking`
- Prefix kullan: `AG_` veya `Appointment_General_`

### Fonksiyon İsimlendirme
- snake_case kullan
- Prefix kullan: `ag_` veya `appointment_general_`
- Örnek: `ag_get_available_slots()`

### Hook İsimlendirme
- Actions: `ag_{action_name}`
- Filters: `ag_filter_{filter_name}`

## Dizin Yapısı

```
appointment-general/
├── appointment-general.php          # Ana plugin dosyası
├── uninstall.php                    # Uninstall hook
├── readme.txt                       # WordPress.org readme
├── CLAUDE.md                        # Bu dosya
│
├── includes/                        # Core PHP sınıfları
│   ├── class-ag-loader.php         # Hook ve filter loader
│   ├── class-ag-activator.php      # Aktivasyon işlemleri
│   ├── class-ag-deactivator.php    # Deaktivasyon işlemleri
│   ├── class-ag-i18n.php           # Internationalization
│   ├── class-ag-booking.php        # Randevu CRUD işlemleri
│   ├── class-ag-service.php        # Hizmet yönetimi
│   ├── class-ag-staff.php          # Personel yönetimi
│   ├── class-ag-customer.php       # Müşteri yönetimi
│   ├── class-ag-calendar.php       # Takvim işlemleri
│   ├── class-ag-notification.php   # E-posta/SMS bildirimleri
│   └── class-ag-settings.php       # Ayarlar yönetimi
│
├── admin/                           # Admin panel
│   ├── class-ag-admin.php          # Admin ana sınıfı
│   ├── partials/                   # Admin template parçaları
│   │   ├── admin-dashboard.php
│   │   ├── admin-bookings.php
│   │   ├── admin-services.php
│   │   ├── admin-staff.php
│   │   └── admin-settings.php
│   ├── css/
│   │   └── ag-admin.css
│   └── js/
│       └── ag-admin.js
│
├── public/                          # Frontend
│   ├── class-ag-public.php         # Public ana sınıfı
│   ├── partials/                   # Public template parçaları
│   │   ├── booking-form.php
│   │   ├── booking-calendar.php
│   │   └── booking-confirmation.php
│   ├── css/
│   │   └── ag-public.css
│   └── js/
│       └── ag-public.js
│
├── templates/                       # Override edilebilir temalar
│   ├── single-booking.php
│   └── archive-booking.php
│
├── languages/                       # Çeviri dosyaları
│   └── appointment-general.pot
│
└── assets/                          # Statik dosyalar
    └── images/
```

## Veritabanı Şeması

### Tablolar
Prefix: `{wp_prefix}ag_`

#### ag_categories (Kategoriler)
```sql
CREATE TABLE {prefix}ag_categories (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3788d8',
    icon VARCHAR(100) DEFAULT '',
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### ag_services (Hizmetler)
```sql
CREATE TABLE {prefix}ag_services (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id BIGINT(20) UNSIGNED,           -- Kategori ID (opsiyonel)
    name VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL DEFAULT 30,          -- dakika
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    buffer_before INT DEFAULT 0,               -- dakika
    buffer_after INT DEFAULT 0,                -- dakika
    color VARCHAR(7) DEFAULT '#3788d8',
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY category_id (category_id)
);
```

#### ag_staff (Personel)
```sql
CREATE TABLE {prefix}ag_staff (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED,               -- WP user ID (opsiyonel)
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    bio TEXT,
    avatar_url VARCHAR(500),
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id)
);
```

#### ag_staff_services (Personel-Hizmet İlişkisi)
```sql
CREATE TABLE {prefix}ag_staff_services (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED NOT NULL,
    service_id BIGINT(20) UNSIGNED NOT NULL,
    custom_price DECIMAL(10,2),                -- NULL ise servis fiyatı
    custom_duration INT,                        -- NULL ise servis süresi
    PRIMARY KEY (id),
    UNIQUE KEY staff_service (staff_id, service_id)
);
```

#### ag_working_hours (Çalışma Saatleri)
```sql
CREATE TABLE {prefix}ag_working_hours (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL,              -- 0=Pazar, 6=Cumartesi
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_working TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id),
    KEY staff_day (staff_id, day_of_week)
);
```

#### ag_breaks (Molalar)
```sql
CREATE TABLE {prefix}ag_breaks (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED NOT NULL,
    day_of_week TINYINT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    PRIMARY KEY (id)
);
```

#### ag_holidays (Tatiller/Kapalı Günler)
```sql
CREATE TABLE {prefix}ag_holidays (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    staff_id BIGINT(20) UNSIGNED,              -- NULL ise tüm personel
    date DATE NOT NULL,
    reason VARCHAR(255),
    PRIMARY KEY (id),
    KEY staff_date (staff_id, date)
);
```

#### ag_bookings (Randevular)
```sql
CREATE TABLE {prefix}ag_bookings (
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
    status ENUM('pending','confirmed','cancelled','completed','no_show') DEFAULT 'pending',
    notes TEXT,
    internal_notes TEXT,
    token VARCHAR(64) UNIQUE,                  -- Yönetim linki için
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY service_id (service_id),
    KEY staff_id (staff_id),
    KEY customer_id (customer_id),
    KEY booking_date (booking_date),
    KEY status (status)
);
```

#### ag_customers (Müşteriler)
```sql
CREATE TABLE {prefix}ag_customers (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email),
    KEY user_id (user_id)
);
```

## Güvenlik Kuralları

### Veri Doğrulama
- Tüm inputlar için `sanitize_*` fonksiyonları kullan
- `sanitize_text_field()` - text inputlar
- `sanitize_email()` - email adresleri
- `absint()` - pozitif integer
- `sanitize_textarea_field()` - textarea
- `wp_kses_post()` - HTML içerik

### SQL Güvenliği
- **ASLA** raw SQL query yazma
- Her zaman `$wpdb->prepare()` kullan
```php
$wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}ag_bookings WHERE id = %d",
    $booking_id
);
```

### Nonce Kontrolü
- Tüm form işlemlerinde nonce kullan
```php
// Oluşturma
wp_nonce_field( 'ag_booking_action', 'ag_booking_nonce' );

// Doğrulama
if ( ! wp_verify_nonce( $_POST['ag_booking_nonce'], 'ag_booking_action' ) ) {
    die( 'Security check failed' );
}
```

### Yetki Kontrolü
- Admin işlemleri için `current_user_can()` kullan
```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized access' );
}
```

### Escape Outputları
- `esc_html()` - HTML içinde text
- `esc_attr()` - HTML attribute
- `esc_url()` - URL
- `esc_js()` - JavaScript içinde
- `wp_kses()` - Kontrollü HTML

## AJAX İşlemleri

### Admin AJAX
```php
// Hook kaydı
add_action( 'wp_ajax_ag_create_booking', array( $this, 'ajax_create_booking' ) );
add_action( 'wp_ajax_nopriv_ag_create_booking', array( $this, 'ajax_create_booking' ) );

// Handler
public function ajax_create_booking() {
    check_ajax_referer( 'ag_ajax_nonce', 'nonce' );

    // İşlem...

    wp_send_json_success( $data );
    // veya
    wp_send_json_error( $message );
}
```

### REST API (Tercih Edilen)
```php
// Endpoint kaydı
add_action( 'rest_api_init', function() {
    register_rest_route( 'ag/v1', '/bookings', array(
        'methods'             => 'POST',
        'callback'            => 'ag_create_booking',
        'permission_callback' => function() {
            return true; // veya yetki kontrolü
        },
    ) );
} );
```

## Shortcode Kullanımı

### Booking Formu
```
[ag_booking_form]
[ag_booking_form service="1" staff="2"]
[ag_booking_form category="haircut"]
```

### Personel Listesi
```
[ag_staff_list]
[ag_staff_list show_services="yes"]
```

### Hizmet Listesi
```
[ag_services]
[ag_services category="massage"]
```

## Hooks (Extensibility)

### Actions
```php
do_action( 'ag_before_booking_created', $booking_data );
do_action( 'ag_after_booking_created', $booking_id, $booking_data );
do_action( 'ag_booking_status_changed', $booking_id, $new_status, $old_status );
do_action( 'ag_before_send_notification', $booking_id, $type );
```

### Filters
```php
apply_filters( 'ag_filter_available_slots', $slots, $date, $staff_id );
apply_filters( 'ag_filter_booking_price', $price, $service_id, $staff_id );
apply_filters( 'ag_filter_email_template', $template, $type );
apply_filters( 'ag_filter_working_hours', $hours, $staff_id );
```

## Settings API Kullanımı

Options prefix: `ag_`

### Temel Ayarlar
- `ag_time_slot_interval` - Slot aralığı (dakika)
- `ag_booking_lead_time` - Minimum randevu süresi
- `ag_booking_future_days` - Kaç gün ilerisi için randevu
- `ag_currency` - Para birimi
- `ag_date_format` - Tarih formatı
- `ag_time_format` - Saat formatı

### Bildirim Ayarları
- `ag_admin_email` - Admin email
- `ag_email_from_name` - Gönderen adı
- `ag_email_from_address` - Gönderen email
- `ag_sms_enabled` - SMS aktif mi
- `ag_sms_provider` - SMS sağlayıcı

## Test Gereksinimleri

### Unit Testler
- PHPUnit kullan
- WordPress test framework entegrasyonu
- Minimum %60 code coverage hedefle

### Test Edilecekler
- Booking CRUD işlemleri
- Slot hesaplama algoritması
- Çakışma kontrolü
- Email gönderimi
- Nonce/yetki kontrolleri

## Performans Kuralları

- Transient API kullan sık değişmeyen veriler için
- Object caching desteği ekle
- Gereksiz query'lerden kaçın
- Assets'leri sadece gerekli sayfalarda yükle
- Lazy loading kullan

## i18n (Çoklu Dil)

- Text domain: `appointment-general`
- Tüm string'leri çevrilebilir yap:
```php
__( 'Book Now', 'appointment-general' );
_e( 'Select Service', 'appointment-general' );
sprintf( __( 'Booking #%d confirmed', 'appointment-general' ), $id );
```

## Versiyon Geçmişi

### v1.0.0
- İlk sürüm
- Temel booking işlevselliği
- Admin panel
- Email bildirimleri

---

**Önemli Notlar:**
1. Her commit'te changelog güncelle
2. Semantic versioning kullan
3. WordPress coding standards'a uy
4. Security audit'leri düzenli yap
5. Backward compatibility'yi koru
