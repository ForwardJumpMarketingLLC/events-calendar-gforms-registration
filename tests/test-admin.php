<?php
/**
 * Class ECGF_Admin
 *
 * @package ForwardJump\EC_GF_Registration
 */

namespace ForwardJump\EC_GF_Registration;

/**
 * Tests related to WP Admin.
 */
class ECGF_Admin extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		set_current_screen( 'edit-post' );

		// Require the plugin bootstrap file so that the CMB2 dependency
		// is read into memory.
		require EC_GF_PATH;
		$cmb_init = \CMB2_Bootstrap_224::initiate();
		$cmb_init->include_cmb();
	}

	/**
	 * Check for Post_Metabox class.
	 */
	function test_post_metabox_class_exists() {
		$this->assertTrue( class_exists( 'ForwardJump\EC_GF_Registration\Post_Metabox') );
	}

	/**
	 * Check that our config can be used to create an instance of CMB2.
	 */
	function test_get_instance_of_cmb2() {
		$config = include EC_GF_CONFIG_DIR . '/post-metabox-config.php';
		$metabox = new Post_Metabox( $config[0] );

		$cmb = $metabox->init_metabox();

		$this->assertInstanceOf( 'CMB2', $cmb );
	}
}
