<?php
/**
 * Class ECGF_Display_Form
 *
 * @package ForwardJump\EC_GF_Registration
 */

namespace ForwardJump\EC_GF_Registration;

/**
 * Tests related to displaying the form.
 */
class ECGF_Display_Form extends \WP_UnitTestCase {

	/**
	 * Check for CMB2.
	 */
	function test_display_event_registration_form_class_exists() {

		$this->assertTrue( class_exists( 'ForwardJump\EC_GF_Registration\Display_Event_Registration_Form') );
	}
}
