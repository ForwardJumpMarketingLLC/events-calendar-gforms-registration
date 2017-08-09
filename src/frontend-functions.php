<?php
/**
 * Front end functions.
 *
 * @package     ForwardJump\ECGF_Registration
 * @author      Tim Jensen <tim@forwardjump.com>
 * @license     GNU General Public License 2.0+
 * @link        https://forwardjump.com
 * @since       0.1.0
 */

namespace ForwardJump\ECGF_Registration;

add_action( 'tribe_events_before_view', __NAMESPACE__ . '\\front_end_init' );
/**
 * Initializes extending the Gravity Form and displaying it on the event.
 *
 * @since 0.1.0
 *
 * @return void
 */
function front_end_init() {
	( new Extend_Gravity_Form() )->init();

	( new Display_Event_Registration_Form() )->init();
}

// Adds the post id to the GF lead detail.
add_action( 'gform_after_submission', [ Extend_Gravity_Form::class, 'add_post_id_to_lead_detail' ] );

add_filter( 'gform_pre_validation', __NAMESPACE__ . '\\add_validation_filter_to_event_reservations' );
/**
 * Adds a validation filter to forms that are being used for event registration.
 *
 * @param array $form The current form to be filtered.
 *
 * @return array
 */
function add_validation_filter_to_event_reservations( $form ) {

	if ( ! tribe_is_event() ) {
		return $form;
	}

	global $wp_filter;

	$form_settings = get_post_meta( get_the_ID(), 'ecgf_form_settings', true );

	foreach ( (array) $form_settings as $field ) {
		add_filter( "gform_field_validation_{$form['id']}_{$field['field_id']}", __NAMESPACE__ . '\\validate_reservation_request', 10, 4 );
	}

	return $form;
}

/**
 * Validate the reservation request to make sure the max number is not exceeded.
 *
 * @param array  $result Validation result.
 * @param string $value  Form field value.
 * @param array  $form   Form The current form to be filtered.
 * @param object $field  Form field data.
 *
 * @return mixed
 */
function validate_reservation_request( $result, $value, $form, $field ) {

	if ( '0' !== $value && 0 > (int) $value ) {
		$result['is_valid'] = false;
		$result['message'] = 'Please enter a positive number.';
		return $result;
	}

	$extended_form = new Extend_Gravity_Form();
	$available_reservations = $extended_form->get_available_reservations();

	$remaining_reservations = isset( $available_reservations[ $field->id ] ) ? $available_reservations[ $field->id ] : null;

	if ( is_null( $remaining_reservations ) ) {
		return $result;
	}

	$result['is_valid'] = ( (int) $value <= $remaining_reservations );

	if ( ! $result['is_valid'] ) {

		if ( 0 === (int) $remaining_reservations ) {
			$result['message'] = 'Sorry, there are no more seats available.';
		} else {
			$result['message'] = "Please enter a value less than {$remaining_reservations}";
		}
	}

	return $result;
}
