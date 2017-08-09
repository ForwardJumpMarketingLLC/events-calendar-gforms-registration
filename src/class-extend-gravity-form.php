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
	public function __construct() {
		$this->set_properties();
	}

	/**
	 * Set the post_id variable to the ID of the current post.
	 *
	 * @return false|int
	 */
	protected function set_properties() {
		$this->post_id                = get_the_ID();
		$this->event_form_settings    = get_post_meta( $this->get_post_id(), 'ecgf_form_settings', true );
		$this->max_reservations       = $this->get_max_reservations();
		$this->booked_reservations    = $this->get_booked_reservations();
		$this->available_reservations = $this->get_available_reservations();
	}

	/**
	 * Hooks into Gravity Forms to modify the form.
	 *
	 * @return bool|void
	 */
	public function init() {
		if ( empty( (array) $this->event_form_settings ) ) {
			return false;
		}

		add_filter( 'gform_pre_render', [ $this, 'modify_event_registration_form' ] );
		add_filter( 'gform_pre_validation', [ $this, 'add_validation_filter_to_event_reservations' ] );
	}

	public function add_validation_filter_to_event_reservations() {
		return false;
	}

	/**
	 * Get the post_id variable.
	 *
	 * @return null|int
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * @param $form
	 *
	 * @return array
	 */
	public function modify_event_registration_form( $form ) {
		if ( ! tribe_is_event() || ! $form ) {
			return $form;
		}

		$form = $this->insert_registration_notice( $form );

		return $form;
	}

	/**
	 * Adds the post ID to the lead detail table when the form is submitted on a
	 * singular Tribe Events page.
	 *
	 * @since 0.1.0
	 *
	 * @param array $entry Form entry information.
	 */
	public static function add_post_id_to_lead_detail( $entry ) {

		if ( ! is_singular( 'tribe_events' ) || empty( (array) $entry ) ) {
			return false;
		}

		global $wpdb;

		$wpdb->insert( "{$wpdb->prefix}rg_lead_detail",
			[
				'value'        => get_the_ID(),
				'form_id'      => $entry['form_id'],
				'lead_id'      => $entry['id'],
				'field_number' => self::EVENT_ID_DB_KEY,
			]
		);

		return $wpdb->result;
	}

	/**
	 * Inserts an event registration notice above the appropriate form field.
	 *
	 * @param array $form The current form to be filtered.
	 *
	 * @return array
	 */
	protected function insert_registration_notice( $form ) {
		$notice             = get_post_meta( $this->get_post_id(), 'ecgf_registration_notice', true );
		$insert_above_field = isset( $this->event_form_settings[0]['field_id'] ) ? $this->event_form_settings[0]['field_id'] : null;

		if ( ! $insert_above_field || ! $notice ) {
			return $form;
		}

		$form_field_ids = array_column( (array) $form['fields'], 'id' );

		$splice_position = array_search( (int) $insert_above_field, (array) $form_field_ids, true );

		$notice = $this->update_registration_notice( $notice, $this->event_form_settings );

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
	 * Get the maximum reservations for each of the selected fields.
	 *
	 * @return array
	 */
	protected function get_max_reservations() {
		if ( ! empty( $this->max_reservations ) ) {
			return $this->max_reservations;
		}

		return $this->transform_array( $this->event_form_settings, 'field_id', 'max_reservations' );
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
	public function get_available_reservations() {

		if ( $this->available_reservations ) {
			return $this->available_reservations;
		}

		if ( empty( $this->max_reservations ) ) {
			return [];
		}

		if ( empty( $this->get_booked_reservations() ) ) {
			return (array) $max_reservations;
		}

		foreach ( $this->max_reservations as $index => $max ) {
			$this->available_reservations[ $index ] = max( 0, ( $max - $this->get_booked_reservations()[ $index ] ) );
		}

		return $this->available_reservations;
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
