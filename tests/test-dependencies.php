<?php
/**
 * Class ECGF_Dependencies
 *
 * @package ForwardJump\EC_GF_Registration
 */

namespace ForwardJump\EC_GF_Registration;

/**
 * Tests the presence of dependencies.
 */
class ECGF_Dependencies extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		set_current_screen( 'edit-post' );

		// Require the plugin bootstrap file so that the CMB2 dependency
		// is read into memory.
		require EC_GF_PATH;
		$cmb_init = \CMB2_Bootstrap_224::initiate();
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
