/* eslint-disable-next-line func-names */
(function ($) {
  function isBackendForm() {
    return $.isFunction($.entwine);
  }

  function escapeHtml(text) {
    return $('<div/>').text(text).html();
  }

  function convertNewLineToBR(text) {
    return text.replace(/\n/g, '<br>');
  }

  /**
   * Returns the ss.i18n substitution or the default string.
   * @param {string} translationKey
   * @param {string} defaultStr
   * @param {object} substitutions
   * @returns
   */
  function provideTranslationOrDefault(translationKey, defaultStr, substitutions = null) {
    /* eslint-disable no-undef, no-underscore-dangle */
    if (typeof ss !== 'undefined' && typeof ss.i18n !== 'undefined') {
      return ss.i18n.inject(ss.i18n._t(translationKey, defaultStr), substitutions);
    }
    if (substitutions) {
      const regex = new RegExp('{([A-Za-z0-9_]*)}', 'g');
      /* eslint-disable-next-line no-param-reassign */
      defaultStr = defaultStr.replace(regex, (match, key) => (
        (substitutions[key]) ? substitutions[key] : match
      ));
    }
    return defaultStr;
    /* eslint-enable no-undef, no-underscore-dangle */
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
    const msg = provideTranslationOrDefault(
      'Signify_AjaxCompositeValidator.VALIDATION_ERRORS',
      'There are validation errors on this form, please fix them before saving or publishing.',
    );
    setFormErrorMsg($form, msg);
    const idPrefix = `${$form.attr('id')}_`;
    const holderSuffix = '_Holder';

    for (let i = 0; i < data.length; i += 1) {
      const error = data[i];
      // Errors can be for the form in general.
      if (!error.fieldName) {
        const $message = $('<div/>').html(convertNewLineToBR(error.message))
          .addClass(`js-ajax-validation message ${error.messageType}`);
        $message.insertAfter($form.find(`#${idPrefix}error`));
        /* eslint-disable-next-line no-continue */
        continue;
      }
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
    const statusMsg = provideTranslationOrDefault(
      'Signify_AjaxCompositeValidator.VALIDATION_ERROR_TOAST',
      'Validation Error',
    );
    statusMessage(statusMsg, 'error', $form);
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

  function finallySubmit($form, button) {
    if (isBackendForm()) {
      // If we're in the CMS and we've been provided a button action, we need to tell the
      // container to submit the form. This ensures that the correct sequence of events occurs.
      // Relying on bubbling this event can result in errors.
      if (button) {
        const cmsContainer = $form.closest('.cms-container');
        if (cmsContainer.length) {
          cmsContainer.submitForm($form, button);
        }
      }
      // I'm honestly not sure by what magic it happens but the form submits correctly on the
      // backend.
    } else {
      // On the front-end we have to make the form submit.
      $form.get(0).submitWithoutEvent();
    }
  }

  /**
   * Form submit handler for ajax validation.
   */
  function onFormSubmit(event, button, entwine, jquery = $) {
    // Don't allow the form to submit on its own - because we're using AJAX we have to do things
    // asyncronously and unfortunately I can't find a way to asyncronously preventDefault.
    event.preventDefault();
    if (!button) {
      // Get button that spawned the submit event.
      const delegatedEvent = event.delegatedEvent ?? event;
      const originalEvent = delegatedEvent.originalEvent ?? delegatedEvent;
      const $clicked = jquery(originalEvent.submitter ?? originalEvent.target);
      if ($clicked.hasClass('element-editor__hover-bar-area')) {
        // Add elemental block button clicked. Don't validate or submit form.
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
    $form.addClass('js-validating');
    clearValidation($form);

    // Perform these actions if the validation POST request is successful.
    function successFn(data) {
      let hasErrors = false;
      if (data !== true) {
        if (data.length) {
          hasErrors = true;
        }
      }

      // Confirm recaptcha v2 is completed if present.
      const $recaptchaField = $form.find('div.g-recaptcha');
      if ($recaptchaField.length > 0 && typeof grecaptcha === 'object') {
        const widgetId = $recaptchaField.data('widgetid');
        // If there's no widgetId this is probably recaptcha v3.
        if (widgetId !== null && widgetId !== undefined) {
          // If there's no response, the user hasn't completed the captcha.
          /* eslint-disable-next-line no-undef */
          if (!grecaptcha.getResponse(widgetId)) {
            if (data === true) {
              /* eslint-disable-next-line no-param-reassign */
              data = [];
            }

            const captchaMsg = provideTranslationOrDefault(
              'Signify_AjaxCompositeValidator.CAPTCHA_VALIDATION_ERROR',
              'Please answer the captcha.',
            );
            data.push({
              fieldName: null,
              message: captchaMsg,
              messageType: 'required',
            });
            hasErrors = true;
          }
        }
      }

      // Finish validation, display errors or submit form.
      $form.removeClass('js-validating');
      if (hasErrors) {
        displayValidationErrors(data, $form);
        jquery(button).attr('disabled', false);
        jquery(button).removeClass('loading');
        // Don't submit the form if there are errors.
        return false;
      }
      return finallySubmit($form, button);
    }

    // Perform these actions if there is an error in the validation POST request.
    function errorFn(request, status, error) {
      const cannotValidateMsg = provideTranslationOrDefault(
        'Signify_AjaxCompositeValidator.CANNOT_VALIDATE',
        'Could not validate. Aborting AJAX validation.',
      );
      statusMessage(cannotValidateMsg, 'error');
      /* eslint-disable-next-line no-console */
      console.error(`Error with AJAX validation request: ${status}: ${error}`);
      return finallySubmit($form, button);
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
    });

    return false;
  }

  if (!isBackendForm()) {
    // This is a front end form. Entwine won't work but isn't needed.
    $('form.js-multi-validator-ajax').on('submit', onFormSubmit);
    // Ensure that calling validate() on the form will trigger a submit event
    // See https://developer.mozilla.org/en-US/docs/Web/API/HTMLFormElement/submit_event
    $('form.js-multi-validator-ajax').each((i, elem) => {
      /* eslint-disable-next-line no-param-reassign */
      elem.submitWithoutEvent = elem.submit;
      /* eslint-disable-next-line no-param-reassign */
      elem.submit = function submit() {
        let event;
        if (typeof Event === 'function') {
          event = new Event('submit', { bubbles: true, cancelable: true });
        } else {
          event = document.createEvent('Event');
          event.initEvent('submit', true, true);
        }
        // We can't know what button was used to submit the form but we can guess.
        event.submitter = $(elem).find('button[type="submit"], input[type="submit"]').get(0);
        elem.dispatchEvent(event);
      };
    });
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
