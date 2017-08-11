<?php
/**
 * Class ECGF_Admin
 *
 * @package ForwardJump\ECGF_Registration
 */

namespace ForwardJump\ECGF_Registration;

/**
 * Tests related to WP Admin.
 */
class ECGF_Admin extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		set_current_screen( 'edit-post' );

		load_admin_files();

		$cmb_init = \CMB2_Bootstrap_2252::initiate();
		$cmb_init->include_cmb();
	}

	/**
	 * Check for Post_Metabox class.
	 */
	function test_post_metabox_class_exists() {
		$this->assertTrue( class_exists( 'ForwardJump\ECGF_Registration\Post_Metabox') );
	}

	/**
	 * Check that our config can be used to create an instance of CMB2.
	 */
	function test_get_instance_of_cmb2() {
		$config = include ECGF_CONFIG_DIR . '/post-metabox-config.php';
		$metabox = new Post_Metabox( $config[0] );

		$cmb = $metabox->init_metabox();

		$this->assertInstanceOf( 'CMB2', $cmb );
	}
}
