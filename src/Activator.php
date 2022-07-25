<?php

namespace WAWP;

require_once __DIR__ . '/Addon.php';
require_once __DIR__ . '/WAIntegration.php';
require_once __DIR__ . '/Log.php';
require_once __DIR__ . '/WAWPException.php';

// Log::wap_log_debug('not in function');

class Activator {

	const SHOW_NOTICE_ACTIVATION = 'show_notice_activation';
	const LICENSE_CHECK_OPTION = 'license-check-' . CORE_SLUG;


	/**
	 * Constructor for Activator class
	 */
	public function __construct($filename) {
		register_activation_hook($filename, array($this, 'activate_plugin_callback'));

		add_action('admin_notices','WAWP\Activator::admin_notices_creds_check');

		Addon::instance()::new_addon(array(
			'slug' => CORE_SLUG,
			'name' => CORE_NAME,
			'filename' => $filename,
			'license_check_option' => self::LICENSE_CHECK_OPTION,
			'show_activation_notice' => self::SHOW_NOTICE_ACTIVATION,
			'is_addon' => 0 // only core calls activator, so is_addon will always be false here
		));



	}


	/**
	 * Activates the WAWP plugin.
	 *
	 * Checks if user has already entered valid Wild Apricot credentials and license key
	 * -> If so, then the full Wild Apricot functionality is run
	 */
	public static function activate_plugin_callback() {
		/**
		 * Log back into Wild Apricot if credentials are entered and a valid license key is provided
		 */

		// Call Addon's activation function
		// returns false & does disable_plugin if license is invalid/nonexistent
		
		$is_disabled = Addon::is_plugin_disabled();
		if ($is_disabled) return;
		if (!WAIntegration::valid_wa_credentials()) {
			do_action('disable_plugin', CORE_SLUG, Addon::LICENSE_STATUS_NOT_ENTERED);
			Log::wap_log_warning('Missing Wild Apricot API credentials. Plugin functionality disabled.');
			return;
		}
		$did_activate = Addon::instance()::activate(CORE_SLUG);
		if (!$did_activate) {
			Log::wap_log_warning('Missing license key for ' . CORE_NAME . ' plugin functionality disabled.');
			return;
		}

		Log::wap_log_debug('Plugin activated.');

		// **** this code will only run if license AND wa credentials are valid ****
		// Run credentials obtained hook, which will read in the credentials in WAIntegration.php
		do_action('wawp_wal_credentials_obtained');
		// Also create CRON event to refresh the membership levels/groups
		require_once('MySettingsPage.php');
		MySettingsPage::setup_cron_job();
	}


	/**
	 * Checks the status of the WA Authorization credentials and the license key
	 * Displays appropriate admin notice messages if either one is invalid or missing. 
	 * 
	 * @return void
	 */
	public static function admin_notices_creds_check() {
		if (!is_wawp_settings() && !is_plugin_page() && !is_post_edit_page()) return;

		// only display these messages on wawp settings page or plugin page right after plugin is activated
		$exception = Exception::fatal_error();
		if ($exception) {
			Exception::admin_notice_error_message_template($exception);
			return;
		}

		if (Addon::is_plugin_disabled()) {
			self::empty_wa_message();
			return;
		}

		$should_activation_show_notice = get_option(self::SHOW_NOTICE_ACTIVATION);
		$valid_wa_creds = WAIntegration::valid_wa_credentials();
		$valid_license = Addon::instance()::has_valid_license(CORE_SLUG);

		if (!$valid_wa_creds && (is_wawp_settings()
		 || is_plugin_page() && $should_activation_show_notice)) {
			unset($_GET['activate']);
			Addon::update_show_activation_notice_option(CORE_SLUG, 0);
			if (!$valid_license) {
				/**
				 * if both creds are invalid show one message telling the user 
				 * to enter both instead of two separate messages
				 */
				self::empty_creds_message();
			} else {
				self::empty_wa_message();
			}
			return;
		}

		// print out licensing messages
		Addon::instance()::license_admin_notices();
	}

	private static function empty_creds_message() {
		echo "<div class='notice notice-warning'><p>";
		echo "Please enter your ";
		echo "<a href=" . esc_url(admin_url('admin.php?page=wawp-login')) . ">Wild Apricot credentials</a>";
		echo " and ";
		echo "<a href=" . esc_url(admin_url('admin.php?page=wawp-licensing')) . "> license key</a>";
		echo " in order to use the <strong>" . CORE_NAME . "</strong> functionality.";
		echo "</p></div>";
	}


	private static function empty_wa_message() {

		echo "<div class='notice notice-warning'><p>";
		echo "Please enter your Wild Apricot credentials";
		if (!is_wa_login_menu()) {
			echo " in <a href=" . esc_html__(admin_url('admin.php?page=wawp-login')) . ">Wild Apricot Press > Authorization</a>";
		}
		echo " in order to use the <strong>" . CORE_NAME . "</strong> functionality.";
		echo "</p></div>";
	}

	// private static function fatal_error_message() {
	// 	Exception::admin_notice_error_message_template();
	// }

}
?>
