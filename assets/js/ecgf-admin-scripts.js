;(function ($, window, document) {
	'use strict';

	var formIdSelector = {},
		formFieldsSelector = {},
		entriesLinkSelector = {},
		formId = null,
		ajaxData = {};

	/**
	 * Set the selector for form select box.
	 */
	function setFormIdSelector() {
		formIdSelector = $('.cmb2-id-ecgf-form-id');
	}

	/**
	 * Set the selector for the form fields select box.
	 */
	function setFormFieldsSelectors() {
		formFieldsSelector = $('.cmb2-id-ecgf-form-settings select');
	}

	/**
	 * Set the selector for the view entries link.
	 */
	function setEntriesLinkSelector() {
		entriesLinkSelector = $('#gfom-entries-link');
	}

	/**
	 * Set the selected form ID.
	 */
	function setFormId() {
		formId = formIdSelector.find('select').val();
	}

	/**
	 * Listen for a change in the selected form.
	 */
	function formSelectListener() {
		formIdSelector.on('change', '#ecgf_form_id', setFormId );
		formIdSelector.on('change', '#ecgf_form_id', setFormFieldsSelectors );
		formIdSelector.on('change', '#ecgf_form_id', ajaxUpdate);
	}

	/**
	 * Updates the field select options based upon the selected form.
	 */
	function ajaxUpdate() {
		formFieldsSelector.empty();
		formFieldsSelector.parent().addClass('ajaxing');

		ajaxData = {
			'action': 'ecgf_get_gform_field_list',
			'formId': formId,
		};

		$.ajax({
			type    : 'POST',
			url     : ajaxurl,
			data    : ajaxData,
			dataType: 'json',
			success : function (data) {
				formFieldsSelector.parent().removeClass('ajaxing');

				$.each(data, function (key, value) {
					formFieldsSelector.append($('<option/>', {
						value: key,
						text : value,
					}));
				});
			},
		});
	};

	/**
	 * Update the URL for the View Entries link.
	 */
	function updateEntriesLink() {
		if ( false === $.isNumeric( formId ) ) {
			// This is a new form, so let's remove the view entries link.
			entriesLinkSelector.remove();

			return;
		}

		var updatedHref = entriesLinkSelector.attr('href').replace('{form_id}', formId);
		entriesLinkSelector.attr('href', updatedHref);
	}

	/**
	 * Initialize variables and event listeners.
	 */
	function init() {
		setFormIdSelector();
		setFormFieldsSelectors();
		setEntriesLinkSelector();
		setFormId();
		formSelectListener();
		updateEntriesLink();
	}

	/**
	 * Ensure DOM is fully loaded before executing.
	 */
	$(document).ready(function () {
		init();
	});

})(jQuery, window, document);
