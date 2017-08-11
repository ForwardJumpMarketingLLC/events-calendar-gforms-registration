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
	$gform_id = get_post_meta( get_the_ID(), 'ecgf_selected_form_id', true );

	if ( ! $gform_id ) {
		return;
	}

	echo do_shortcode("[gravityform id={$gform_id}]");
}
