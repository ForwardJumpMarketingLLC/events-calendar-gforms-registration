<?php
/**
 * Plugin Name:     Events Calendar GForms Registration
 * Plugin URI:
 * https://github.com/ForwardJumpMarketingLLC/events-calendar-gforms-registration
 * Description:     Use Gravity Forms to handle registration for The Events
 * Calendar events. Author:          Tim Jensen Author URI:
 * https://forwardjump.com Text Domain:     events-calendar-gforms-registration
 * Domain Path:     /languages Version:         0.1.0
 *
 * @package         ForwardJump\ECGF_Registration
 */

namespace ForwardJump\ECGF_Registration;

define( 'ECGF_DIR', __DIR__ );
define( 'ECGF_PATH', __FILE__ );
define( 'ECGF_URL', plugins_url( null, __FILE__ ) );
define( 'ECGF_CONFIG_DIR', __DIR__ . '/config' );
define( 'ECGF_DIR_TEXT_DOMAIN', 'events-calendar-gforms-registration' );

/**
 * Loads admin files.
 *
 * @return void
 */
function load_admin_files() {
	if ( ! is_admin() ) {
		return;
	}

	require_once ECGF_DIR . '/vendor/CMB2/init.php';
	require_once ECGF_DIR . '/src/admin-functions.php';
}

load_admin_files();

require_once ECGF_DIR . '/vendor/autoload.php';
require_once ECGF_DIR . '/src/frontend-functions.php';


