<?php
/**
 * Class ECGF_Dependencies
 *
 * @package ForwardJump\EC_GF_Registration
 */

/**
 * Tests the presence of dependencies.
 */
class ECGF_Dependencies extends WP_UnitTestCase {

	/**
	 * Check for CMB2.
	 */
	function test_cmb2_is_present() {

		$this->assertTrue( class_exists( 'CMB2') );
	}
}
