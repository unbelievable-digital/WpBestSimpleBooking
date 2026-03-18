# WordPress.org Plugin Dizinine Ekleme Kılavuzu

Bu kılavuz, Easy Salon Booking plugin'ini WordPress.org resmi dizinine ekleme sürecini adım adım açıklar.

---

## 1. Ön Gereksinimler

### WordPress.org Hesabı
- https://wordpress.org/support/register.php adresinden hesap oluştur
- Email adresini doğrula
- Profil bilgilerini doldur

### Plugin Gereksinimleri
- [ ] GPL v2 veya uyumlu lisans
- [ ] Benzersiz plugin adı (slug)
- [ ] Güvenlik standartlarına uygunluk
- [ ] WordPress coding standards'a uygunluk
- [ ] Çalışan, test edilmiş kod

---

## 2. Plugin Hazırlığı

### 2.1 Lisans Dosyası
Plugin klasörüne `LICENSE` veya `license.txt` dosyası ekle:

```
GNU GENERAL PUBLIC LICENSE
Version 2, June 1991
...
```

### 2.2 Ana Plugin Dosyası Header'ı
`easy-salon-booking.php` dosyasının başında olması gerekenler:

```php
<?php
/**
 * Plugin Name:       Easy Salon Booking
 * Plugin URI:        https://example.com/easy-salon-booking
 * Description:       Professional appointment booking system for salons, barbershops, and service providers.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       easy-salon-booking
 * Domain Path:       /languages
 */
```

### 2.3 Güvenlik Kontrolü
Her PHP dosyasının başına ekle:

```php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
```

---

## 3. readme.txt Dosyası (Kritik!)

WordPress.org, plugin sayfasını `readme.txt` dosyasından oluşturur.

### 3.1 Temel Yapı

```txt
=== Easy Salon Booking ===
Contributors: your-wp-username
Donate link: https://example.com/donate
Tags: appointment, booking, salon, barbershop, scheduling
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional appointment booking system for salons, barbershops, and service providers.

== Description ==

Easy Salon Booking is a comprehensive appointment management solution designed for:

* Barbershops
* Beauty salons
* Spas and wellness centers
* Any service-based business

**Key Features:**

* Easy online booking form
* Staff management with individual schedules
* Service categories and pricing
* Email notifications
* Calendar view for appointments
* Customer management
* Multi-language support

**Pro Features (Coming Soon):**

* SMS notifications
* Payment integration
* Google Calendar sync
* Custom booking forms

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/easy-salon-booking/` directory, or install through WordPress plugins screen.
2. Activate the plugin through 'Plugins' screen in WordPress.
3. Go to Easy Salon Booking menu to configure the plugin.
4. Add your services and staff members.
5. Use shortcode `[esb_booking_form]` to display the booking form.

== Frequently Asked Questions ==

= How do I display the booking form? =

Use the shortcode `[esb_booking_form]` on any page or post.

= Can I have multiple staff members? =

Yes, you can add unlimited staff members and assign services to each.

= Does it send email notifications? =

Yes, both admin and customers receive email notifications for new bookings.

= Is it mobile responsive? =

Yes, the booking form is fully responsive and works on all devices.

= Can customers cancel their appointments? =

Yes, customers receive a unique link to manage their booking.

== Screenshots ==

1. Booking form on frontend
2. Admin dashboard
3. Service management
4. Staff management
5. Calendar view
6. Settings page

== Changelog ==

= 1.0.0 =
* Initial release
* Booking form with service and staff selection
* Admin dashboard
* Email notifications
* Multi-language support (EN, TR, DE, FR, RU, BG)

== Upgrade Notice ==

= 1.0.0 =
Initial release of Easy Salon Booking.
```

### 3.2 readme.txt Doğrulama
https://wordpress.org/plugins/developers/readme-validator/ adresinde test et.

---

## 4. Assets (Görseller)

WordPress.org plugin sayfası için görseller hazırla.

### 4.1 Klasör Yapısı
SVN'de `assets/` klasörü oluştur (plugin klasörünün dışında):

```
/assets/
├── banner-772x250.png      # Plugin banner (zorunlu)
├── banner-1544x500.png     # Retina banner (önerilen)
├── icon-128x128.png        # Plugin icon (zorunlu)
├── icon-256x256.png        # Retina icon (önerilen)
├── screenshot-1.png        # Screenshot 1
├── screenshot-2.png        # Screenshot 2
└── ...
```

### 4.2 Görsel Özellikleri

| Dosya | Boyut | Format | Açıklama |
|-------|-------|--------|----------|
| banner-772x250.png | 772x250 px | PNG/JPG | Ana banner |
| banner-1544x500.png | 1544x500 px | PNG/JPG | Retina banner |
| icon-128x128.png | 128x128 px | PNG | Plugin ikonu |
| icon-256x256.png | 256x256 px | PNG | Retina ikon |
| screenshot-X.png | Herhangi | PNG/JPG | Ekran görüntüleri |

---

## 5. Başvuru Süreci

### 5.1 Plugin Paketleme
```bash
# Gereksiz dosyaları temizle
rm -rf node_modules/
rm -rf .git/
rm -rf .github/
rm -rf tests/
rm -f .gitignore
rm -f .editorconfig
rm -f composer.lock
rm -f package-lock.json

# ZIP oluştur
cd /wp-content/plugins/
zip -r easy-salon-booking.zip easy-salon-booking/ \
    -x "*.DS_Store" \
    -x "*__MACOSX*" \
    -x "*.git*"
```

### 5.2 Başvuru Formu
1. https://wordpress.org/plugins/developers/add/ adresine git
2. WordPress.org hesabınla giriş yap
3. ZIP dosyasını yükle
4. Plugin Guidelines'ı kabul et
5. "Upload" butonuna tıkla

### 5.3 İnceleme Süreci
- **Süre:** Genellikle 1-10 iş günü
- **Email:** Onay veya düzeltme talepleri email ile gelir
- **Revizyon:** Sorunlar varsa düzeltip tekrar gönder

---

## 6. SVN Kullanımı (Onay Sonrası)

Plugin onaylandıktan sonra SVN erişimi verilir.

### 6.1 İlk Kurulum
```bash
# SVN checkout
svn checkout https://plugins.svn.wordpress.org/easy-salon-booking/ easy-salon-booking-svn

# Klasör yapısı
cd easy-salon-booking-svn/
ls -la
# assets/    - Görseller (banner, icon, screenshots)
# branches/  - Eski sürümler
# tags/      - Sürüm etiketleri
# trunk/     - Ana geliştirme
```

### 6.2 İlk Yükleme
```bash
# Plugin dosyalarını trunk'a kopyala
cp -r /path/to/easy-salon-booking/* trunk/

# Assets'leri ekle
cp banner-772x250.png assets/
cp icon-128x128.png assets/
cp screenshot-1.png assets/

# SVN'e ekle
svn add trunk/*
svn add assets/*

# Commit et
svn commit -m "Initial release v1.0.0" --username your-wp-username
```

### 6.3 Sürüm Etiketleme
```bash
# Tag oluştur (her sürüm için zorunlu!)
svn copy trunk tags/1.0.0
svn commit -m "Tagging version 1.0.0"
```

---

## 7. Güncelleme Yayınlama

### 7.1 Yeni Sürüm Adımları

1. **Kod güncellemelerini yap**

2. **Versiyon numaralarını güncelle:**
   - `easy-salon-booking.php` → Version: 1.1.0
   - `readme.txt` → Stable tag: 1.1.0

3. **Changelog ekle:**
   ```txt
   = 1.1.0 =
   * Added: Google Calendar integration
   * Fixed: Time zone issue
   * Improved: Performance optimization
   ```

4. **SVN'e yükle:**
   ```bash
   # trunk'ı güncelle
   cp -r /path/to/easy-salon-booking/* trunk/
   svn commit -m "Update to version 1.1.0"

   # Yeni tag oluştur
   svn copy trunk tags/1.1.0
   svn commit -m "Tagging version 1.1.0"
   ```

---

## 8. Plugin Guidelines Özeti

### Yapılması Gerekenler
- [x] GPL v2+ uyumlu lisans
- [x] Tüm kodlar orijinal veya GPL uyumlu
- [x] Güvenli kod (SQL injection, XSS koruması)
- [x] Doğru nonce kullanımı
- [x] Yetki kontrolleri
- [x] Türkçe/İngilizce çeviri desteği

### Yapılmaması Gerekenler
- [ ] Harici sunuculara izinsiz bağlantı
- [ ] Tracking/analytics (izinsiz)
- [ ] Obfuscated (gizlenmiş) kod
- [ ] Backdoor veya güvenlik açıkları
- [ ] Başka plugin/tema'larla çakışma
- [ ] Spam veya reklam içerik
- [ ] "Premium" zorunluluğu olan temel özellikler

---

## 9. Yararlı Linkler

| Kaynak | URL |
|--------|-----|
| Plugin Handbook | https://developer.wordpress.org/plugins/ |
| Plugin Guidelines | https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/ |
| readme.txt Validator | https://wordpress.org/plugins/developers/readme-validator/ |
| Plugin Check (PHPCS) | https://wordpress.org/plugins/plugin-check/ |
| SVN Kullanımı | https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/ |
| Destek Forumları | https://wordpress.org/support/plugin/easy-salon-booking/ |

---

## 10. Checklist - Başvuru Öncesi

### Kod Kalitesi
- [ ] WordPress Coding Standards kontrolü (PHPCS)
- [ ] Plugin Check tool ile tarama
- [ ] Güvenlik taraması
- [ ] PHP 7.4+ uyumluluk testi
- [ ] WordPress 5.8+ uyumluluk testi

### Dosyalar
- [ ] `readme.txt` hazır ve doğrulanmış
- [ ] Lisans dosyası mevcut
- [ ] Tüm dosyalarda ABSPATH kontrolü
- [ ] Text domain tutarlı
- [ ] POT dosyası güncel

### Görseller
- [ ] Banner (772x250) hazır
- [ ] Icon (128x128) hazır
- [ ] En az 2 screenshot

### Test
- [ ] Temiz WordPress kurulumunda test
- [ ] Farklı temalarda test
- [ ] Mobil cihazlarda test
- [ ] Çoklu dil testi

---

## Destek

Sorularınız için:
- WordPress.org Plugin Developer Handbook
- Make WordPress Slack #pluginreview kanalı
- WordPress.org destek forumları

---

*Son güncelleme: Ocak 2025*
