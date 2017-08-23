<?php
/**
 * Plugin Name:     Events Calendar GForms Registration
 * Plugin URI:      https://bitbucket.org/forwardjump/events-calendar-gravity-forms-registration
 * Description:     Use Gravity Forms to handle registration for The Events Calendar events.
 * Author:          Tim Jensen
 * Author URI:      https://forwardjump.com
 * Text Domain:     events-calendar-gforms-registration
 * Domain Path:     /languages
 * Version:         0.1.1
 *
 * BitBucket Plugin URI: https://bitbucket.org/forwardjump/events-calendar-gravity-forms-registration
 *
 * @package         ForwardJump\ECGF_Registration
 */

namespace ForwardJump\ECGF_Registration;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( 'ECGF_DIR', __DIR__ );
define( 'ECGF_PATH', __FILE__ );
define( 'ECGF_URL', plugins_url( null, __FILE__ ) );
define( 'ECGF_CONFIG_DIR', __DIR__ . '/config' );
define( 'ECGF_DIR_TEXT_DOMAIN', 'events-calendar-gforms-registration' );

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init', 5 );
/**
 * Checks for dependecies before loading plugin files.
 *
 * @since 0.1.1
 * @return void
 */
function init() {

	if ( ! class_exists( 'Tribe__Events__Main' ) || ! class_exists( 'GFForms' ) ) {

		add_action( 'admin_notices', __NAMESPACE__ . '\\activation_error_notice' );
		add_action( 'admin_init', __NAMESPACE__ . '\\deactivate_plugin' );

		return;
	}

	require_once ECGF_DIR . '/vendor/autoload.php';

	load_admin_files();

	load_frontend_files();
}

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

/**
 * Loads front end files.
 *
 * @return void
 */
function load_frontend_files() {
	if ( is_admin() ) {
		return;
	}

	require_once ECGF_DIR . '/src/frontend-functions.php';
}

/**
 * Deactivation notice.
 *
 * @since 0.1.0
 */
function activation_error_notice() {

	$plugin_data = get_plugin_data( ECGF_PATH );

	?>
	<div class="notice notice-error is-dismissible">
		<p>Error activating
			<b><?php echo esc_html( isset( $plugin_data['Name'] ) ? $plugin_data['Name'] : 'plugin' ); ?></b>. Please
			activate The Events Calendar and Gravity Forms plugins, then try again.</p>
	</div>
	<?php
}

/**
 * Deactivates this plugin.
 *
 * @return void
 */
function deactivate_plugin() {
	deactivate_plugins( ECGF_PATH, true );
}
