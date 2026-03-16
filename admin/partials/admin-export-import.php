<?php
/**
 * Admin Export/Import Template
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="unbsb-admin-wrap unbsb-export-import">
	<div class="unbsb-admin-header">
		<div>
			<h1><?php esc_html_e( 'Export / Import', 'unbelievable-salon-booking' ); ?></h1>
			<p class="unbsb-subtitle"><?php esc_html_e( 'Export your data as JSON or import from a backup file.', 'unbelievable-salon-booking' ); ?></p>
		</div>
	</div>

	<div class="unbsb-export-import-grid">
		<!-- Export Section -->
		<div class="unbsb-card">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Export Data', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<p class="unbsb-export-desc">
					<?php esc_html_e( 'Download all your plugin data as a JSON file. This includes categories, services, staff, customers, bookings, promo codes, working hours, and settings.', 'unbelievable-salon-booking' ); ?>
				</p>

				<div id="unbsb-export-summary" class="unbsb-export-summary">
					<div class="unbsb-export-summary-loading">
						<span class="dashicons dashicons-update unbsb-spin"></span>
						<?php esc_html_e( 'Loading summary...', 'unbelievable-salon-booking' ); ?>
					</div>
				</div>

				<button type="button" id="unbsb-export-btn" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Export All Data', 'unbelievable-salon-booking' ); ?>
				</button>
			</div>
		</div>

		<!-- Import Section -->
		<div class="unbsb-card">
			<div class="unbsb-card-header">
				<h2>
					<span class="dashicons dashicons-upload"></span>
					<?php esc_html_e( 'Import Data', 'unbelievable-salon-booking' ); ?>
				</h2>
			</div>
			<div class="unbsb-card-body">
				<p class="unbsb-import-desc">
					<?php esc_html_e( 'Import data from a previously exported JSON file.', 'unbelievable-salon-booking' ); ?>
				</p>

				<!-- File Upload -->
				<div class="unbsb-form-group">
					<label class="unbsb-label"><?php esc_html_e( 'Select File', 'unbelievable-salon-booking' ); ?></label>
					<div id="unbsb-import-dropzone" class="unbsb-import-dropzone">
						<span class="dashicons dashicons-cloud-upload"></span>
						<p><?php esc_html_e( 'Drag & drop your JSON file here or click to browse', 'unbelievable-salon-booking' ); ?></p>
						<span class="unbsb-import-dropzone-hint"><?php esc_html_e( 'Only .json files are accepted', 'unbelievable-salon-booking' ); ?></span>
						<input type="file" id="unbsb-import-file" accept=".json" style="display: none;" />
					</div>
					<div id="unbsb-import-file-info" class="unbsb-import-file-info" style="display: none;">
						<span class="dashicons dashicons-media-code"></span>
						<span id="unbsb-import-file-name"></span>
						<span id="unbsb-import-file-size"></span>
						<button type="button" id="unbsb-import-file-remove" class="unbsb-btn-icon" title="<?php esc_attr_e( 'Remove file', 'unbelievable-salon-booking' ); ?>">
							<span class="dashicons dashicons-no-alt"></span>
						</button>
					</div>
				</div>

				<!-- Import Mode -->
				<div class="unbsb-form-group">
					<label class="unbsb-label"><?php esc_html_e( 'Import Mode', 'unbelievable-salon-booking' ); ?></label>
					<div class="unbsb-import-modes">
						<label class="unbsb-import-mode-option unbsb-import-mode-selected">
							<input type="radio" name="unbsb_import_mode" value="merge" checked />
							<div class="unbsb-import-mode-content">
								<span class="unbsb-import-mode-icon dashicons dashicons-plus-alt2"></span>
								<strong><?php esc_html_e( 'Merge', 'unbelievable-salon-booking' ); ?></strong>
								<span><?php esc_html_e( 'Add imported data to existing records. Existing data will not be deleted.', 'unbelievable-salon-booking' ); ?></span>
							</div>
						</label>
						<label class="unbsb-import-mode-option">
							<input type="radio" name="unbsb_import_mode" value="replace" />
							<div class="unbsb-import-mode-content">
								<span class="unbsb-import-mode-icon dashicons dashicons-update"></span>
								<strong><?php esc_html_e( 'Replace', 'unbelievable-salon-booking' ); ?></strong>
								<span><?php esc_html_e( 'Delete all existing data and replace with imported data.', 'unbelievable-salon-booking' ); ?></span>
							</div>
						</label>
					</div>
					<p class="unbsb-import-warning" id="unbsb-replace-warning" style="display: none;">
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Warning: Replace mode will permanently delete all existing data before importing. Make sure you have a backup!', 'unbelievable-salon-booking' ); ?>
					</p>
				</div>

				<!-- Import Button -->
				<button type="button" id="unbsb-import-btn" class="unbsb-btn unbsb-btn-primary unbsb-btn-lg" disabled>
					<span class="dashicons dashicons-upload"></span>
					<?php esc_html_e( 'Import Data', 'unbelievable-salon-booking' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Progress Overlay -->
	<div id="unbsb-import-progress-overlay" class="unbsb-import-progress-overlay" style="display: none;">
		<div class="unbsb-import-progress-card">
			<div class="unbsb-import-progress-header">
				<span class="dashicons dashicons-update unbsb-spin"></span>
				<h3 id="unbsb-import-progress-title"><?php esc_html_e( 'Importing data...', 'unbelievable-salon-booking' ); ?></h3>
			</div>
			<div class="unbsb-import-progress-bar-wrap">
				<div class="unbsb-import-progress-bar">
					<div id="unbsb-import-progress-fill" class="unbsb-import-progress-fill" style="width: 0%"></div>
				</div>
				<span id="unbsb-import-progress-percent" class="unbsb-import-progress-percent">0%</span>
			</div>
			<p id="unbsb-import-progress-status" class="unbsb-import-progress-status"><?php esc_html_e( 'Preparing import...', 'unbelievable-salon-booking' ); ?></p>
		</div>
	</div>

	<!-- Result Modal -->
	<div id="unbsb-import-result-overlay" class="unbsb-import-progress-overlay" style="display: none;">
		<div class="unbsb-import-progress-card unbsb-import-result-card">
			<div id="unbsb-import-result-icon" class="unbsb-import-result-icon unbsb-import-result-success">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<h3 id="unbsb-import-result-title"></h3>
			<p id="unbsb-import-result-message"></p>
			<div id="unbsb-import-result-details" class="unbsb-import-result-details"></div>
			<button type="button" id="unbsb-import-result-close" class="unbsb-btn unbsb-btn-primary">
				<?php esc_html_e( 'Close', 'unbelievable-salon-booking' ); ?>
			</button>
		</div>
	</div>
</div>
