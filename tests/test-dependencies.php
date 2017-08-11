<?php
/**
 * Class ECGF_Dependencies
 *
 * @package ForwardJump\ECGF_Registration
 */

namespace ForwardJump\ECGF_Registration;

/**
 * Tests the presence of dependencies.
 */
class ECGF_Dependencies extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		set_current_screen( 'edit-post' );

		load_admin_files();

		$cmb_init = \CMB2_Bootstrap_2252::initiate();
		$cmb_init->include_cmb();
	}

	function test_is_admin() {
		$this->assertTrue( is_admin() );
	}

	/**
	 * Check for CMB2.
	 */
	function test_cmb2_is_present() {
		$this->assertTrue( function_exists( 'new_cmb2_box' ) );
	}
}
