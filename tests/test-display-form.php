<?php
/**
 * Class ECGF_Display_Form
 *
 * @package ForwardJump\ECGF_Registration
 */

namespace ForwardJump\ECGF_Registration;

/**
 * Tests related to displaying the form.
 */
class ECGF_Display_Form extends \WP_UnitTestCase {

	/**
	 * Check for Display_Event_Registration_Form class.
	 */
	function test_display_event_registration_form_class_exists() {
		$this->assertTrue( class_exists( 'ForwardJump\ECGF_Registration\Display_Event_Registration_Form') );
	}

}
