/* eslint-disable-next-line func-names */
(function ($) {
  function isBackendForm() {
    return $.isFunction($.entwine);
  }

  function escapeHtml(text) {
    return $('<div/>').text(text).html();
  }

  function convertNewLineToBR(text) {
    return text.replace('\n', '<br>');
  }

  /**
   * Sets a message in the top right of the CMS.
   * @param {string} text
   * @param {string} type
   */
  function statusMessage(text, type, $form = null) {
    // Escape HTML entities in text
    const safeText = escapeHtml(text);
    if ($.isFunction($.noticeAdd)) {
      $.noticeAdd({
        text: safeText,
        type,
        stayTime: 5000,
        inEffect: {
          left: '0',
          opacity: 'show',
        },
      });
    }
    if (!isBackendForm() && $form != null) {
      $('body,html').animate(
        {
          scrollTop: $form.offset().top,
        },
        500,
      );
    }
  }

  /**
   * Set (or clear) the form's error message.
   * @param {jQuerySub} $form
   * @param {string} msg
   */
  function setFormErrorMsg($form, msg) {
    const idPrefix = `${$form.attr('id')}_`;
    const $elem = $form.find(`#${idPrefix}error`);
    $elem.text(msg);
    if (msg) {
      $elem.addClass('validation validation-bar');
      if (!isBackendForm()) {
        $elem.addClass('form__message form__message--required');
      }
      $elem.show();
    } else {
      $elem.removeClass('validation');
      if (!isBackendForm()) {
        $elem.removeClass('form__message form__message--required');
      }
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
    setFormErrorMsg($form, 'There are validation errors on this form, please fix them before saving or publishing.');
    const idPrefix = `${$form.attr('id')}_`;
    const holderSuffix = '_Holder';

    for (let i = 0; i < data.length; i += 1) {
      const error = data[i];
      // Get the field before which to insert the validation error message.
      const id = `${idPrefix}${error.fieldName.replace(new RegExp(/_{2,}/g), '_')}`;
      const $holder = $(`#${id}${holderSuffix}`);
      let $field = null;
      if (isBackendForm() && $holder.length) {
        $field = $holder;
      } else {
        $field = $(`#${id}`);
      }
      // Add indicator for which tab has an error.
      const tabID = $field.parents('.tab-pane').attr('aria-labelledby');
      $(`#${tabID}`).addClass(`font-icon-attention-1 tab-validation tab-validation--${error.messageType}`);
      // Create and insert the validation error message element.
      const $message = $('<div/>').html(convertNewLineToBR(error.message))
        .addClass(`js-ajax-validation message ${error.messageType}`);
      if (isBackendForm()) {
        $message.insertBefore($field);
      } else {
        $field.addClass('holder-required');
        $message.addClass('form__message form__message--required');
        $message.insertAfter($field);
      }
    }

    statusMessage('Validation Error', 'error', $form);
  }

  /**
   * Clear all previous validation error messages.
   * @param {jQuerySub} $form
   */
  function clearValidation($form) {
    setFormErrorMsg($form, '');
    $form.removeClass('validationerror');
    $form.find('.holder-required').removeClass('holder-required');
    $form.find('.js-ajax-validation').remove();
    const $tabs = $form.find('a.ui-tabs-anchor');
    $tabs.each((index, elem) => {
      $(elem).removeClass('font-icon-attention-1 tab-validation');
      /* eslint-disable-next-line no-param-reassign */
      elem.className = elem.className.replace(new RegExp(/tab-validation--\w*/g, ''));
    });
  }

  /**
   * Form submit handler for ajax validation.
   */
  function onFormSubmit(event, button, entwine, jquery = $) {
    if (!button) {
      // Get button that spawned the submit event.
      const delegatedEvent = event.delegatedEvent ?? event;
      const originalEvent = delegatedEvent.originalEvent ?? delegatedEvent;
      const $clicked = jquery(originalEvent.submitter ?? originalEvent.target);
      if ($clicked.hasClass('element-editor__hover-bar-area')) {
        // Add elemental block button clicked. Don't validate or submit form.
        event.preventDefault();
        return false;
      }
      if ($clicked.attr('name')?.startsWith('action_')) {
        // Set button if the clicked button is a valid FormAction.
        /* eslint-disable-next-line no-param-reassign */
        button = $clicked.get(0);
      }
    }
    jquery(button).attr('disabled', true);
    const $form = entwine !== undefined ? entwine : jquery(this);
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
      /* eslint-disable-next-line no-console */
      console.error(`Error with AJAX validation request: ${status}: ${error}`);
    }

    // Validate.
    const validateUrl = $form.data('validation-link');
    const serialised = $form.serializeArray();
    serialised.push({ name: 'action_app_ajaxValidate', value: '1' });
    if (button) {
      serialised.push({ name: '_original_action', value: button.getAttribute('name') });
    }
    jquery.ajax({
      type: 'POST',
      url: validateUrl,
      data: serialised,
      success: successFn,
      error: errorFn,
      async: false,
    });

    if (hasErrors) {
      jquery(button).attr('disabled', false);
      // Don't submit the form if there are errors.
      event.preventDefault();
      return false;
    }
    if (isBackendForm()) {
      // If we're in the CMS and we've been provided a button action, we need to tell the
      // container to submit the form. This ensures that the correct sequence of events occurs.
      // Relying on bubbling this event can result in errors.
      if (button) {
        const cmsContainer = $form.closest('.cms-container');
        if (cmsContainer.length) {
          cmsContainer.submitForm($form, button);
          event.preventDefault();
          return false;
        }
      }
      // If we're in the CMS and haven't been given a button, let SilverStripe handle the
      // event directly.
      /* eslint-disable-next-line no-underscore-dangle */
      entwine._super(event, button);
    }
    return true;
  }

  if (!isBackendForm()) {
    // This is a front end form. Entwine won't work but isn't needed.
    $('form.js-multi-validator-ajax').on('submit', onFormSubmit);
    return;
  }

  $.entwine('ss', (jquery) => {
    /**
     * Use entwine to bind form submit handler.
     */
    jquery('form.js-multi-validator-ajax').entwine({
      onsubmit(event, button) {
        return onFormSubmit(event, button, this, jquery);
      },
    });
  });
}(jQuery));
