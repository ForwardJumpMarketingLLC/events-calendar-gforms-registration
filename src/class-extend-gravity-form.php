<?php
/**
 * Extend a Gravity Forms form.
 *
 * @package     ForwardJump\EC_GF_Registration
 * @author      Tim Jensen <tim@timjensen.us>
 * @license     GNU General Public License 2.0+
 * @link        https://www.timjensen.us
 * @since       0.1.0
 */

namespace ForwardJump\EC_GF_Registration;


class Extend_Gravity_Form {

	/**
	 * Post ID.
	 *
	 * @var int
	 */
	protected $post_id;

	/**
	 * The event id field number key used in the database.
	 *
	 * @var float
	 */
	protected $event_id_db_key = 0.9999;

	/**
	 * Extend_Gravity_Form constructor.
	 */
	public function __construct() {
	}

	public function init() {
		add_action( 'gform_after_submission', [ $this, 'add_post_id_to_lead_detail' ] );
		add_filter( 'gform_pre_render', [ $this, 'modify_event_registration_form' ] );
	}

	/**
	 * Adds the post ID to the lead detail table when the form is submitted on a
	 * singular Tribe Events page.
	 *
	 * @since 0.1.0
	 *
	 * @param array $entry Form entry information.
	 */
	protected function add_post_id_to_lead_detail( $entry ) {

		if ( ! is_singular( 'tribe_events' ) ) {
			return;
		}

		global $wpdb;

		$wpdb->insert( "{$wpdb->prefix}rg_lead_detail",
			[
				'value'        => $this->post_id,
				'form_id'      => $entry['form_id'],
				'lead_id'      => $entry['id'],
				'field_number' => $this->event_id_db_key,
			]
		);
	}

	/**
	 * Returns the number of participants for the current event.
	 *
	 * @param string $post_id Post ID.
	 *
	 * @return array
	 */
	protected function get_event_participants_count( $post_id = '' ) {

		static $event_participant_count = null;

		if ( $event_participant_count ) {
			return $event_participant_count;
		}

		$post_id      = (string) $post_id ?: get_the_ID();
		$field_number = get_event_id_field_number_key();
		$field_ids    = array_column(
			get_post_meta( $post_id, 'ecgf_form_fields', true ),
			'field_id'
		);

		$field_placeholders = implode( ', ', array_fill( 0, count( $field_ids ), '%s' ) );
		$sql_variables      = array_merge( (array) $field_ids, (array) $field_number, (array) $post_id );

		global $wpdb;

		$sql_statement = "
			SELECT ld1.field_number AS field_id, SUM(ld1.value) AS count
			FROM {$wpdb->prefix}rg_lead_detail AS ld
			INNER JOIN {$wpdb->prefix}rg_lead_detail AS ld1
			ON ld1.lead_id = ld.lead_id 
			AND ld1.field_number IN ( {$field_placeholders} )
			INNER JOIN {$wpdb->prefix}rg_lead AS l 
			ON l.id = ld.lead_id
			AND l.status = 'active'
			WHERE cast(ld.field_number as decimal(5,4)) = %f 
			AND ld.value IN ( %s )
			GROUP BY ld1.field_number
		";

		$results = $wpdb->get_results( $wpdb->prepare( $sql_statement, $sql_variables ) );

		$event_participant_count = $results;

		if ( ! $event_participant_count ) {
			return [];
		}

		return $event_participant_count;
	}

	/**
	 * @param $form
	 *
	 * @return array
	 */
	protected function modify_event_registration_form( $form ) {
		if ( ! tribe_is_event() ) {
			return $form;
		}

		$form = insert_event_registration_message_in_form( $form );
		$form = render_reservation_field_select_options( $form );

		return $form;
	}
}
