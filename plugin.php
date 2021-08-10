<?php

// namespace WAWP;
/**
 * Plugin Name:       Wild Apricot for WordPress (WAWP)
 * Plugin URI:        https://newpathconsulting.com/wild-apricot-for-wordpress
 * Description:       Integrates your Wild Apricot account with your WordPress website!
 * Version:           1.0.0
 * Requires at least: 5.3
 * Requires PHP:      5.6
 * Author:            NewPath Consulting
 * Author URI:        https://newpathconsulting.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pdev
 * Domain Path:       /public/lang
 */

use WAWP\Activator;

/*
Copyright (C) 2021 NewPath Consulting

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

For further inquires, contact NewPath Consulting at support@newpathconsulting.com
or at 5000 Yonge Street, Suite 1901, Toronto, Ontario, M2N 7E9, Canada.
*/

require_once plugin_dir_path(__FILE__) . 'src/Activator.php';
require_once plugin_dir_path(__FILE__) . 'src/WAIntegration.php';
require_once plugin_dir_path(__FILE__) . 'src/MySettingsPage.php';
require_once plugin_dir_path(__FILE__) . 'src/Deactivator.php';

$activator = new Activator('wawp', plugin_basename(__FILE__), 'Wild Apricot for Wordpress (WAWP)');
// Enqueue stylesheet
add_action('admin_enqueue_scripts', 'wawp_enqueue_admin_script');
function wawp_enqueue_admin_script($hook) {
    wp_enqueue_style('wawp-styles-admin', plugin_dir_url(__FILE__) . 'css/wawp-styles-admin.css', array(), '1.0');
}

// Create settings page
$my_settings_page = new WAWP\MySettingsPage();

$licenses = get_option('wawp_license_keys');
// don't do any WA integration if there is no license key
if ($licenses && array_key_exists('wawp', $licenses)) {
    // Create WA Integration instance
    $wa_integration_instance = new WAWP\WAIntegration();
}


// Deactivation hook
register_deactivation_hook(__FILE__, function() {
	WAWP\Deactivator::deactivate();
} );

?>
