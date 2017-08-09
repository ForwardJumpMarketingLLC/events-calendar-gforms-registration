;(
    function($, window, document) {
      'use strict';

      var formIdSelector = {},
          formFieldsSelector = {},
          selectedFormId,
          data;

      function setFormIdSelector() {
        formIdSelector = $('.cmb2-id-ecgf-selected-form-id');
      }

      function setFormFieldsSelectors() {
        formFieldsSelector = $('.cmb2-id-ecgf-form-fields select');
      }

      function formSelectListener() {
        formIdSelector.on('change', '#ecgf_selected_form_id', ajaxUpdate);
      }

      function ajaxUpdate() {
        setFormFieldsSelectors();

        $(formFieldsSelector).empty();
        $(formFieldsSelector).parent().addClass('ajaxing');

        selectedFormId = $(this).val();

        data = {
          'action': 'btu_get_gform_field_list',
          'formId': selectedFormId,
        };

        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: data,
          dataType: 'json',
          success: function(data) {
            $(formFieldsSelector).parent().removeClass('ajaxing');

            $.each(data, function(key, value) {
              $(formFieldsSelector).append($('<option/>', {
                value: key,
                text: value,
              }));
            });
          },
        });

      };

      function init() {
        setFormIdSelector();
        setFormFieldsSelectors();
        formSelectListener();
      }

      $(document).ready(function() {
        init();
      });

    }
)(jQuery, window, document);
