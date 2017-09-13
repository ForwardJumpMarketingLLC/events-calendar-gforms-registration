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

	$config = apply_filters( 'ecgf_post_metabox_config', $config );

	foreach ( (array) $config as $metabox ) {
		( new Post_Metabox( $metabox ) )->init();
	}
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets' );
/**
 * Register the assets only on Tribe Events pages.
 *
 * @return void
 */
function enqueue_assets() {
	if ( 'tribe_events' !== get_post_type() ) {
		return;
	}

	wp_enqueue_style( 'ecgf_styles', ECGF_URL . '/assets/css/ecgf-admin-styles.css', null, '0.1.0' );
	wp_enqueue_script( 'ecgf_scripts', ECGF_URL . '/assets/js/ecgf-admin-scripts.js', [ 'jquery' ], '0.1.0', true );
}

/**
 * Returns an array of the registered Gravity Forms.
 *
 * @return array
 */
function get_forms() {
	$forms_array = \GFFormsModel::get_forms();

	$forms_list = [ 'null' => 'None selected' ];
	foreach ( $forms_array as $forms ) {
		$forms_list[$forms->id] = $forms->title;
	}

	return $forms_list;
}

/**
 * Get the GF fields from the selected form
 *
 * @param object $field CMB2 field object.
 *
 * @return array|bool
 */
function get_form_fields( $field ) {

	$form_id = get_post_meta( get_the_ID(), 'ecgf_form_id', true );

	if ( false === (bool) $form_id ) {
		return false;
	}

	$options = get_form_field_options( $form_id );

	return $options;
}

add_action( 'wp_ajax_ecgf_get_gform_field_list', __NAMESPACE__ . '\\get_gform_fields' );
/**
 * Gets an array of fields for the selected Gravity Form to pass to the AJAX
 * request.
 *
 * @return bool|void
 */
function get_gform_fields() {

	$form_id = intval( $_POST['formId'] );

	if ( false === (bool) $form_id ) {
		return false;
	}

	$options = get_form_field_options( $form_id );

	echo json_encode( $options );

	wp_die();
}

/**
 * Return a keyed array of field options for the specified Gravity Form.
 *
 * @param int $form_id
 *
 * @return array
 */
function get_form_field_options( $form_id ) {
	$form = \GFFormsModel::get_form_meta( $form_id );

	$options = [];
	foreach ( (array) $form['fields'] as $field ) {
		if ( empty( $field['inputs'] ) ) {
			$options[$field['id']] = $field['label'];
		} else {
			foreach ( $field['inputs'] as $input ) {
				if ( empty( $input['isHidden'] ) ) {
					$options[$input['id']] = $input['label'];
				}
			}
		}
	}

	return $options;
}

add_filter( 'gform_entry_meta', __NAMESPACE__ . '\\modify_entry_meta', 10, 2 );
/**
 * Modifies the entry meta if the form has been designated as a registration form.
 *
 * @param array $entry_meta Entry meta array.
 * @param int $form_id The ID of the form from which the entry value was submitted.
 *
 * @return array
 */
function modify_entry_meta( $entry_meta, $form_id ) {
	if ( ! is_event_registration_form( $form_id ) ) {
		return $entry_meta;
	}

	$entry_meta['event_id'] = array(
		'label' => 'Event Info',
		'is_numeric' => false,
		'is_default_column' => false,
	);

	return $entry_meta;
}

add_filter( 'gform_form_post_get_meta', __NAMESPACE__ . '\\add_event_id_field_to_form_meta' );
/**
 * Modifies the entry meta if the form has been designated as a registration form.
 *
 * @param array $form The form object.
 * @return array
 */
function add_event_id_field_to_form_meta( $form ) {
	if ( 'gf_edit_forms' === rgget( 'page' ) || ! is_event_registration_form( $form['id'] ) ) {
		return $form;
	}

	$new_field = \GF_Fields::create(
		[
			'id'    => 'event_id',
			'label' => 'Event ID',
			'type'  => 'text',
		]
	);

	$form['fields'][] = $new_field;

	return $form;
}

add_filter( 'gform_entries_column_filter', __NAMESPACE__ . '\\change_column_data', 10, 5 );
/**
 * Changes how the 'event_id' meta value is rendered.
 *
 * @param string  $value        Current value that will be displayed in this
 *                              cell.
 * @param integer $form_id      ID of the current form.
 * @param integer $field_id     ID of the field that this column applies to.
 * @param object  $entry        Current entry object.
 * @param string  $query_string Current page query string with search and
 *                              pagination state.
 *
 * @return string
 */
function change_column_data( $value, $form_id, $field_id, $entry, $query_string ) {
	if ( 'event_id' !== $field_id ) {
		return $value;
	}

	return event_info_output( $value );
}

/**
 * Renders basic information about the event.
 *
 * @param string|int $event_id The post ID of the event.
 * @param bool       $link     Whether the value should be rendered as a link.
 *
 * @return string
 */
function event_info_output( $event_id, $link = true ) {
	if ( ! $event_id ) {
		return '';
	}

	$info = $event_id . ' - ' . get_the_title( $event_id );

	if ( ! $link ) {
		return $info;
	}

	return sprintf( '<a href="%s">%s</a>', get_edit_post_link( $event_id ), $info );
}

add_filter( 'gform_entry_field_value', __NAMESPACE__ . '\\modify_entry_event_id_field_value', 10, 4 );
/**
 * @param string $value The current entry value to be filtered.
 * @param object $field The field from which the entry value was submitted.
 * @param array $lead The current entry.
 * @param array $form The form from which the entry value was submitted.
 * @return string
 */
function modify_entry_event_id_field_value( $value, $field, $lead, $form ) {
	if ( isset( $field['id'] ) && 'event_id' === $field['id'] ) {
		$value = event_info_output( $value, true );
	}

	return $value;
}

add_filter( 'gform_export_field_value', __NAMESPACE__ . '\\modify_exported_event_value', 10, 4 );
/**
 * Modify the Event field value when entries are exported.
 *
 * @param string $value    Value of the field being exported.
 * @param int    $form_id  ID of the current form.
 * @param int    $field_id ID of the current field.
 * @param object $entry    The current entry.
 *
 * @return string
 */
function modify_exported_event_value( $value, $form_id, $field_id, $entry ) {
	if ( 'event_id' === $field_id && ! empty( $value ) ) {
		$value = event_info_output( $value, false );
	}

	return $value;
}

/**
 * Returns the ids of forms that have been designated as registration forms.
 *
 * @return array
 */
function get_registration_form_ids() {

	global $wpdb;

	$sql_statement
		= "			
			SELECT pm.meta_value AS form_id
			FROM $wpdb->posts AS p
			INNER JOIN $wpdb->postmeta AS pm
			ON pm.post_id = p.ID
			   AND pm.meta_key IN ( 'ecgf_form_id' )
			       AND pm.meta_value NOT IN ( '' )
			WHERE p.post_type IN ( 'tribe_events' )
			AND p.post_status NOT IN ( 'trash' )
			GROUP BY form_id
		";

	$results = $wpdb->get_results( $sql_statement );

	return array_column( $results, 'form_id' );
}

/**
 * Checks if the specified form is among the designated registration forms.
 *
 * @param string|int $form_id Form ID.
 *
 * @return bool
 */
function is_event_registration_form( $form_id ) {
	$form_ids = get_registration_form_ids();

	return in_array( $form_id, $form_ids );
}

add_action( 'gform_admin_pre_render', __NAMESPACE__ . '\\add_event_info_merge_tag' );
/**
 * Add the {event_info} merge tag to the drop-down options.
 *
 * @param array $form Form to be filtered.
 *
 * @return array
 */
function add_event_info_merge_tag( $form ) {
	if ( ! is_event_registration_form( $form['id'] ) ) {
		return $form;
	}

	?>
	<script type="text/javascript">
		gform.addFilter('gform_merge_tags', 'add_merge_tags');

		function add_merge_tags(mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option) {
			mergeTags["other"].tags.push({tag: '{event_info}', label: 'Event Registration Info'});

			return mergeTags;
		}
	</script>
	<?php

	return $form;
}

