<?php
/**
 * Admin Email Templates Page
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// E-posta şablonlarını al
$notification      = new AG_Notification();
$email_templates   = $notification->get_all_templates();

// Ayarlar
$settings = array(
	'ag_email_reminder_enabled' => get_option( 'ag_email_reminder_enabled', 'yes' ),
	'ag_email_reminder_hours'   => get_option( 'ag_email_reminder_hours', 24 ),
	'ag_email_logo_url'         => get_option( 'ag_email_logo_url', '' ),
	'ag_email_primary_color'    => get_option( 'ag_email_primary_color', '#3b82f6' ),
);

// Şablon tipleri ve ikonları
$template_icons = array(
	'booking_received'  => 'dashicons-email-alt',
	'booking_confirmed' => 'dashicons-yes-alt',
	'booking_cancelled' => 'dashicons-dismiss',
	'booking_reminder'  => 'dashicons-bell',
	'admin_new_booking' => 'dashicons-admin-users',
);

$template_colors = array(
	'booking_received'  => '#3b82f6',
	'booking_confirmed' => '#10b981',
	'booking_cancelled' => '#ef4444',
	'booking_reminder'  => '#f59e0b',
	'admin_new_booking' => '#8b5cf6',
);

$template_descriptions = array(
	'booking_received'  => __( 'Müşteri randevu talebi oluşturduğunda gönderilir.', 'appointment-general' ),
	'booking_confirmed' => __( 'Randevu admin tarafından onaylandığında gönderilir.', 'appointment-general' ),
	'booking_cancelled' => __( 'Randevu iptal edildiğinde gönderilir.', 'appointment-general' ),
	'booking_reminder'  => __( 'Randevudan önce hatırlatma olarak gönderilir.', 'appointment-general' ),
	'admin_new_booking' => __( 'Yeni randevu geldiğinde admin\'e gönderilir.', 'appointment-general' ),
);
?>

<div class="ag-email-templates-page">
	<!-- Header -->
	<div class="ag-settings-header">
		<div class="ag-settings-header-content">
			<div class="ag-settings-header-icon">
				<span class="dashicons dashicons-email-alt"></span>
			</div>
			<div class="ag-settings-header-text">
				<h1><?php esc_html_e( 'E-posta Şablonları', 'appointment-general' ); ?></h1>
				<p><?php esc_html_e( 'Müşterilere ve yöneticilere gönderilen e-postaları özelleştirin.', 'appointment-general' ); ?></p>
			</div>
		</div>
	</div>

	<div class="ag-email-templates-layout">
		<!-- Sol Panel: Şablon Listesi -->
		<div class="ag-email-sidebar">
			<div class="ag-card">
				<div class="ag-card-header">
					<h3><?php esc_html_e( 'Şablonlar', 'appointment-general' ); ?></h3>
				</div>
				<div class="ag-card-body" style="padding: 0;">
					<ul class="ag-template-list">
						<?php foreach ( $email_templates as $index => $template ) : ?>
						<li class="ag-template-item<?php echo 0 === $index ? ' active' : ''; ?>"
							data-template-id="<?php echo esc_attr( $template->id ); ?>"
							data-template-type="<?php echo esc_attr( $template->type ); ?>">
							<div class="ag-template-item-icon" style="background-color: <?php echo esc_attr( $template_colors[ $template->type ] ?? '#6b7280' ); ?>">
								<span class="dashicons <?php echo esc_attr( $template_icons[ $template->type ] ?? 'dashicons-email' ); ?>"></span>
							</div>
							<div class="ag-template-item-info">
								<span class="ag-template-item-name"><?php echo esc_html( $template->name ); ?></span>
								<span class="ag-template-item-status <?php echo $template->is_active ? 'active' : 'inactive'; ?>">
									<?php echo $template->is_active ? esc_html__( 'Aktif', 'appointment-general' ) : esc_html__( 'Pasif', 'appointment-general' ); ?>
								</span>
							</div>
						</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<!-- Genel Ayarlar -->
			<div class="ag-card" style="margin-top: 20px;">
				<div class="ag-card-header">
					<h3><?php esc_html_e( 'E-posta Ayarları', 'appointment-general' ); ?></h3>
				</div>
				<div class="ag-card-body">
					<div class="ag-form-group">
						<label for="ag_email_logo_url"><?php esc_html_e( 'Logo URL', 'appointment-general' ); ?></label>
						<input type="url" name="ag_email_logo_url" id="ag_email_logo_url" value="<?php echo esc_url( $settings['ag_email_logo_url'] ); ?>" placeholder="https://...">
						<small class="ag-help-text"><?php esc_html_e( 'Boş bırakılırsa işletme adı gösterilir', 'appointment-general' ); ?></small>
					</div>

					<div class="ag-form-group">
						<label for="ag_email_primary_color"><?php esc_html_e( 'Ana Renk', 'appointment-general' ); ?></label>
						<div style="display: flex; gap: 10px; align-items: center;">
							<input type="color" name="ag_email_primary_color" id="ag_email_primary_color" value="<?php echo esc_attr( $settings['ag_email_primary_color'] ); ?>" style="width: 50px; height: 38px;">
							<input type="text" id="ag_email_primary_color_text" value="<?php echo esc_attr( $settings['ag_email_primary_color'] ); ?>" style="width: 100px; font-family: monospace;">
						</div>
					</div>

					<hr style="margin: 15px 0;">

					<div class="ag-form-group">
						<label class="ag-checkbox-label">
							<input type="checkbox" name="ag_email_reminder_enabled" id="ag_email_reminder_enabled" value="yes" <?php checked( $settings['ag_email_reminder_enabled'], 'yes' ); ?>>
							<?php esc_html_e( 'Hatırlatma E-postası Gönder', 'appointment-general' ); ?>
						</label>
					</div>

					<div class="ag-form-group">
						<label for="ag_email_reminder_hours"><?php esc_html_e( 'Hatırlatma Zamanı', 'appointment-general' ); ?></label>
						<select name="ag_email_reminder_hours" id="ag_email_reminder_hours">
							<option value="1" <?php selected( $settings['ag_email_reminder_hours'], 1 ); ?>>1 <?php esc_html_e( 'saat önce', 'appointment-general' ); ?></option>
							<option value="2" <?php selected( $settings['ag_email_reminder_hours'], 2 ); ?>>2 <?php esc_html_e( 'saat önce', 'appointment-general' ); ?></option>
							<option value="6" <?php selected( $settings['ag_email_reminder_hours'], 6 ); ?>>6 <?php esc_html_e( 'saat önce', 'appointment-general' ); ?></option>
							<option value="12" <?php selected( $settings['ag_email_reminder_hours'], 12 ); ?>>12 <?php esc_html_e( 'saat önce', 'appointment-general' ); ?></option>
							<option value="24" <?php selected( $settings['ag_email_reminder_hours'], 24 ); ?>>1 <?php esc_html_e( 'gün önce', 'appointment-general' ); ?></option>
							<option value="48" <?php selected( $settings['ag_email_reminder_hours'], 48 ); ?>>2 <?php esc_html_e( 'gün önce', 'appointment-general' ); ?></option>
						</select>
					</div>

					<button type="button" class="ag-btn ag-btn-secondary ag-btn-block" id="ag-save-email-settings">
						<span class="dashicons dashicons-saved"></span>
						<?php esc_html_e( 'Ayarları Kaydet', 'appointment-general' ); ?>
					</button>
				</div>
			</div>
		</div>

		<!-- Sağ Panel: Şablon Düzenleyici -->
		<div class="ag-email-editor">
			<?php foreach ( $email_templates as $index => $template ) : ?>
			<div class="ag-template-editor<?php echo 0 === $index ? ' active' : ''; ?>" id="editor-<?php echo esc_attr( $template->id ); ?>">
				<div class="ag-card">
					<div class="ag-card-header" style="display: flex; justify-content: space-between; align-items: center;">
						<div>
							<h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
								<span class="dashicons <?php echo esc_attr( $template_icons[ $template->type ] ?? 'dashicons-email' ); ?>" style="color: <?php echo esc_attr( $template_colors[ $template->type ] ?? '#6b7280' ); ?>"></span>
								<?php echo esc_html( $template->name ); ?>
							</h3>
							<p class="ag-help-text" style="margin: 5px 0 0 0;"><?php echo esc_html( $template_descriptions[ $template->type ] ?? '' ); ?></p>
						</div>
						<div style="display: flex; gap: 10px; align-items: center;">
							<label class="ag-switch">
								<input type="checkbox" class="ag-template-active" data-template-id="<?php echo esc_attr( $template->id ); ?>" <?php checked( $template->is_active, 1 ); ?>>
								<span class="ag-switch-slider"></span>
							</label>
							<span class="ag-switch-label"><?php esc_html_e( 'Aktif', 'appointment-general' ); ?></span>
						</div>
					</div>
					<div class="ag-card-body">
						<!-- Konu -->
						<div class="ag-form-group">
							<label><?php esc_html_e( 'E-posta Konusu', 'appointment-general' ); ?></label>
							<input type="text" class="ag-template-subject" data-template-id="<?php echo esc_attr( $template->id ); ?>" value="<?php echo esc_attr( $template->subject ); ?>">
						</div>

						<!-- Editör Toolbar -->
						<div class="ag-form-group">
							<label><?php esc_html_e( 'E-posta İçeriği', 'appointment-general' ); ?></label>
							<div class="ag-editor-wrapper">
								<div class="ag-editor-toolbar">
									<div class="ag-toolbar-group">
										<button type="button" class="ag-toolbar-btn" data-command="bold" title="<?php esc_attr_e( 'Kalın', 'appointment-general' ); ?>">
											<strong>B</strong>
										</button>
										<button type="button" class="ag-toolbar-btn" data-command="italic" title="<?php esc_attr_e( 'İtalik', 'appointment-general' ); ?>">
											<em>I</em>
										</button>
										<button type="button" class="ag-toolbar-btn" data-command="link" title="<?php esc_attr_e( 'Link', 'appointment-general' ); ?>">
											<span class="dashicons dashicons-admin-links"></span>
										</button>
									</div>
									<div class="ag-toolbar-separator"></div>
									<div class="ag-toolbar-group">
										<button type="button" class="ag-toolbar-btn" data-command="h3" title="<?php esc_attr_e( 'Başlık', 'appointment-general' ); ?>">
											H3
										</button>
										<button type="button" class="ag-toolbar-btn" data-command="p" title="<?php esc_attr_e( 'Paragraf', 'appointment-general' ); ?>">
											P
										</button>
									</div>
									<div class="ag-toolbar-separator"></div>
									<div class="ag-toolbar-group">
										<button type="button" class="ag-toolbar-btn" data-command="table" title="<?php esc_attr_e( 'Detay Tablosu', 'appointment-general' ); ?>">
											<span class="dashicons dashicons-editor-table"></span>
										</button>
										<button type="button" class="ag-toolbar-btn" data-command="button" title="<?php esc_attr_e( 'Aksiyon Butonu', 'appointment-general' ); ?>">
											<span class="dashicons dashicons-button"></span>
										</button>
									</div>
									<div class="ag-toolbar-separator"></div>
									<div class="ag-toolbar-group">
										<select class="ag-insert-variable">
											<option value=""><?php esc_html_e( 'Değişken Ekle', 'appointment-general' ); ?></option>
											<optgroup label="<?php esc_attr_e( 'Müşteri', 'appointment-general' ); ?>">
												<option value="{customer_name}"><?php esc_html_e( 'Müşteri Adı', 'appointment-general' ); ?></option>
												<option value="{customer_email}"><?php esc_html_e( 'Müşteri E-posta', 'appointment-general' ); ?></option>
												<option value="{customer_phone}"><?php esc_html_e( 'Müşteri Telefon', 'appointment-general' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'Randevu', 'appointment-general' ); ?>">
												<option value="{services_list}"><?php esc_html_e( 'Hizmetler', 'appointment-general' ); ?></option>
												<option value="{staff_name}"><?php esc_html_e( 'Personel', 'appointment-general' ); ?></option>
												<option value="{booking_date}"><?php esc_html_e( 'Tarih', 'appointment-general' ); ?></option>
												<option value="{booking_time}"><?php esc_html_e( 'Saat', 'appointment-general' ); ?></option>
												<option value="{total_duration}"><?php esc_html_e( 'Süre', 'appointment-general' ); ?></option>
												<option value="{price}"><?php esc_html_e( 'Ücret', 'appointment-general' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'İşletme', 'appointment-general' ); ?>">
												<option value="{company_name}"><?php esc_html_e( 'İşletme Adı', 'appointment-general' ); ?></option>
												<option value="{company_phone}"><?php esc_html_e( 'İşletme Telefon', 'appointment-general' ); ?></option>
												<option value="{company_address}"><?php esc_html_e( 'İşletme Adres', 'appointment-general' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'Linkler', 'appointment-general' ); ?>">
												<option value="{manage_booking_url}"><?php esc_html_e( 'Randevu Yönetim Linki', 'appointment-general' ); ?></option>
												<option value="{calendar_links}"><?php esc_html_e( 'Takvim Linkleri', 'appointment-general' ); ?></option>
											</optgroup>
										</select>
									</div>
								</div>
								<textarea class="ag-template-content" data-template-id="<?php echo esc_attr( $template->id ); ?>" rows="15"><?php echo esc_textarea( $template->content ); ?></textarea>
							</div>
						</div>

						<!-- Aksiyon Butonları -->
						<div class="ag-editor-actions">
							<div class="ag-editor-actions-left">
								<button type="button" class="ag-btn ag-btn-primary ag-save-template" data-template-id="<?php echo esc_attr( $template->id ); ?>">
									<span class="dashicons dashicons-saved"></span>
									<?php esc_html_e( 'Şablonu Kaydet', 'appointment-general' ); ?>
								</button>
								<button type="button" class="ag-btn ag-btn-secondary ag-preview-template" data-template-type="<?php echo esc_attr( $template->type ); ?>">
									<span class="dashicons dashicons-visibility"></span>
									<?php esc_html_e( 'Önizleme', 'appointment-general' ); ?>
								</button>
							</div>
							<div class="ag-editor-actions-right">
								<input type="email" class="ag-test-email-input" placeholder="test@example.com">
								<button type="button" class="ag-btn ag-btn-outline ag-send-test-email" data-template-type="<?php echo esc_attr( $template->type ); ?>">
									<span class="dashicons dashicons-email"></span>
									<?php esc_html_e( 'Test Gönder', 'appointment-general' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Değişkenler Yardım Kartı -->
				<div class="ag-card ag-variables-help" style="margin-top: 20px;">
					<div class="ag-card-header">
						<h3><?php esc_html_e( 'Kullanılabilir Değişkenler', 'appointment-general' ); ?></h3>
					</div>
					<div class="ag-card-body">
						<div class="ag-variables-grid">
							<div class="ag-variable-group">
								<h4><?php esc_html_e( 'Müşteri Bilgileri', 'appointment-general' ); ?></h4>
								<code>{customer_name}</code>
								<code>{customer_email}</code>
								<code>{customer_phone}</code>
							</div>
							<div class="ag-variable-group">
								<h4><?php esc_html_e( 'Randevu Detayları', 'appointment-general' ); ?></h4>
								<code>{services_list}</code>
								<code>{staff_name}</code>
								<code>{booking_date}</code>
								<code>{booking_time}</code>
								<code>{total_duration}</code>
								<code>{price}</code>
							</div>
							<div class="ag-variable-group">
								<h4><?php esc_html_e( 'İşletme Bilgileri', 'appointment-general' ); ?></h4>
								<code>{company_name}</code>
								<code>{company_phone}</code>
								<code>{company_address}</code>
							</div>
							<div class="ag-variable-group">
								<h4><?php esc_html_e( 'Özel', 'appointment-general' ); ?></h4>
								<code>{manage_booking_url}</code>
								<code>{calendar_links}</code>
								<code>{admin_url}</code>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<!-- E-posta Önizleme Modal -->
<div class="ag-modal" id="ag-email-preview-modal" style="display: none;">
	<div class="ag-modal-overlay"></div>
	<div class="ag-modal-content ag-modal-lg" style="max-width: 750px;">
		<div class="ag-modal-header">
			<h3><?php esc_html_e( 'E-posta Önizleme', 'appointment-general' ); ?></h3>
			<button type="button" class="ag-modal-close">&times;</button>
		</div>
		<div class="ag-modal-body" style="padding: 0;">
			<div class="ag-preview-device-bar">
				<button type="button" class="ag-device-btn active" data-width="100%">
					<span class="dashicons dashicons-desktop"></span>
				</button>
				<button type="button" class="ag-device-btn" data-width="375px">
					<span class="dashicons dashicons-smartphone"></span>
				</button>
			</div>
			<div class="ag-preview-container">
				<iframe id="ag-email-preview-frame"></iframe>
			</div>
		</div>
	</div>
</div>
