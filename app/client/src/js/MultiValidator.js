(function($) {
  $.entwine('ss', function($) {

    function escapeHtml(text) {
      return $('<div/>').text(text).html();
    }

    function convertNewLineToBR(text) {
      return text.replace("\n", '<br>');
    }

    /**
     * Sets a message in the top right of the CMS.
     * @param {string} text
     * @param {string} type
     */
    function statusMessage(text, type) {
      // Escape HTML entities in text
      text = escapeHtml(text);
      $.noticeAdd({
        text: text,
        type: type,
        stayTime: 5000,
        inEffect: {
          left: '0',
          opacity: 'show',
        },
      });
    };

    /**
     * Set (or clear) the form's error message.
     * @param {jQuerySub} $form
     * @param {string} msg
     */
    function setFormErrorMsg($form, msg) {
      const idPrefix = $form.attr('id') + '_';
      const $elem = $form.find('#' + idPrefix + 'error');
      $elem.text(msg);
      if (msg) {
        $elem.addClass('validation');
        $elem.show();
      } else {
        $elem.removeClass('validation');
        $elem.hide();
      }
    }

    /**
     * Add validation error message elements for each field which failed validation.
     * @param {array} data
     * @param {jQuerySub} $form
     */
    function displayValidationErrors(data, $form) {
      $form.addClass('validationerror');
      setFormErrorMsg($form, 'There are validation errors on this page, please fix them before saving or publishing.');
      const idPrefix = $form.attr('id') + '_';
      const holderSuffix = '_Holder';

      for(let i = 0; i < data.length; i++) {
        const error = data[i];
        // Get the field before which to insert the validation error message.
        const id = idPrefix + error.fieldName;
        const $holder = $('#' + id + holderSuffix);
        let $field = null;
        if ($holder.length) {
          $field = $holder;
        } else {
          $field = $('#' + id);
        }
        // Create and insert the validation error message element.
        const $message = $('<div/>').html(convertNewLineToBR(error.message))
        .addClass('js-ajax-validation message ' + error.messageType);
        $message.insertBefore($field);
      }

      statusMessage('Validation Error', 'error');
    }

    /**
     * Clear all previous validation error messages.
     * @param {jQuerySub} $form
     */
    function clearValidation($form) {
      setFormErrorMsg($form, '');
      $form.find('.js-ajax-validation').remove();
    }

    /**
     * Submit handler for ajax validation forms.
     */
    $('form.js-multi-validator-ajax').entwine({
      onsubmit(event, button) {
        const $form = this;
        clearValidation($form);
        let hasErrors = false;

        // Perform these actions if the validation POST request is successful.
        function successFn(data) {
          if (data !== true) {
            displayValidationErrors(data, $form);
            hasErrors = true;
          }
        }

        // Perform these actions if there is an error in the validation POST request.
        function errorFn(request, status, error) {
          statusMessage('Could not validate. Aborting AJAX validation.', 'error');
          console.error('Error with AJAX validation request: ' + status + ': ' + error);
        }

        // Validate.
        const validateUrl = this.data('validation-link');
        const serialised = this.serializeArray();
        serialised.push({name:'action_app_ajaxValidate', value:'1'});
        $.ajax({
          type: 'POST',
          url: validateUrl,
          data: serialised,
          success: successFn,
          error: errorFn,
          async:false
        });

        if (hasErrors) {
          // Don't submit the form if there are errors.
          event.preventDefault();
          return false;
        } else {
          // If we're in the CMS and we've been provided a button action, we need to tell the container to submit the form.
          // This ensures that the correct sequence of events occurs. Relying on bubbling this event can result in errors.
          if (button) {
            const cmsContainer = this.closest('.cms-container');
            if (cmsContainer.length) {
              cmsContainer.submitForm(this, button);
              event.preventDefault();
              return false;
            }
          }
          // If we're not in the CMS or haven't been given a button, let SilverStripe handle the event directly.
          this._super(event, button);
        }
      }
    });

  });
})(jQuery);
