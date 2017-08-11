<?php
/**
 * Functions.
 *
 * @package     ForwardJump\ECGF_Registration
 * @author      Tim Jensen <tim@forwardjump.com>
 * @license     GNU General Public License 2.0+
 * @link        https://forwardjump.com
 * @since       0.1.0
 */

namespace ForwardJump\ECGF_Registration;

add_action( 'plugins_loaded', __NAMESPACE__ . '\\event_metabox_init' );
/**
 * Initializes the post metaboxes.
 *
 * @since 0.1.0
 *
 * @return void
 */
function event_metabox_init() {

	$config = include_once ECGF_CONFIG_DIR . '/post-metabox-config.php';

	foreach ( (array) $config as $metabox ) {
		( new Post_Metabox( $metabox ) )->init();
	}
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );
/**
 * Register the assets.
 */
function enqueue_assets() {
	if ( 'tribe_events' !== get_post_type() ) {
		return;
	}

	wp_enqueue_style( 'ecgf_styles', ECGF_URL . '/assets/css/ecgf-admin-styles.css', null, '0.1.0' );
	wp_enqueue_script( 'ecgf_scripts', ECGF_URL . '/assets/js/ecgf-admin-scripts.js', [ 'jquery' ], '0.1.0', true );
}

/**
 * Checks if Gravity Forms is active.
 *
 * @return bool
 */
function is_gf_active() {
	return (bool) class_exists( 'GFFormsModel' );
}

/**
 * Returns an array of the registered Gravity Forms.
 *
 * @return array
 */
function get_forms() {
	if ( ! class_exists( 'GFFormsModel' ) ) {
		return [];
	}

	$forms_array = \GFFormsModel::get_forms();

	$forms_list = [ 'null' => 'None selected' ];
	foreach ( $forms_array as $forms ) {
		$forms_list[ $forms->id ] = $forms->title;
	}

	return $forms_list;
}

/**
 * Get the GF fields from the selected form
 *
 * @param $field    CMB2 field
 *
 * @return array|bool
 */
function get_form_fields( $field ) {

	$form_id = get_post_meta( get_the_ID(), 'ecgf_selected_form_id', true );

	if ( false === (bool) $form_id ) {
		return false;
	}

	$form = \GFFormsModel::get_form_meta( $form_id );

	$fields_list = [ 'null' => 'None selected' ];
	foreach ( $form['fields'] as $field ) {
		$fields_list[ $field->id ] = $field->label;
	}

	return $fields_list;
}

add_action( 'wp_ajax_ecgf_get_gform_field_list', __NAMESPACE__ . '\\get_gform_fields' );
/**
 * Gets an array of fields for the selected Gravity Form to pass to the AJAX
 * request.
 */
function get_gform_fields() {

	$form_id = intval( $_POST['formId'] );

	$fields = \GFFormsModel::get_form_meta( $form_id );

	$options = [ 'null' => 'None selected' ];
	foreach ( $fields['fields'] as $field ) {
		$options[ $field['id'] ] = $field['label'];
	}

	echo json_encode( $options );

	wp_die();
}
