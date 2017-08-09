<?php
/**
 * Display Event Registration Form.
 *
 * @package     ForwardJump\ECGF_Registration
 * @author      Tim Jensen <tim@timjensen.us>
 * @license     GNU General Public License 2.0+
 * @link        https://www.timjensen.us
 * @since       0.1.0
 */

namespace ForwardJump\ECGF_Registration;

/**
 * Class Display_Event_Registration_Form
 *
 * @package ForwardJump\ECGF_Registration
 */
class Display_Event_Registration_Form {

	/**
	 * ID of the Gravity Form to be rendered.
	 *
	 * @var int
	 */
	protected $gform_id;

	/**
	 * Display_Event_Registration_Form constructor.
	 */
	public function __construct() {
		$this->gform_id = get_post_meta( get_the_ID(), 'ecgf_selected_form_id', true );
	}

	/**
	 * Hook into the event to render the selected Gravity Form.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'tribe_events_single_event_after_the_meta', [ $this, 'render_gravity_form' ] );
	}

	/**
	 * Render the Gravity Form that was selected on the Event edit screen.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render_gravity_form() {
		if ( ! $this->gform_id ) {
			return;
		}

		echo do_shortcode("[gravityform id={$this->gform_id}]");
	}
}
