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
 * @package         ForwardJump\EC_GF_Registration
 */

namespace ForwardJump\EC_GF_Registration;

$constants = [
	'EC_GF_DIR'             => __DIR__,
	'EC_GF_PATH'            => __FILE__,
	'EC_GF_URL'             => plugins_url( null, __FILE__ ),
	'EC_GF_CONFIG_DIR'      => __DIR__ . '/config',
	'EC_GF_DIR_TEXT_DOMAIN' => 'events-calendar-gforms-registration'
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
	require_once EC_GF_DIR . '/vendor/CMB2/init.php';
	require_once EC_GF_DIR . '/src/functions.php';
}

require_once EC_GF_DIR . '/vendor/autoload.php';


