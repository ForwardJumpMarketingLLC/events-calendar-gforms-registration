<?php
/**
 * Post metaboxes.
 *
 * @package ForwardJump\EC_GF_Registration
 * @since   0.1.0
 * @author  Tim Jensen
 * @link    https://forwardjump.com/
 * @license GNU General Public License 2.0+
 */

namespace ForwardJump\EC_GF_Registration;

/**
 * This is an example config array.
 *
 * 'post_type'      (string) => Post type slug.
 * 'metabox_title'  (string) => Title of the metabox.
 * 'metabox_fields' (array)  => The CMB2 fields that will display within the
 *                              metabox.
 */
return [
	[
		'metabox' => [
			'object_types' => [ 'tribe_events' ],
			'title'        => 'Event Registration Information',
			'show_on_cb'   => 'ForwardJump\EC_GF_Registration\is_gf_active',
		],
		'fields'  => [
			[
				'name'       => 'Select Registration Form',
				'id'         => 'ecgf_selected_form_id',
				'type'       => 'select',
				'options_cb' => 'ForwardJump\EC_GF_Registration\get_forms',
			],
			[
				'name'        => 'Notice',
				'description' => 'Optionally display a message above the first form field selected below.',
				'id'          => 'ecgf_registration_notice',
				'type'        => 'textarea_small',
			],
			[
				'type'   => 'group',
				'id'     => 'ecgf_form_fields',
				'options'     => array(
					'group_title'   => __( 'Form Field - reference as {field_{#}}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
					'add_button'    => __( 'Add Another Field', 'cmb2' ),
				),
				'fields' => [
					[
						'name' => 'Maximum Registrants',
						'id'   => 'max_participants',
						'type' => 'text_small',
					],
					[
						'name'        => 'Select Field',
						'description' => 'Choose the form field that should be used to update the number of registrants.',
						'id'          => 'field_id',
						'type'        => 'select',
						'options_cb'     => 'ForwardJump\EC_GF_Registration\get_form_fields',
					],
				],
			],
		],
	],
];
