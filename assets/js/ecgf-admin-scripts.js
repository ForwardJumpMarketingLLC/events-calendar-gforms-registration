;(
    function($, window, document) {
      'use strict';

      var formIdSelector = {},
          formFieldsSelector = {},
          formId,
          data;

      function setFormIdSelector() {
        formIdSelector = $('.cmb2-id-ecgf-form-id');
      }

      function setFormFieldsSelectors() {
        formFieldsSelector = $('.cmb2-id-ecgf-form-settings select');
      }

      function formSelectListener() {
        formIdSelector.on('change', '#ecgf_form_id', ajaxUpdate);
      }

      function ajaxUpdate() {
        setFormFieldsSelectors();

        $(formFieldsSelector).empty();
        $(formFieldsSelector).parent().addClass('ajaxing');

        formId = $(this).val();

        data = {
          'action': 'ecgf_get_gform_field_list',
          'formId': formId,
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
