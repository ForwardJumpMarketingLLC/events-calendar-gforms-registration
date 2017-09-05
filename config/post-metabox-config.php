<?php
/**
 * Post metaboxes.
 *
 * @package ForwardJump\ECGF_Registration
 * @since   0.1.0
 * @author  Tim Jensen
 * @link    https://forwardjump.com/
 * @license GNU General Public License 2.0+
 */

namespace ForwardJump\ECGF_Registration;

/**
 * This is an example config array.
 *
 * 'metabox' (string) => Title of the metabox.
 * 'fields'  (array)  => The CMB2 fields that will display within the metabox.
 */
return [
	[
		'metabox' => [
			'object_types' => [ 'tribe_events' ],
			'classes'      => 'ecgf-registration',
			'title'        => 'Event Registration Information',
			'show_on_cb'   => 'ForwardJump\ECGF_Registration\is_gf_active',
		],
		'fields'  => [
			[
				'name'       => 'Select Registration Form',
				'id'         => 'ecgf_form_id',
				'type'       => 'select',
				'options_cb' => 'ForwardJump\ECGF_Registration\get_forms',
			],
			[
				'type'    => 'group',
				'id'      => 'ecgf_form_settings',
				'options' => array(
					'group_title' => __( 'Form Field - reference as {field_{#}}', ECGF_DIR_TEXT_DOMAIN ),
					'add_button'  => __( 'Add Another Field', ECGF_DIR_TEXT_DOMAIN ),
				),
				'fields'  => [
					[
						'name'             => 'Select Field',
						'description'      => 'This field will be used to keep track of the number of registrants.',
						'id'               => 'field_id',
						'type'             => 'select',
						'show_option_none' => 'None selected',
						'options_cb'       => 'ForwardJump\ECGF_Registration\get_form_fields',
					],
					[
						'name'        => 'Limit',
						'description' => '<p class="cmb2-metabox-description">Enter a number to limit the number of registrants, or leave blank for unlimited.</p>',
						'id'          => 'max_reservations',
						'type'        => 'text_small',
						'attributes'  => [
							'type' => 'number',
							'min'  => '0',
						],
					],
				],
			],
			[
				'type'       => 'group',
				'id'         => 'ecgf_form_meta',
				'repeatable' => false,
				'options'    => array(
					'group_title' => __( 'Form Options', ECGF_DIR_TEXT_DOMAIN ),
				),
				'fields'     => [
					[
						'name' => 'Display form title?',
						'id'   => 'show_form_title',
						'type' => 'checkbox',
					],
					[
						'name' => 'Display form description?',
						'id'   => 'show_form_description',
						'type' => 'checkbox',
					],
					[
						'name' => 'Enable Ajax?',
						'id'   => 'enable_ajax',
						'type' => 'checkbox',
					],
					[
						'name'        => 'Notice',
						'description' => 'Display a message above the first form field that is selected above. (Optional)<br><strong>Example:</strong> "There are only {field_1} seats left."',
						'id'          => 'notice',
						'type'        => 'textarea_small',
					],
				]
			]
		],
	],
];
