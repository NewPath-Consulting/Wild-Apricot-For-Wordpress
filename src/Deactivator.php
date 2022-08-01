<?php

namespace WAWP;

require_once __DIR__ . '/WAWPApi.php';
require_once __DIR__ . '/WAIntegration.php';
require_once __DIR__ . '/MySettingsPage.php';

class Deactivator {

	/**
	 * Deactivate function. Makes login page private and removes cron jobs.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Set WAWP WildApricot Login page to a Private page so that users cannot access it
		// https://wordpress.stackexchange.com/questions/273557/how-to-set-post-status-to-delete
		// First, get the id of the Login page
		$login_page_id = get_option(WAIntegration::LOGIN_PAGE_ID_OPT);
		if (isset($login_page_id) && $login_page_id != '') { // valid
			$login_page = get_post($login_page_id, 'ARRAY_A');
			$login_page['post_status'] = 'private';
			wp_update_post($login_page);
		}

		// Unschedule the CRON events
		WAWPApi::unsetCronJob(MySettingsPage::CRON_HOOK);
		WAWPApi::unsetCronJob(WAIntegration::USER_REFRESH_HOOK);
		WAWPApi::unsetCronJob(WAIntegration::LICENSE_CHECK_HOOK);
	}
}
?>
