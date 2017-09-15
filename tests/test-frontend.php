<?php
/**
 * Class ECGF_Frontend
 *
 * @package ForwardJump\ECGF_Registration
 */

namespace ForwardJump\ECGF_Registration;

/**
 * Tests related to WP Admin.
 */
class ECGF_Frontend extends \WP_UnitTestCase {

	public $post_id = null;

	public $field_id = '10';

	public $max_reservations = '20';

	public $form_settings = [];


	public function setUp() {
		parent::setUp();

		require_once dirname( dirname( __DIR__ ) ) . '/the-events-calendar/the-events-calendar.php';
		require_once dirname( dirname( __DIR__ ) ) . '/gravityforms/gravityforms.php';

		load_frontend_files();

		$this->post_id = $this->factory->post->create(
			[
				'post_type' => 'tribe_events',
				'post_title' => 'Event',
			]
		);

		update_post_meta( $this->post_id, 'ecgf_form_settings',
			[
				[
					'field_id'         => $this->field_id,
					'max_reservations' => $this->max_reservations,
				],
			]
		);

		global $post;
		$post = get_post( $this->post_id );

		setup_postdata( $post );
	}

	function test_dependencies() {
		$this->assertTrue( class_exists( 'Tribe__Events__Main' ) );
		$this->assertTrue( class_exists( 'GFForms' ) );
	}

	function test_correct_post_object() {
		$this->assertSame( get_the_ID(), $this->post_id );
	}

	function test_post_meta() {
		$this->form_settings = get_post_meta( $this->post_id, 'ecgf_form_settings', true );
		$this->assertEquals( $this->field_id, $this->form_settings[0]['field_id'] );
		$this->assertEquals( $this->max_reservations, $this->form_settings[0]['max_reservations'] );
	}

	function test_remaining_reservations() {
		$ecgf = new Extend_Gravity_Form();
		$ecgf->set_properties();

		$this->assertEquals( $ecgf->get_post_id(), $this->post_id );
		$this->assertEquals( $ecgf->get_available_reservations()[ $this->field_id ], $this->max_reservations );
	}
}
