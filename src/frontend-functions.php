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

add_action( 'gform_loaded', __NAMESPACE__ . '\\extend_gravity_form' );
/**
 * Initializes extending the Gravity Form used for event registration.
 *
 * @since 0.1.0
 *
 * @return void
 */
function extend_gravity_form() {
	( new Extend_Gravity_Form() )->init();
}

add_action( 'tribe_events_single_event_after_the_meta', __NAMESPACE__ . '\\render_gravity_form' );
/**
 * Render the Gravity Form that was selected on the Event edit screen.
 *
 * @since 0.1.0
 *
 * @return void
 */
function render_gravity_form() {

	if ( tribe_is_past_event() ) {
		return;
	}

	$gform_id = get_post_meta( get_the_ID(), 'ecgf_form_id', true );

	if ( 0 === (int) $gform_id ) {
		return;
	}

	$shortcode_options = get_post_meta( get_the_ID(), 'ecgf_form_meta', true );

	$show_title = empty( $shortcode_options[0]['show_form_title'] ) ? 'false' : 'true';
	$show_description = empty( $shortcode_options[0]['show_form_description'] ) ? 'false' : 'true';
	$enable_ajax = empty( $shortcode_options[0]['enable_ajax'] ) ? 'false' : 'true';

	echo do_shortcode("[gravityform id={$gform_id} title={$show_title} description={$show_description} ajax={$enable_ajax}]");
}
