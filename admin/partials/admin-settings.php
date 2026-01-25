<?php
/**
 * Admin Settings Template - Modern Tab Design
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Mevcut ayarları al
$settings = array(
	'ag_time_slot_interval'        => get_option( 'ag_time_slot_interval', 30 ),
	'ag_booking_lead_time'         => get_option( 'ag_booking_lead_time', 60 ),
	'ag_booking_future_days'       => get_option( 'ag_booking_future_days', 30 ),
	'ag_booking_flow_mode'         => get_option( 'ag_booking_flow_mode', 'service_first' ),
	'ag_currency'                  => get_option( 'ag_currency', 'TRY' ),
	'ag_currency_symbol'           => get_option( 'ag_currency_symbol', '₺' ),
	'ag_currency_position'         => get_option( 'ag_currency_position', 'after' ),
	'ag_date_format'               => get_option( 'ag_date_format', 'd.m.Y' ),
	'ag_time_format'               => get_option( 'ag_time_format', 'H:i' ),
	'ag_admin_email'               => get_option( 'ag_admin_email', get_option( 'admin_email' ) ),
	'ag_email_from_name'           => get_option( 'ag_email_from_name', get_bloginfo( 'name' ) ),
	'ag_email_from_address'        => get_option( 'ag_email_from_address', get_option( 'admin_email' ) ),
	'ag_company_name'              => get_option( 'ag_company_name', get_bloginfo( 'name' ) ),
	'ag_company_phone'             => get_option( 'ag_company_phone', '' ),
	'ag_company_address'           => get_option( 'ag_company_address', '' ),
	'ag_enable_ics'                => get_option( 'ag_enable_ics', 'yes' ),
	'ag_allow_cancel'              => get_option( 'ag_allow_cancel', 'yes' ),
	'ag_allow_reschedule'          => get_option( 'ag_allow_reschedule', 'yes' ),
	'ag_cancel_deadline_hours'     => get_option( 'ag_cancel_deadline_hours', 24 ),
	'ag_reschedule_deadline_hours' => get_option( 'ag_reschedule_deadline_hours', 24 ),
	'ag_max_reschedules'           => get_option( 'ag_max_reschedules', 2 ),
	'ag_enable_multi_service'      => get_option( 'ag_enable_multi_service', 'no' ),
	'ag_sms_enabled'               => get_option( 'ag_sms_enabled', 'no' ),
	'ag_sms_provider'              => get_option( 'ag_sms_provider', 'netgsm' ),
	'ag_sms_netgsm_username'       => get_option( 'ag_sms_netgsm_username', '' ),
	'ag_sms_netgsm_password'       => get_option( 'ag_sms_netgsm_password', '' ),
	'ag_sms_netgsm_sender'         => get_option( 'ag_sms_netgsm_sender', '' ),
	'ag_sms_reminder_enabled'      => get_option( 'ag_sms_reminder_enabled', 'yes' ),
	'ag_sms_reminder_hours'        => get_option( 'ag_sms_reminder_hours', 24 ),
	'ag_sms_on_booking'            => get_option( 'ag_sms_on_booking', 'yes' ),
	'ag_sms_on_confirmation'       => get_option( 'ag_sms_on_confirmation', 'yes' ),
	'ag_sms_on_cancellation'       => get_option( 'ag_sms_on_cancellation', 'no' ),
);

// SMS şablonlarını al
$sms_templates = array();
if ( class_exists( 'AG_SMS_Manager' ) ) {
	$sms_manager   = new AG_SMS_Manager();
	$sms_templates = $sms_manager->get_templates();
}

// Tab tanımları
$tabs = array(
	'general'  => array(
		'label' => __( 'Genel', 'appointment-general' ),
		'icon'  => 'dashicons-admin-generic',
	),
	'business' => array(
		'label' => __( 'İşletme', 'appointment-general' ),
		'icon'  => 'dashicons-building',
	),
	'format'   => array(
		'label' => __( 'Para & Tarih', 'appointment-general' ),
		'icon'  => 'dashicons-calendar-alt',
	),
	'email'    => array(
		'label' => __( 'E-posta', 'appointment-general' ),
		'icon'  => 'dashicons-email',
	),
	'booking'  => array(
		'label' => __( 'Randevu', 'appointment-general' ),
		'icon'  => 'dashicons-clock',
	),
	'sms'      => array(
		'label' => __( 'SMS', 'appointment-general' ),
		'icon'  => 'dashicons-smartphone',
	),
);

$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
if ( ! array_key_exists( $current_tab, $tabs ) ) {
	$current_tab = 'general';
}
?>

<div class="ag-settings-page">
	<!-- Header -->
	<div class="ag-settings-header">
		<div class="ag-settings-header-content">
			<div class="ag-settings-header-icon">
				<span class="dashicons dashicons-admin-settings"></span>
			</div>
			<div class="ag-settings-header-text">
				<h1><?php esc_html_e( 'Ayarlar', 'appointment-general' ); ?></h1>
				<p><?php esc_html_e( 'Randevu sisteminizi özelleştirin ve yapılandırın.', 'appointment-general' ); ?></p>
			</div>
		</div>
	</div>

	<div class="ag-settings-container">
		<!-- Tab Navigation -->
		<div class="ag-settings-nav">
			<ul class="ag-settings-tabs">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
				<li class="ag-settings-tab <?php echo $current_tab === $tab_id ? 'active' : ''; ?>">
					<a href="<?php echo esc_url( add_query_arg( 'tab', $tab_id ) ); ?>">
						<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
						<span class="tab-label"><?php echo esc_html( $tab['label'] ); ?></span>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<!-- Tab Content -->
		<div class="ag-settings-content">
			<form id="ag-settings-form">
				<?php if ( 'general' === $current_tab ) : ?>
				<!-- GENEL AYARLAR -->
				<div class="ag-settings-section">
					<div class="ag-section-header">
						<h2><?php esc_html_e( 'Randevu Ayarları', 'appointment-general' ); ?></h2>
						<p><?php esc_html_e( 'Randevu sisteminin temel çalışma parametrelerini belirleyin.', 'appointment-general' ); ?></p>
					</div>

					<div class="ag-settings-grid">
						<div class="ag-setting-item">
							<label for="ag_time_slot_interval">
								<span class="setting-label"><?php esc_html_e( 'Saat Aralığı', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Randevu slotları arasındaki süre', 'appointment-general' ); ?></span>
							</label>
							<div class="ag-input-group">
								<select name="ag_time_slot_interval" id="ag_time_slot_interval" class="ag-select">
									<option value="15" <?php selected( $settings['ag_time_slot_interval'], 15 ); ?>>15 dakika</option>
									<option value="30" <?php selected( $settings['ag_time_slot_interval'], 30 ); ?>>30 dakika</option>
									<option value="60" <?php selected( $settings['ag_time_slot_interval'], 60 ); ?>>60 dakika</option>
								</select>
							</div>
						</div>

						<div class="ag-setting-item">
							<label for="ag_booking_lead_time">
								<span class="setting-label"><?php esc_html_e( 'Minimum Önceden Randevu', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'En az kaç dakika önce randevu alınabilir', 'appointment-general' ); ?></span>
							</label>
							<div class="ag-input-group">
								<input type="number" name="ag_booking_lead_time" id="ag_booking_lead_time" value="<?php echo esc_attr( $settings['ag_booking_lead_time'] ); ?>" min="0" step="30" class="ag-input">
								<span class="ag-input-suffix"><?php esc_html_e( 'dakika', 'appointment-general' ); ?></span>
							</div>
						</div>

						<div class="ag-setting-item">
							<label for="ag_booking_future_days">
								<span class="setting-label"><?php esc_html_e( 'İleri Randevu Süresi', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Kaç gün ilerisi için randevu alınabilir', 'appointment-general' ); ?></span>
							</label>
							<div class="ag-input-group">
								<input type="number" name="ag_booking_future_days" id="ag_booking_future_days" value="<?php echo esc_attr( $settings['ag_booking_future_days'] ); ?>" min="1" max="365" class="ag-input">
								<span class="ag-input-suffix"><?php esc_html_e( 'gün', 'appointment-general' ); ?></span>
							</div>
						</div>

						<div class="ag-setting-item">
							<label for="ag_booking_flow_mode">
								<span class="setting-label"><?php esc_html_e( 'Randevu Akışı', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Müşterinin izleyeceği adım sırası', 'appointment-general' ); ?></span>
							</label>
							<select name="ag_booking_flow_mode" id="ag_booking_flow_mode" class="ag-select">
								<option value="service_first" <?php selected( $settings['ag_booking_flow_mode'], 'service_first' ); ?>><?php esc_html_e( 'Önce Hizmet Seç', 'appointment-general' ); ?></option>
								<option value="staff_first" <?php selected( $settings['ag_booking_flow_mode'], 'staff_first' ); ?>><?php esc_html_e( 'Önce Personel Seç', 'appointment-general' ); ?></option>
								<option value="service_only" <?php selected( $settings['ag_booking_flow_mode'], 'service_only' ); ?>><?php esc_html_e( 'Sadece Hizmet (Personel Otomatik)', 'appointment-general' ); ?></option>
								<option value="staff_only" <?php selected( $settings['ag_booking_flow_mode'], 'staff_only' ); ?>><?php esc_html_e( 'Sadece Personel (Tüm Hizmetler)', 'appointment-general' ); ?></option>
							</select>
						</div>
					</div>

					<div class="ag-setting-item ag-setting-toggle">
						<div class="ag-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Çoklu Hizmet Seçimi', 'appointment-general' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Müşteriler tek randevuda birden fazla hizmet seçebilir', 'appointment-general' ); ?></span>
						</div>
						<label class="ag-toggle">
							<input type="checkbox" name="ag_enable_multi_service" id="ag_enable_multi_service" value="yes" <?php checked( $settings['ag_enable_multi_service'], 'yes' ); ?>>
							<span class="ag-toggle-slider"></span>
						</label>
					</div>
				</div>

				<?php elseif ( 'business' === $current_tab ) : ?>
				<!-- İŞLETME BİLGİLERİ -->
				<div class="ag-settings-section">
					<div class="ag-section-header">
						<h2><?php esc_html_e( 'İşletme Bilgileri', 'appointment-general' ); ?></h2>
						<p><?php esc_html_e( 'E-posta ve SMS bildirimlerinde görünecek işletme bilgilerini girin.', 'appointment-general' ); ?></p>
					</div>

					<div class="ag-settings-form-single">
						<div class="ag-setting-item">
							<label for="ag_company_name">
								<span class="setting-label"><?php esc_html_e( 'İşletme Adı', 'appointment-general' ); ?></span>
							</label>
							<input type="text" name="ag_company_name" id="ag_company_name" value="<?php echo esc_attr( $settings['ag_company_name'] ); ?>" class="ag-input ag-input-lg" placeholder="<?php esc_attr_e( 'Örn: Elit Kuaför Salonu', 'appointment-general' ); ?>">
						</div>

						<div class="ag-setting-item">
							<label for="ag_company_phone">
								<span class="setting-label"><?php esc_html_e( 'İşletme Telefonu', 'appointment-general' ); ?></span>
							</label>
							<input type="tel" name="ag_company_phone" id="ag_company_phone" value="<?php echo esc_attr( $settings['ag_company_phone'] ); ?>" class="ag-input ag-input-lg" placeholder="<?php esc_attr_e( '0212 555 55 55', 'appointment-general' ); ?>">
						</div>

						<div class="ag-setting-item">
							<label for="ag_company_address">
								<span class="setting-label"><?php esc_html_e( 'İşletme Adresi', 'appointment-general' ); ?></span>
							</label>
							<textarea name="ag_company_address" id="ag_company_address" rows="3" class="ag-textarea" placeholder="<?php esc_attr_e( 'Tam adres bilgisi...', 'appointment-general' ); ?>"><?php echo esc_textarea( $settings['ag_company_address'] ); ?></textarea>
						</div>
					</div>
				</div>

				<?php elseif ( 'format' === $current_tab ) : ?>
				<!-- PARA BİRİMİ & TARİH/SAAT -->
				<div class="ag-settings-section">
					<div class="ag-section-header">
						<h2><?php esc_html_e( 'Para Birimi', 'appointment-general' ); ?></h2>
						<p><?php esc_html_e( 'Fiyatların nasıl görüntüleneceğini ayarlayın.', 'appointment-general' ); ?></p>
					</div>

					<div class="ag-settings-grid ag-grid-3">
						<div class="ag-setting-item">
							<label for="ag_currency">
								<span class="setting-label"><?php esc_html_e( 'Para Birimi Kodu', 'appointment-general' ); ?></span>
							</label>
							<input type="text" name="ag_currency" id="ag_currency" value="<?php echo esc_attr( $settings['ag_currency'] ); ?>" maxlength="3" class="ag-input" placeholder="TRY">
						</div>

						<div class="ag-setting-item">
							<label for="ag_currency_symbol">
								<span class="setting-label"><?php esc_html_e( 'Para Birimi Sembolü', 'appointment-general' ); ?></span>
							</label>
							<input type="text" name="ag_currency_symbol" id="ag_currency_symbol" value="<?php echo esc_attr( $settings['ag_currency_symbol'] ); ?>" maxlength="5" class="ag-input" placeholder="₺">
						</div>

						<div class="ag-setting-item">
							<label for="ag_currency_position">
								<span class="setting-label"><?php esc_html_e( 'Sembol Konumu', 'appointment-general' ); ?></span>
							</label>
							<select name="ag_currency_position" id="ag_currency_position" class="ag-select">
								<option value="before" <?php selected( $settings['ag_currency_position'], 'before' ); ?>>Önce (₺100)</option>
								<option value="after" <?php selected( $settings['ag_currency_position'], 'after' ); ?>>Sonra (100₺)</option>
							</select>
						</div>
					</div>
				</div>

				<div class="ag-settings-section">
					<div class="ag-section-header">
						<h2><?php esc_html_e( 'Tarih ve Saat Formatı', 'appointment-general' ); ?></h2>
						<p><?php esc_html_e( 'Tarih ve saatlerin nasıl görüntüleneceğini seçin.', 'appointment-general' ); ?></p>
					</div>

					<div class="ag-settings-grid">
						<div class="ag-setting-item">
							<label for="ag_date_format">
								<span class="setting-label"><?php esc_html_e( 'Tarih Formatı', 'appointment-general' ); ?></span>
							</label>
							<select name="ag_date_format" id="ag_date_format" class="ag-select">
								<option value="d.m.Y" <?php selected( $settings['ag_date_format'], 'd.m.Y' ); ?>>31.12.2024</option>
								<option value="d/m/Y" <?php selected( $settings['ag_date_format'], 'd/m/Y' ); ?>>31/12/2024</option>
								<option value="Y-m-d" <?php selected( $settings['ag_date_format'], 'Y-m-d' ); ?>>2024-12-31</option>
								<option value="d F Y" <?php selected( $settings['ag_date_format'], 'd F Y' ); ?>>31 Aralık 2024</option>
							</select>
						</div>

						<div class="ag-setting-item">
							<label for="ag_time_format">
								<span class="setting-label"><?php esc_html_e( 'Saat Formatı', 'appointment-general' ); ?></span>
							</label>
							<select name="ag_time_format" id="ag_time_format" class="ag-select">
								<option value="H:i" <?php selected( $settings['ag_time_format'], 'H:i' ); ?>>14:30 (24 saat)</option>
								<option value="g:i A" <?php selected( $settings['ag_time_format'], 'g:i A' ); ?>>2:30 PM (12 saat)</option>
							</select>
						</div>
					</div>
				</div>

				<?php elseif ( 'email' === $current_tab ) : ?>
				<!-- E-POSTA AYARLARI -->
				<div class="ag-settings-section">
					<div class="ag-section-header">
						<h2><?php esc_html_e( 'E-posta Ayarları', 'appointment-general' ); ?></h2>
						<p><?php esc_html_e( 'Randevu bildirim e-postalarının gönderim ayarlarını yapılandırın.', 'appointment-general' ); ?></p>
					</div>

					<div class="ag-settings-form-single">
						<div class="ag-setting-item">
							<label for="ag_admin_email">
								<span class="setting-label"><?php esc_html_e( 'Bildirim E-postası', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Yeni randevu bildirimleri bu adrese gönderilir', 'appointment-general' ); ?></span>
							</label>
							<input type="email" name="ag_admin_email" id="ag_admin_email" value="<?php echo esc_attr( $settings['ag_admin_email'] ); ?>" class="ag-input ag-input-lg">
						</div>

						<div class="ag-setting-item">
							<label for="ag_email_from_name">
								<span class="setting-label"><?php esc_html_e( 'Gönderen Adı', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'E-postalarda görünecek gönderen adı', 'appointment-general' ); ?></span>
							</label>
							<input type="text" name="ag_email_from_name" id="ag_email_from_name" value="<?php echo esc_attr( $settings['ag_email_from_name'] ); ?>" class="ag-input ag-input-lg">
						</div>

						<div class="ag-setting-item">
							<label for="ag_email_from_address">
								<span class="setting-label"><?php esc_html_e( 'Gönderen E-posta', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'E-postaların gönderileceği adres', 'appointment-general' ); ?></span>
							</label>
							<input type="email" name="ag_email_from_address" id="ag_email_from_address" value="<?php echo esc_attr( $settings['ag_email_from_address'] ); ?>" class="ag-input ag-input-lg">
						</div>
					</div>

					<div class="ag-info-box">
						<span class="dashicons dashicons-info"></span>
						<p><?php esc_html_e( 'E-posta şablonlarını düzenlemek için sol menüden "E-posta Şablonları" sayfasına gidin.', 'appointment-general' ); ?></p>
					</div>
				</div>

				<?php elseif ( 'booking' === $current_tab ) : ?>
				<!-- RANDEVU AYARLARI -->
				<div class="ag-settings-section">
					<div class="ag-section-header">
						<h2><?php esc_html_e( 'İptal ve Değiştirme', 'appointment-general' ); ?></h2>
						<p><?php esc_html_e( 'Müşterilerin randevu iptal etme ve değiştirme kurallarını belirleyin.', 'appointment-general' ); ?></p>
					</div>

					<div class="ag-setting-item ag-setting-toggle">
						<div class="ag-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Randevu İptali', 'appointment-general' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Müşteriler randevularını iptal edebilir', 'appointment-general' ); ?></span>
						</div>
						<label class="ag-toggle">
							<input type="checkbox" name="ag_allow_cancel" id="ag_allow_cancel" value="yes" <?php checked( $settings['ag_allow_cancel'], 'yes' ); ?>>
							<span class="ag-toggle-slider"></span>
						</label>
					</div>

					<div class="ag-setting-item ag-setting-toggle">
						<div class="ag-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Randevu Değiştirme', 'appointment-general' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Müşteriler randevularını yeniden planlayabilir', 'appointment-general' ); ?></span>
						</div>
						<label class="ag-toggle">
							<input type="checkbox" name="ag_allow_reschedule" id="ag_allow_reschedule" value="yes" <?php checked( $settings['ag_allow_reschedule'], 'yes' ); ?>>
							<span class="ag-toggle-slider"></span>
						</label>
					</div>

					<div class="ag-settings-grid ag-grid-3">
						<div class="ag-setting-item">
							<label for="ag_cancel_deadline_hours">
								<span class="setting-label"><?php esc_html_e( 'İptal Son Saati', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Randevudan kaç saat önce', 'appointment-general' ); ?></span>
							</label>
							<div class="ag-input-group">
								<input type="number" name="ag_cancel_deadline_hours" id="ag_cancel_deadline_hours" value="<?php echo esc_attr( $settings['ag_cancel_deadline_hours'] ); ?>" min="0" max="168" class="ag-input">
								<span class="ag-input-suffix"><?php esc_html_e( 'saat', 'appointment-general' ); ?></span>
							</div>
						</div>

						<div class="ag-setting-item">
							<label for="ag_reschedule_deadline_hours">
								<span class="setting-label"><?php esc_html_e( 'Değiştirme Son Saati', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Randevudan kaç saat önce', 'appointment-general' ); ?></span>
							</label>
							<div class="ag-input-group">
								<input type="number" name="ag_reschedule_deadline_hours" id="ag_reschedule_deadline_hours" value="<?php echo esc_attr( $settings['ag_reschedule_deadline_hours'] ); ?>" min="0" max="168" class="ag-input">
								<span class="ag-input-suffix"><?php esc_html_e( 'saat', 'appointment-general' ); ?></span>
							</div>
						</div>

						<div class="ag-setting-item">
							<label for="ag_max_reschedules">
								<span class="setting-label"><?php esc_html_e( 'Maks. Değiştirme', 'appointment-general' ); ?></span>
								<span class="setting-hint"><?php esc_html_e( 'Bir randevu kaç kez değiştirilebilir', 'appointment-general' ); ?></span>
							</label>
							<div class="ag-input-group">
								<input type="number" name="ag_max_reschedules" id="ag_max_reschedules" value="<?php echo esc_attr( $settings['ag_max_reschedules'] ); ?>" min="1" max="10" class="ag-input">
								<span class="ag-input-suffix"><?php esc_html_e( 'kez', 'appointment-general' ); ?></span>
							</div>
						</div>
					</div>
				</div>

				<div class="ag-settings-section">
					<div class="ag-section-header">
						<h2><?php esc_html_e( 'Takvim Entegrasyonu', 'appointment-general' ); ?></h2>
						<p><?php esc_html_e( 'Randevuları takvim uygulamalarına ekleme özelliğini yapılandırın.', 'appointment-general' ); ?></p>
					</div>

					<div class="ag-setting-item ag-setting-toggle">
						<div class="ag-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'Takvim Dosyası (ICS)', 'appointment-general' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'E-postalara .ics dosyası ve takvim linkleri eklenir', 'appointment-general' ); ?></span>
						</div>
						<label class="ag-toggle">
							<input type="checkbox" name="ag_enable_ics" id="ag_enable_ics" value="yes" <?php checked( $settings['ag_enable_ics'], 'yes' ); ?>>
							<span class="ag-toggle-slider"></span>
						</label>
					</div>

					<div class="ag-calendar-providers">
						<div class="ag-provider-item">
							<span class="dashicons dashicons-google"></span>
							<span>Google Calendar</span>
						</div>
						<div class="ag-provider-item">
							<span class="dashicons dashicons-email-alt"></span>
							<span>Outlook</span>
						</div>
						<div class="ag-provider-item">
							<span class="dashicons dashicons-calendar"></span>
							<span>Apple Calendar</span>
						</div>
						<div class="ag-provider-item">
							<span class="dashicons dashicons-calendar-alt"></span>
							<span>Yahoo Calendar</span>
						</div>
					</div>
				</div>

				<?php elseif ( 'sms' === $current_tab ) : ?>
				<!-- SMS AYARLARI -->
				<div class="ag-settings-section">
					<div class="ag-section-header">
						<h2><?php esc_html_e( 'SMS Bildirimleri', 'appointment-general' ); ?></h2>
						<p><?php esc_html_e( 'NetGSM ile SMS bildirim ayarlarını yapılandırın.', 'appointment-general' ); ?></p>
					</div>

					<div class="ag-setting-item ag-setting-toggle ag-toggle-main">
						<div class="ag-toggle-content">
							<span class="setting-label"><?php esc_html_e( 'SMS Bildirimlerini Etkinleştir', 'appointment-general' ); ?></span>
							<span class="setting-hint"><?php esc_html_e( 'Randevu bildirimleri SMS olarak gönderilir', 'appointment-general' ); ?></span>
						</div>
						<label class="ag-toggle">
							<input type="checkbox" name="ag_sms_enabled" id="ag_sms_enabled" value="yes" <?php checked( $settings['ag_sms_enabled'], 'yes' ); ?>>
							<span class="ag-toggle-slider"></span>
						</label>
					</div>

					<div id="ag-sms-settings" class="ag-sms-settings-inner" style="<?php echo 'yes' !== $settings['ag_sms_enabled'] ? 'display:none;' : ''; ?>">
						<div class="ag-settings-subsection">
							<h3><?php esc_html_e( 'NetGSM API Bilgileri', 'appointment-general' ); ?></h3>

							<div class="ag-settings-grid ag-grid-3">
								<div class="ag-setting-item">
									<label for="ag_sms_netgsm_username">
										<span class="setting-label"><?php esc_html_e( 'Kullanıcı Adı', 'appointment-general' ); ?></span>
									</label>
									<input type="text" name="ag_sms_netgsm_username" id="ag_sms_netgsm_username" value="<?php echo esc_attr( $settings['ag_sms_netgsm_username'] ); ?>" class="ag-input">
								</div>

								<div class="ag-setting-item">
									<label for="ag_sms_netgsm_password">
										<span class="setting-label"><?php esc_html_e( 'Şifre', 'appointment-general' ); ?></span>
									</label>
									<input type="password" name="ag_sms_netgsm_password" id="ag_sms_netgsm_password" value="<?php echo esc_attr( $settings['ag_sms_netgsm_password'] ); ?>" class="ag-input">
								</div>

								<div class="ag-setting-item">
									<label for="ag_sms_netgsm_sender">
										<span class="setting-label"><?php esc_html_e( 'Gönderen ID', 'appointment-general' ); ?></span>
									</label>
									<input type="text" name="ag_sms_netgsm_sender" id="ag_sms_netgsm_sender" value="<?php echo esc_attr( $settings['ag_sms_netgsm_sender'] ); ?>" class="ag-input">
								</div>
							</div>

							<div class="ag-sms-actions">
								<button type="button" class="ag-btn ag-btn-outline" id="ag-sms-check-balance">
									<span class="dashicons dashicons-chart-area"></span>
									<?php esc_html_e( 'Bakiye Sorgula', 'appointment-general' ); ?>
								</button>
								<span id="ag-sms-balance-result" class="ag-balance-result"></span>

								<div class="ag-sms-test">
									<input type="tel" id="ag_sms_test_phone" placeholder="5xxxxxxxxx" class="ag-input">
									<button type="button" class="ag-btn ag-btn-outline" id="ag-sms-send-test">
										<span class="dashicons dashicons-smartphone"></span>
										<?php esc_html_e( 'Test SMS', 'appointment-general' ); ?>
									</button>
								</div>
							</div>
						</div>

						<div class="ag-settings-subsection">
							<h3><?php esc_html_e( 'Bildirim Tetikleyicileri', 'appointment-general' ); ?></h3>

							<div class="ag-toggle-list">
								<div class="ag-setting-item ag-setting-toggle">
									<div class="ag-toggle-content">
										<span class="setting-label"><?php esc_html_e( 'Yeni Randevu', 'appointment-general' ); ?></span>
									</div>
									<label class="ag-toggle ag-toggle-sm">
										<input type="checkbox" name="ag_sms_on_booking" value="yes" <?php checked( $settings['ag_sms_on_booking'], 'yes' ); ?>>
										<span class="ag-toggle-slider"></span>
									</label>
								</div>

								<div class="ag-setting-item ag-setting-toggle">
									<div class="ag-toggle-content">
										<span class="setting-label"><?php esc_html_e( 'Randevu Onayı', 'appointment-general' ); ?></span>
									</div>
									<label class="ag-toggle ag-toggle-sm">
										<input type="checkbox" name="ag_sms_on_confirmation" value="yes" <?php checked( $settings['ag_sms_on_confirmation'], 'yes' ); ?>>
										<span class="ag-toggle-slider"></span>
									</label>
								</div>

								<div class="ag-setting-item ag-setting-toggle">
									<div class="ag-toggle-content">
										<span class="setting-label"><?php esc_html_e( 'Randevu İptali', 'appointment-general' ); ?></span>
									</div>
									<label class="ag-toggle ag-toggle-sm">
										<input type="checkbox" name="ag_sms_on_cancellation" value="yes" <?php checked( $settings['ag_sms_on_cancellation'], 'yes' ); ?>>
										<span class="ag-toggle-slider"></span>
									</label>
								</div>

								<div class="ag-setting-item ag-setting-toggle">
									<div class="ag-toggle-content">
										<span class="setting-label"><?php esc_html_e( 'Hatırlatma', 'appointment-general' ); ?></span>
									</div>
									<label class="ag-toggle ag-toggle-sm">
										<input type="checkbox" name="ag_sms_reminder_enabled" value="yes" <?php checked( $settings['ag_sms_reminder_enabled'], 'yes' ); ?>>
										<span class="ag-toggle-slider"></span>
									</label>
								</div>
							</div>

							<div class="ag-setting-item" style="margin-top: 20px;">
								<label for="ag_sms_reminder_hours">
									<span class="setting-label"><?php esc_html_e( 'Hatırlatma Zamanı', 'appointment-general' ); ?></span>
								</label>
								<select name="ag_sms_reminder_hours" id="ag_sms_reminder_hours" class="ag-select">
									<option value="1" <?php selected( $settings['ag_sms_reminder_hours'], 1 ); ?>>1 saat önce</option>
									<option value="2" <?php selected( $settings['ag_sms_reminder_hours'], 2 ); ?>>2 saat önce</option>
									<option value="3" <?php selected( $settings['ag_sms_reminder_hours'], 3 ); ?>>3 saat önce</option>
									<option value="6" <?php selected( $settings['ag_sms_reminder_hours'], 6 ); ?>>6 saat önce</option>
									<option value="12" <?php selected( $settings['ag_sms_reminder_hours'], 12 ); ?>>12 saat önce</option>
									<option value="24" <?php selected( $settings['ag_sms_reminder_hours'], 24 ); ?>>1 gün önce</option>
									<option value="48" <?php selected( $settings['ag_sms_reminder_hours'], 48 ); ?>>2 gün önce</option>
								</select>
							</div>
						</div>

						<?php if ( ! empty( $sms_templates ) ) : ?>
						<div class="ag-settings-subsection">
							<h3><?php esc_html_e( 'SMS Şablonları', 'appointment-general' ); ?></h3>
							<p class="ag-subsection-desc">
								<?php esc_html_e( 'Değişkenler:', 'appointment-general' ); ?>
								<code>{customer_name}</code> <code>{service_name}</code> <code>{staff_name}</code> <code>{booking_date}</code> <code>{booking_time}</code> <code>{price}</code>
							</p>

							<div class="ag-sms-templates">
								<?php foreach ( $sms_templates as $template ) : ?>
								<div class="ag-sms-template-item">
									<div class="ag-template-header">
										<span class="ag-template-name"><?php echo esc_html( $template->name ); ?></span>
										<label class="ag-toggle ag-toggle-sm">
											<input type="checkbox" name="sms_template_active_<?php echo esc_attr( $template->id ); ?>" value="1" <?php checked( $template->is_active, 1 ); ?>>
											<span class="ag-toggle-slider"></span>
										</label>
									</div>
									<textarea name="sms_template_<?php echo esc_attr( $template->id ); ?>" rows="2" class="ag-textarea"><?php echo esc_textarea( $template->message ); ?></textarea>
								</div>
								<?php endforeach; ?>
							</div>

							<button type="button" class="ag-btn ag-btn-outline" id="ag-save-sms-templates">
								<span class="dashicons dashicons-saved"></span>
								<?php esc_html_e( 'Şablonları Kaydet', 'appointment-general' ); ?>
							</button>
						</div>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>

				<!-- Save Button -->
				<div class="ag-settings-footer">
					<button type="submit" class="ag-btn ag-btn-primary ag-btn-lg" id="ag-save-settings">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Değişiklikleri Kaydet', 'appointment-general' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
