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

	$form_id = get_post_meta( get_the_ID(), 'ecgf_form_id', true );

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
		if ( empty( $field['inputs'] ) ) {
			$options[ $field['id'] ] = $field['label'];
		} else {
			foreach ( $field['inputs'] as $input ) {
				if ( empty( $input['isHidden'] ) ) {
					$options[ $input['id'] ] = $input['label'];
				}
			}
		}
	}

	echo json_encode( $options );

	wp_die();
}

add_action( 'gform_loaded', function() {
//	$extend_gf = new Extend_Gravity_Form();
//	$extend_gf->gform_hooks();
} );

add_filter( 'gform_entry_meta', function ($entry_meta, $form_id){
//	return $entry_meta;
	//data will be stored with the meta key named score
	//label - entry list will use Score as the column header
	//is_numeric - used when sorting the entry list, indicates whether the data should be treated as numeric when sorting
	//is_default_column - when set to true automatically adds the column to the entry list, without having to edit and add the column for display
	//update_entry_meta_callback - indicates what function to call to update the entry meta upon form submission or editing an entry

	$entry_meta['event_id'] = array(
		'label' => 'Event ID',
		'is_numeric' => true,
		'is_default_column' => true,
		'value' => '2',
	);

	return $entry_meta;
}, 10, 2);

add_filter( 'gform_entries_column_filter', __NAMESPACE__ . '\\change_column_data', 10, 5 );
function change_column_data( $value, $form_id, $field_id, $entry, $query_string ) {

	if ( 'event_id' !== $field_id ) {
		return $value;
	}

	return sprintf( '<a href="%s">%s</a>', get_the_permalink( $value ), $value . ' - ' .  get_the_title( $value ) );
}

add_filter( "gform_admin_pre_render", function( $form ) {
	$new_field = \GF_Fields::create(
		[
			'id'                   => 888,
			'type'                 => 'text',
			'label'                => 'Event',
		]
	);

	array_push( $form['fields'], $new_field );
	return $form;
} );
