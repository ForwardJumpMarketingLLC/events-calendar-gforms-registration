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

$constants = [
	'ECGF_DIR'             => __DIR__,
	'ECGF_PATH'            => __FILE__,
	'ECGF_URL'             => plugins_url( null, __FILE__ ),
	'ECGF_CONFIG_DIR'      => __DIR__ . '/config',
	'ECGF_DIR_TEXT_DOMAIN' => 'events-calendar-gforms-registration'
];

/**
 * Define our constants.
 */
array_walk( $constants, function ( $value, $constant ) {

	if ( ! defined( $constant ) ) {
		define( $constant, $value );
	}

} );

if ( is_admin() ) {
	require_once ECGF_DIR . '/vendor/CMB2/init.php';
	require_once ECGF_DIR . '/src/admin-functions.php';
}

require_once ECGF_DIR . '/vendor/autoload.php';
require_once ECGF_DIR . '/src/frontend-functions.php';


