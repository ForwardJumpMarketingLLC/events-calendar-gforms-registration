<?php
/**
 * Extend a Gravity Forms form.
 *
 * @package     ForwardJump\ECGF_Registration
 * @author      Tim Jensen <tim@timjensen.us>
 * @license     GNU General Public License 2.0+
 * @link        https://www.timjensen.us
 * @since       0.1.0
 */

namespace ForwardJump\ECGF_Registration;

/**
 * Class Extend_Gravity_Form
 *
 * @package ForwardJump\ECGF_Registration
 */
class Extend_Gravity_Form {

	/**
	 * The event id field number key used in the database.
	 *
	 * @var float
	 */
	const EVENT_ID_DB_KEY = 0.9999;

	/**
	 * Post ID.
	 *
	 * @var null
	 */
	protected $post_id = null;

	/**
	 * Event form id.
	 *
	 * @var null
	 */
	protected $event_form_id = null;

	/**
	 * Event form settings.
	 *
	 * @var array
	 */
	protected $event_form_settings = [];

	/**
	 * Maximum number of reservations.
	 *
	 * @var array
	 */
	protected $max_reservations = [];

	/**
	 * Number of booked reservations.
	 *
	 * @var array
	 */
	protected $booked_reservations = [];

	/**
	 * Number of available reservations.
	 *
	 * @var array
	 */
	protected $available_reservations = [];

	/**
	 * Extend_Gravity_Form constructor.
	 */
	public function __construct() {}

	/**
	 * Hooks into Gravity Forms to modify the form.
	 *
	 * @return bool|void
	 */
	public function init() {
		add_action( 'wp', [ $this, 'set_properties' ] );
		add_action( 'wp', [ $this, 'gform_hooks' ], 5 );
	}

	/**
	 * Sets the class properties.
	 *
	 * @return bool
	 */
	public function set_properties() {
		$this->post_id             = $this->get_post_id();
		$this->event_form_id       = $this->get_event_form_id();
		$this->event_form_settings = $this->get_event_form_settings();

		if ( empty( $this->event_form_id ) || empty( $this->event_form_settings ) ) {
			return false;
		}

		$this->max_reservations       = $this->get_max_reservations();
		$this->booked_reservations    = $this->get_booked_reservations();
		$this->available_reservations = $this->get_available_reservations();

		return true;
	}

	/**
	 * Get the post_id variable.
	 *
	 * @return null|int
	 */
	protected function get_post_id() {
		if ( ! $this->post_id ) {
			$this->post_id = get_the_ID();
		}

		return $this->post_id;
	}

	/**
	 * Get the event Gravity Form id.
	 *
	 * @return null|string
	 */
	protected function get_event_form_id() {
		if ( ! $this->event_form_id ) {
			$this->event_form_id = get_post_meta( $this->get_post_id(), 'ecgf_form_id', true );
		}

		return $this->event_form_id;
	}

	/**
	 * Get the form settings.
	 *
	 * @return null|array
	 */
	protected function get_event_form_settings() {
		if ( ! $this->event_form_settings ) {
			$this->event_form_settings = get_post_meta( $this->get_post_id(), 'ecgf_form_settings', true );
		}

		return $this->event_form_settings;
	}

	/**
	 * Get the maximum reservations for each of the selected fields.
	 *
	 * @return array
	 */
	protected function get_max_reservations() {
		if ( ! empty( $this->max_reservations ) ) {
			return $this->max_reservations;
		}

		$this->max_reservations = $this->transform_array( $this->event_form_settings, 'field_id', 'max_reservations' );

		return $this->max_reservations;
	}

	/**
	 * Returns the number of participants for the current event.
	 *
	 * @return array
	 */
	protected function get_booked_reservations() {

		if ( $this->booked_reservations ) {
			return $this->booked_reservations;
		}

		$field_number = self::EVENT_ID_DB_KEY;
		$field_ids    = array_column(
			(array) $this->event_form_settings,
			'field_id'
		);

		$field_placeholders = implode( ', ', array_fill( 0, count( $field_ids ), '%s' ) );
		$sql_variables      = array_merge( (array) $field_ids, (array) $field_number, (array) $this->get_post_id() );

		global $wpdb;

		$sql_statement
			= "
			SELECT ld1.field_number AS field_id, SUM(ld1.value) AS booked_reservations
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

		$booked_reservations = $wpdb->get_results( $wpdb->prepare( $sql_statement, $sql_variables ) );

		$this->booked_reservations = $this->transform_array( $booked_reservations, 'field_id', 'booked_reservations' );

		return $this->booked_reservations;
	}

	/**
	 * Returns the number of available registration slots for the given event.
	 *
	 * @param mixed $event_id Post ID for the event.
	 *
	 * @return array
	 */
	protected function get_available_reservations() {

		if ( $this->available_reservations ) {
			return $this->available_reservations;
		}

		if ( empty( $this->get_max_reservations() ) ) {
			return [];
		}

		$booked_reservations = $this->get_booked_reservations();

		foreach ( $this->get_max_reservations() as $index => $max ) {
			$booked                                 = isset( $this->get_booked_reservations()[ $index ] ) ? $this->get_booked_reservations()[ $index ] : 0;
			$this->available_reservations[ $index ] = max( 0, ( $max - $booked ) );
		}

		return $this->available_reservations;
	}

	/**
	 * Hook into the appropriate Gravity Form.
	 *
	 * @return void
	 */
	public function gform_hooks() {
		if ( ! is_singular( 'tribe_events' ) ) {
			return;
		}

		$form_id = $this->get_event_form_id();

		if ( ! $form_id ) {
			return;
		}

		add_filter( "gform_pre_render_{$form_id}", [ $this, 'insert_registration_notice' ] );

		$form_settings = $this->get_event_form_settings();
		foreach ( (array) $form_settings as $field ) {
			add_filter( "gform_field_validation_{$form_id}_{$field['field_id']}", [ $this, 'validate_reservation_request' ], 10, 4 );
		}

		add_action( "gform_after_submission_{$form_id}", [ $this, 'add_post_id_to_lead_detail' ], 10, 2 );
	}

	/**
	 * Inserts an event registration notice above the appropriate form field.
	 *
	 * @param array $form The current form to be filtered.
	 *
	 * @return array
	 */
	public function insert_registration_notice( $form ) {
		$notice             = get_post_meta( $this->get_post_id(), 'ecgf_registration_notice', true );
		$insert_above_field = isset( $this->get_event_form_settings()[0]['field_id'] ) ? $this->get_event_form_settings()[0]['field_id'] : null;

		if ( ! $insert_above_field || ! $notice ) {
			return $form;
		}

		$form_field_ids = array_column( (array) $form['fields'], 'id' );

		$splice_position = array_search( (int) $insert_above_field, (array) $form_field_ids, true );

		$notice = $this->update_registration_notice( $notice, $this->get_event_form_settings() );

		$new_field = \GF_Fields::create(
			[
				'id'       => 'ecgf_registration_notice',
				'type'     => 'html',
				'content'  => $notice,
				'cssClass' => 'ecgf_registration_notice',
			]
		);

		array_splice( $form['fields'], $splice_position, 0, [ $new_field ] );

		return $form;
	}

	/**
	 * Validate the reservation request to make sure the max number is not
	 * exceeded.
	 *
	 * @param array  $result Validation result.
	 * @param string $value  Form field value.
	 * @param array  $form   Form The current form to be filtered.
	 * @param object $field  Form field data.
	 *
	 * @return mixed
	 */
	public function validate_reservation_request( $result, $value, $form, $field ) {

		if ( empty( $value ) ) {
			return $result;
		}

		if ( 1 > (int) $value ) {
			$result['is_valid'] = false;
			$result['message']  = 'Please enter a valid number.';

			return $result;
		}

		$available_reservations = isset( $this->get_available_reservations()[ $field->id ] ) ? $this->get_available_reservations()[ $field->id ] : null;

		if ( is_null( $available_reservations ) ) {
			return $result;
		}

		$result['is_valid'] = ( (int) $value <= $available_reservations );

		if ( ! $result['is_valid'] ) {

			if ( 0 === (int) $available_reservations ) {
				$result['message'] = 'Sorry, there are no more seats available.';
			} else {
				$result['message'] = "Please enter a value up to {$available_reservations}";
			}
		}

		return $result;
	}

	/**
	 * Adds the post ID to the lead detail table when the form is submitted on a
	 * singular Tribe Events page.
	 *
	 * @since 0.1.0
	 *
	 * @param array $entry Form entry information.
	 * @param array $form  The current form to be filtered.
	 *
	 * @return bool
	 */
	public function add_post_id_to_lead_detail( $entry, $form ) {
		if ( empty( $entry ) ) {
			return false;
		}

		global $wpdb;

		$wpdb->insert( "{$wpdb->prefix}rg_lead_detail",
			[
				'value'        => $this->get_post_id(),
				'form_id'      => $entry['form_id'],
				'lead_id'      => $entry['id'],
				'field_number' => self::EVENT_ID_DB_KEY,
			]
		);

		return (bool) $wpdb->__get( 'result' );
	}

	/**
	 * Converts the key value pair for the input array.
	 *
	 * @param array  $array Input array to be transformed.
	 * @param string $key   Array value that should be converted to the key.
	 * @param string $value Array value in the new key value pair.
	 *
	 * @return array
	 */
	protected function transform_array( $array, $key, $value ) {
		$keys   = array_column( (array) $array, $key );
		$values = array_column( (array) $array, $value );

		$array = array_combine( $keys, $values );

		return $array;
	}

	/**
	 * Update the event registration notice.
	 *
	 * @param string $message Message to be displayed.
	 * @param array  $fields  Field data.
	 *
	 * @return string
	 */
	protected function update_registration_notice( $message, $fields ) {
		$remaining_slots  = (array) $this->get_available_reservations();
		$number_of_fields = count( $remaining_slots );

		$search_strings = [];
		for ( $i = 1; $i <= $number_of_fields; $i ++ ) {
			$search_strings[] = "{field_{$i}}";
		}

		$updated_message = str_replace( (array) $search_strings, (array) $remaining_slots, $message );

		return '<span>' . $updated_message . '</span>';
	}
}
