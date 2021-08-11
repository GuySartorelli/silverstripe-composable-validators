/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*************************************************!*\
  !*** ./client/src/js/AjaxCompositeValidator.js ***!
  \*************************************************/
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

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


  function provideTranslationOrDefault(translationKey, defaultStr) {
    var substitutions = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;

    /* eslint-disable no-undef, no-underscore-dangle */
    if (typeof ss !== 'undefined' && typeof ss.i18n !== 'undefined') {
      return ss.i18n.inject(ss.i18n._t(translationKey, defaultStr), substitutions);
    }

    if (substitutions) {
      var regex = new RegExp('{([A-Za-z0-9_]*)}', 'g');
      /* eslint-disable-next-line no-param-reassign */

      defaultStr = defaultStr.replace(regex, function (match, key) {
        return substitutions[key] ? substitutions[key] : match;
      });
    }

    return defaultStr;
    /* eslint-enable no-undef, no-underscore-dangle */
  }
  /**
   * Sets a message in the top right of the CMS.
   * @param {string} text
   * @param {string} type
   */


  function statusMessage(text, type) {
    var $form = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    // Escape HTML entities in text
    var safeText = escapeHtml(text);

    if ($.isFunction($.noticeAdd)) {
      $.noticeAdd({
        text: safeText,
        type: type,
        stayTime: 5000,
        inEffect: {
          left: '0',
          opacity: 'show'
        }
      });
    }

    if (!isBackendForm() && $form != null) {
      $('body,html').animate({
        scrollTop: $form.offset().top
      }, 500);
    }
  }
  /**
   * Set (or clear) the form's error message.
   * @param {jQuerySub} $form
   * @param {string} msg
   */


  function setFormErrorMsg($form, msg) {
    var idPrefix = "".concat($form.attr('id'), "_");
    var $elem = $form.find("#".concat(idPrefix, "error"));
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
    var msg = provideTranslationOrDefault('Signify_AjaxCompositeValidator.VALIDATION_ERRORS', 'There are validation errors on this form, please fix them before saving or publishing.');
    setFormErrorMsg($form, msg);
    var idPrefix = "".concat($form.attr('id'), "_");
    var holderSuffix = '_Holder';

    for (var i = 0; i < data.length; i += 1) {
      var error = data[i]; // Errors can be for the form in general.

      if (!error.fieldName) {
        var _$message = $('<div/>').html(convertNewLineToBR(error.message)).addClass("js-ajax-validation message ".concat(error.messageType));

        _$message.insertAfter($form.find("#".concat(idPrefix, "error")));
        /* eslint-disable-next-line no-continue */


        continue;
      } // Get the field before which to insert the validation error message.


      var id = "".concat(idPrefix).concat(error.fieldName.replace(new RegExp(/_{2,}/g), '_'));
      var $holder = $("#".concat(id).concat(holderSuffix));
      var $field = null;

      if (isBackendForm() && $holder.length) {
        $field = $holder;
      } else {
        $field = $("#".concat(id));
      } // Add indicator for which tab has an error.


      var tabID = $field.parents('.tab-pane').attr('aria-labelledby');
      $("#".concat(tabID)).addClass("font-icon-attention-1 tab-validation tab-validation--".concat(error.messageType)); // Create and insert the validation error message element.

      var $message = $('<div/>').html(convertNewLineToBR(error.message)).addClass("js-ajax-validation message ".concat(error.messageType));

      if (isBackendForm()) {
        $message.insertBefore($field);
      } else {
        $field.addClass('holder-required');
        $message.addClass('form__message form__message--required');
        $message.insertAfter($field);
      }
    }

    var statusMsg = provideTranslationOrDefault('Signify_AjaxCompositeValidator.VALIDATION_ERROR_TOAST', 'Validation Error');
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
    var $tabs = $form.find('a.ui-tabs-anchor');
    $tabs.each(function (index, elem) {
      $(elem).removeClass('font-icon-attention-1 tab-validation');
      /* eslint-disable-next-line no-param-reassign */

      elem.className = elem.className.replace(new RegExp(/tab-validation--\w*/g, ''));
    });
  }

  function finallySubmit($form, event, button) {
    if (isBackendForm()) {
      // If we're in the CMS and we've been provided a button action, we need to tell the
      // container to submit the form. This ensures that the correct sequence of events occurs.
      // Relying on bubbling this event can result in errors.
      if (button) {
        var cmsContainer = $form.closest('.cms-container');

        if (cmsContainer.length) {
          cmsContainer.submitForm($form, button);
          event.preventDefault();
        }
      } // I'm honestly not sure by what magic it happens but the form submits correctly on the
      // backend.

    } else {
      // On the front-end we have to make the form submit.
      $form.get(0).submitWithoutEvent();
    }
  }
  /**
   * Form submit handler for ajax validation.
   */


  function onFormSubmit(event, button, entwine) {
    var jquery = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : $;
    // Don't allow the form to submit on its own - because we're using AJAX we have to do things
    // asyncronously and unfortunately I can't find a way to asyncronously preventDefault.
    event.preventDefault();

    if (!button) {
      var _event$delegatedEvent, _delegatedEvent$origi, _originalEvent$submit, _$clicked$attr;

      // Get button that spawned the submit event.
      var delegatedEvent = (_event$delegatedEvent = event.delegatedEvent) !== null && _event$delegatedEvent !== void 0 ? _event$delegatedEvent : event;
      var originalEvent = (_delegatedEvent$origi = delegatedEvent.originalEvent) !== null && _delegatedEvent$origi !== void 0 ? _delegatedEvent$origi : delegatedEvent;
      var $clicked = jquery((_originalEvent$submit = originalEvent.submitter) !== null && _originalEvent$submit !== void 0 ? _originalEvent$submit : originalEvent.target);

      if ($clicked.hasClass('element-editor__hover-bar-area')) {
        // Add elemental block button clicked. Don't validate or submit form.
        return false;
      }

      if ((_$clicked$attr = $clicked.attr('name')) !== null && _$clicked$attr !== void 0 && _$clicked$attr.startsWith('action_')) {
        // Set button if the clicked button is a valid FormAction.

        /* eslint-disable-next-line no-param-reassign */
        button = $clicked.get(0);
      }
    }

    jquery(button).attr('disabled', true);
    var $form = entwine !== undefined ? entwine : jquery(this);
    $form.addClass('js-validating');
    clearValidation($form); // Perform these actions if the validation POST request is successful.

    function successFn(data) {
      var hasErrors = false;

      if (data !== true) {
        if (data.length) {
          hasErrors = true;
        }
      } // Confirm recaptcha v2 is completed if present.


      var $recaptchaField = $form.find('div.g-recaptcha');

      if ($recaptchaField.length > 0 && (typeof grecaptcha === "undefined" ? "undefined" : _typeof(grecaptcha)) === 'object') {
        var widgetId = $recaptchaField.data('widgetid'); // If there's no widgetId this is probably recaptcha v3.

        if (widgetId !== null && widgetId !== undefined) {
          // If there's no response, the user hasn't completed the captcha.

          /* eslint-disable-next-line no-undef */
          if (!grecaptcha.getResponse(widgetId)) {
            if (data === true) {
              /* eslint-disable-next-line no-param-reassign */
              data = [];
            }

            var captchaMsg = provideTranslationOrDefault('Signify_AjaxCompositeValidator.CAPTCHA_VALIDATION_ERROR', 'Please answer the captcha.');
            data.push({
              fieldName: null,
              message: captchaMsg,
              messageType: 'required'
            });
            hasErrors = true;
          }
        }
      } // Finish validation, display errors or submit form.


      $form.removeClass('js-validating');

      if (hasErrors) {
        displayValidationErrors(data, $form);
        jquery(button).attr('disabled', false);
        jquery(button).removeClass('loading'); // Don't submit the form if there are errors.

        return false;
      }

      return finallySubmit($form, event, button, entwine);
    } // Perform these actions if there is an error in the validation POST request.


    function errorFn(request, status, error) {
      var cannotValidateMsg = provideTranslationOrDefault('Signify_AjaxCompositeValidator.CANNOT_VALIDATE', 'Could not validate. Aborting AJAX validation.');
      statusMessage(cannotValidateMsg, 'error');
      /* eslint-disable-next-line no-console */

      console.error("Error with AJAX validation request: ".concat(status, ": ").concat(error));
    } // Validate.


    var validateUrl = $form.data('validation-link');
    var serialised = $form.serializeArray();
    serialised.push({
      name: 'action_app_ajaxValidate',
      value: '1'
    });

    if (button) {
      serialised.push({
        name: '_original_action',
        value: button.getAttribute('name')
      });
    }

    jquery.ajax({
      type: 'POST',
      url: validateUrl,
      data: serialised,
      success: successFn,
      error: errorFn
    });
    return false;
  }

  if (!isBackendForm()) {
    // This is a front end form. Entwine won't work but isn't needed.
    $('form.js-multi-validator-ajax').on('submit', onFormSubmit); // Ensure that calling validate() on the form will trigger a submit event
    // See https://developer.mozilla.org/en-US/docs/Web/API/HTMLFormElement/submit_event

    $('form.js-multi-validator-ajax').each(function (i, elem) {
      /* eslint-disable-next-line no-param-reassign */
      elem.submitWithoutEvent = elem.submit;
      /* eslint-disable-next-line no-param-reassign */

      elem.submit = function submit() {
        var event;

        if (typeof Event === 'function') {
          event = new Event('submit', {
            bubbles: true,
            cancelable: true
          });
        } else {
          event = document.createEvent('Event');
          event.initEvent('submit', true, true);
        } // We can't know what button was used to submit the form but we can guess.


        event.submitter = $(elem).find('button[type="submit"], input[type="submit"]').get(0);
        elem.dispatchEvent(event);
      };
    });
    return;
  }

  $.entwine('ss', function (jquery) {
    /**
     * Use entwine to bind form submit handler.
     */
    jquery('form.js-multi-validator-ajax').entwine({
      onsubmit: function onsubmit(event, button) {
        return onFormSubmit(event, button, this, jquery);
      }
    });
  });
})(jQuery);
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,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QixFQUFpQztBQUMvQjFDLFVBQUFBLEtBQUssR0FBRyxJQUFJMEMsS0FBSixDQUFVLFFBQVYsRUFBb0I7QUFBRUMsWUFBQUEsT0FBTyxFQUFFLElBQVg7QUFBaUJDLFlBQUFBLFVBQVUsRUFBRTtBQUE3QixXQUFwQixDQUFSO0FBQ0QsU0FGRCxNQUVPO0FBQ0w1QyxVQUFBQSxLQUFLLEdBQUc2QyxRQUFRLENBQUNDLFdBQVQsQ0FBcUIsT0FBckIsQ0FBUjtBQUNBOUMsVUFBQUEsS0FBSyxDQUFDK0MsU0FBTixDQUFnQixRQUFoQixFQUEwQixJQUExQixFQUFnQyxJQUFoQztBQUNELFNBUDZCLENBUTlCOzs7QUFDQS9DLFFBQUFBLEtBQUssQ0FBQ2EsU0FBTixHQUFrQm5GLENBQUMsQ0FBQ21FLElBQUQsQ0FBRCxDQUFRNUIsSUFBUixDQUFhLDZDQUFiLEVBQTREcUMsR0FBNUQsQ0FBZ0UsQ0FBaEUsQ0FBbEI7QUFDQVQsUUFBQUEsSUFBSSxDQUFDbUQsYUFBTCxDQUFtQmhELEtBQW5CO0FBQ0QsT0FYRDtBQVlELEtBaEJEO0FBaUJBO0FBQ0Q7O0FBRUR0RSxFQUFBQSxDQUFDLENBQUNHLE9BQUYsQ0FBVSxJQUFWLEVBQWdCLFVBQUM0RSxNQUFELEVBQVk7QUFDMUI7QUFDSjtBQUNBO0FBQ0lBLElBQUFBLE1BQU0sQ0FBQyw4QkFBRCxDQUFOLENBQXVDNUUsT0FBdkMsQ0FBK0M7QUFDN0NvSCxNQUFBQSxRQUQ2QyxvQkFDcENqRCxLQURvQyxFQUM3QkMsTUFENkIsRUFDckI7QUFDdEIsZUFBT08sWUFBWSxDQUFDUixLQUFELEVBQVFDLE1BQVIsRUFBZ0IsSUFBaEIsRUFBc0JRLE1BQXRCLENBQW5CO0FBQ0Q7QUFINEMsS0FBL0M7QUFLRCxHQVREO0FBVUQsQ0EzVEEsRUEyVEN5QyxNQTNURCxDQUFELEMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9zaWduaWZ5LWNvbXBvc2FibGUtdmFsaWRhdG9ycy8uL2NsaWVudC9zcmMvanMvQWpheENvbXBvc2l0ZVZhbGlkYXRvci5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKiBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgZnVuYy1uYW1lcyAqL1xuKGZ1bmN0aW9uICgkKSB7XG4gIGZ1bmN0aW9uIGlzQmFja2VuZEZvcm0oKSB7XG4gICAgcmV0dXJuICQuaXNGdW5jdGlvbigkLmVudHdpbmUpO1xuICB9XG5cbiAgZnVuY3Rpb24gZXNjYXBlSHRtbCh0ZXh0KSB7XG4gICAgcmV0dXJuICQoJzxkaXYvPicpLnRleHQodGV4dCkuaHRtbCgpO1xuICB9XG5cbiAgZnVuY3Rpb24gY29udmVydE5ld0xpbmVUb0JSKHRleHQpIHtcbiAgICByZXR1cm4gdGV4dC5yZXBsYWNlKC9cXG4vZywgJzxicj4nKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBSZXR1cm5zIHRoZSBzcy5pMThuIHN1YnN0aXR1dGlvbiBvciB0aGUgZGVmYXVsdCBzdHJpbmcuXG4gICAqIEBwYXJhbSB7c3RyaW5nfSB0cmFuc2xhdGlvbktleVxuICAgKiBAcGFyYW0ge3N0cmluZ30gZGVmYXVsdFN0clxuICAgKiBAcGFyYW0ge29iamVjdH0gc3Vic3RpdHV0aW9uc1xuICAgKiBAcmV0dXJuc1xuICAgKi9cbiAgZnVuY3Rpb24gcHJvdmlkZVRyYW5zbGF0aW9uT3JEZWZhdWx0KHRyYW5zbGF0aW9uS2V5LCBkZWZhdWx0U3RyLCBzdWJzdGl0dXRpb25zID0gbnVsbCkge1xuICAgIC8qIGVzbGludC1kaXNhYmxlIG5vLXVuZGVmLCBuby11bmRlcnNjb3JlLWRhbmdsZSAqL1xuICAgIGlmICh0eXBlb2Ygc3MgIT09ICd1bmRlZmluZWQnICYmIHR5cGVvZiBzcy5pMThuICE9PSAndW5kZWZpbmVkJykge1xuICAgICAgcmV0dXJuIHNzLmkxOG4uaW5qZWN0KHNzLmkxOG4uX3QodHJhbnNsYXRpb25LZXksIGRlZmF1bHRTdHIpLCBzdWJzdGl0dXRpb25zKTtcbiAgICB9XG4gICAgaWYgKHN1YnN0aXR1dGlvbnMpIHtcbiAgICAgIGNvbnN0IHJlZ2V4ID0gbmV3IFJlZ0V4cCgneyhbQS1aYS16MC05X10qKX0nLCAnZycpO1xuICAgICAgLyogZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLXBhcmFtLXJlYXNzaWduICovXG4gICAgICBkZWZhdWx0U3RyID0gZGVmYXVsdFN0ci5yZXBsYWNlKHJlZ2V4LCAobWF0Y2gsIGtleSkgPT4gKFxuICAgICAgICAoc3Vic3RpdHV0aW9uc1trZXldKSA/IHN1YnN0aXR1dGlvbnNba2V5XSA6IG1hdGNoXG4gICAgICApKTtcbiAgICB9XG4gICAgcmV0dXJuIGRlZmF1bHRTdHI7XG4gICAgLyogZXNsaW50LWVuYWJsZSBuby11bmRlZiwgbm8tdW5kZXJzY29yZS1kYW5nbGUgKi9cbiAgfVxuXG4gIC8qKlxuICAgKiBTZXRzIGEgbWVzc2FnZSBpbiB0aGUgdG9wIHJpZ2h0IG9mIHRoZSBDTVMuXG4gICAqIEBwYXJhbSB7c3RyaW5nfSB0ZXh0XG4gICAqIEBwYXJhbSB7c3RyaW5nfSB0eXBlXG4gICAqL1xuICBmdW5jdGlvbiBzdGF0dXNNZXNzYWdlKHRleHQsIHR5cGUsICRmb3JtID0gbnVsbCkge1xuICAgIC8vIEVzY2FwZSBIVE1MIGVudGl0aWVzIGluIHRleHRcbiAgICBjb25zdCBzYWZlVGV4dCA9IGVzY2FwZUh0bWwodGV4dCk7XG4gICAgaWYgKCQuaXNGdW5jdGlvbigkLm5vdGljZUFkZCkpIHtcbiAgICAgICQubm90aWNlQWRkKHtcbiAgICAgICAgdGV4dDogc2FmZVRleHQsXG4gICAgICAgIHR5cGUsXG4gICAgICAgIHN0YXlUaW1lOiA1MDAwLFxuICAgICAgICBpbkVmZmVjdDoge1xuICAgICAgICAgIGxlZnQ6ICcwJyxcbiAgICAgICAgICBvcGFjaXR5OiAnc2hvdycsXG4gICAgICAgIH0sXG4gICAgICB9KTtcbiAgICB9XG4gICAgaWYgKCFpc0JhY2tlbmRGb3JtKCkgJiYgJGZvcm0gIT0gbnVsbCkge1xuICAgICAgJCgnYm9keSxodG1sJykuYW5pbWF0ZShcbiAgICAgICAge1xuICAgICAgICAgIHNjcm9sbFRvcDogJGZvcm0ub2Zmc2V0KCkudG9wLFxuICAgICAgICB9LFxuICAgICAgICA1MDAsXG4gICAgICApO1xuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBTZXQgKG9yIGNsZWFyKSB0aGUgZm9ybSdzIGVycm9yIG1lc3NhZ2UuXG4gICAqIEBwYXJhbSB7alF1ZXJ5U3VifSAkZm9ybVxuICAgKiBAcGFyYW0ge3N0cmluZ30gbXNnXG4gICAqL1xuICBmdW5jdGlvbiBzZXRGb3JtRXJyb3JNc2coJGZvcm0sIG1zZykge1xuICAgIGNvbnN0IGlkUHJlZml4ID0gYCR7JGZvcm0uYXR0cignaWQnKX1fYDtcbiAgICBjb25zdCAkZWxlbSA9ICRmb3JtLmZpbmQoYCMke2lkUHJlZml4fWVycm9yYCk7XG4gICAgJGVsZW0udGV4dChtc2cpO1xuICAgIGlmIChtc2cpIHtcbiAgICAgICRlbGVtLmFkZENsYXNzKCd2YWxpZGF0aW9uIHZhbGlkYXRpb24tYmFyJyk7XG4gICAgICAkZWxlbS5zaG93KCk7XG4gICAgfSBlbHNlIHtcbiAgICAgICRlbGVtLnJlbW92ZUNsYXNzKCd2YWxpZGF0aW9uJyk7XG4gICAgICAkZWxlbS5oaWRlKCk7XG4gICAgfVxuICB9XG5cbiAgLyoqXG4gICAqIEFkZCB2YWxpZGF0aW9uIGVycm9yIG1lc3NhZ2UgZWxlbWVudHMgZm9yIGVhY2ggZmllbGQgd2hpY2ggZmFpbGVkIHZhbGlkYXRpb24uXG4gICAqIEBwYXJhbSB7YXJyYXl9IGRhdGFcbiAgICogQHBhcmFtIHtqUXVlcnlTdWJ9ICRmb3JtXG4gICAqL1xuICBmdW5jdGlvbiBkaXNwbGF5VmFsaWRhdGlvbkVycm9ycyhkYXRhLCAkZm9ybSkge1xuICAgICRmb3JtLmFkZENsYXNzKCd2YWxpZGF0aW9uZXJyb3InKTtcbiAgICBjb25zdCBtc2cgPSBwcm92aWRlVHJhbnNsYXRpb25PckRlZmF1bHQoXG4gICAgICAnU2lnbmlmeV9BamF4Q29tcG9zaXRlVmFsaWRhdG9yLlZBTElEQVRJT05fRVJST1JTJyxcbiAgICAgICdUaGVyZSBhcmUgdmFsaWRhdGlvbiBlcnJvcnMgb24gdGhpcyBmb3JtLCBwbGVhc2UgZml4IHRoZW0gYmVmb3JlIHNhdmluZyBvciBwdWJsaXNoaW5nLicsXG4gICAgKTtcbiAgICBzZXRGb3JtRXJyb3JNc2coJGZvcm0sIG1zZyk7XG4gICAgY29uc3QgaWRQcmVmaXggPSBgJHskZm9ybS5hdHRyKCdpZCcpfV9gO1xuICAgIGNvbnN0IGhvbGRlclN1ZmZpeCA9ICdfSG9sZGVyJztcblxuICAgIGZvciAobGV0IGkgPSAwOyBpIDwgZGF0YS5sZW5ndGg7IGkgKz0gMSkge1xuICAgICAgY29uc3QgZXJyb3IgPSBkYXRhW2ldO1xuICAgICAgLy8gRXJyb3JzIGNhbiBiZSBmb3IgdGhlIGZvcm0gaW4gZ2VuZXJhbC5cbiAgICAgIGlmICghZXJyb3IuZmllbGROYW1lKSB7XG4gICAgICAgIGNvbnN0ICRtZXNzYWdlID0gJCgnPGRpdi8+JykuaHRtbChjb252ZXJ0TmV3TGluZVRvQlIoZXJyb3IubWVzc2FnZSkpXG4gICAgICAgICAgLmFkZENsYXNzKGBqcy1hamF4LXZhbGlkYXRpb24gbWVzc2FnZSAke2Vycm9yLm1lc3NhZ2VUeXBlfWApO1xuICAgICAgICAkbWVzc2FnZS5pbnNlcnRBZnRlcigkZm9ybS5maW5kKGAjJHtpZFByZWZpeH1lcnJvcmApKTtcbiAgICAgICAgLyogZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLWNvbnRpbnVlICovXG4gICAgICAgIGNvbnRpbnVlO1xuICAgICAgfVxuICAgICAgLy8gR2V0IHRoZSBmaWVsZCBiZWZvcmUgd2hpY2ggdG8gaW5zZXJ0IHRoZSB2YWxpZGF0aW9uIGVycm9yIG1lc3NhZ2UuXG4gICAgICBjb25zdCBpZCA9IGAke2lkUHJlZml4fSR7ZXJyb3IuZmllbGROYW1lLnJlcGxhY2UobmV3IFJlZ0V4cCgvX3syLH0vZyksICdfJyl9YDtcbiAgICAgIGNvbnN0ICRob2xkZXIgPSAkKGAjJHtpZH0ke2hvbGRlclN1ZmZpeH1gKTtcbiAgICAgIGxldCAkZmllbGQgPSBudWxsO1xuICAgICAgaWYgKGlzQmFja2VuZEZvcm0oKSAmJiAkaG9sZGVyLmxlbmd0aCkge1xuICAgICAgICAkZmllbGQgPSAkaG9sZGVyO1xuICAgICAgfSBlbHNlIHtcbiAgICAgICAgJGZpZWxkID0gJChgIyR7aWR9YCk7XG4gICAgICB9XG4gICAgICAvLyBBZGQgaW5kaWNhdG9yIGZvciB3aGljaCB0YWIgaGFzIGFuIGVycm9yLlxuICAgICAgY29uc3QgdGFiSUQgPSAkZmllbGQucGFyZW50cygnLnRhYi1wYW5lJykuYXR0cignYXJpYS1sYWJlbGxlZGJ5Jyk7XG4gICAgICAkKGAjJHt0YWJJRH1gKS5hZGRDbGFzcyhgZm9udC1pY29uLWF0dGVudGlvbi0xIHRhYi12YWxpZGF0aW9uIHRhYi12YWxpZGF0aW9uLS0ke2Vycm9yLm1lc3NhZ2VUeXBlfWApO1xuICAgICAgLy8gQ3JlYXRlIGFuZCBpbnNlcnQgdGhlIHZhbGlkYXRpb24gZXJyb3IgbWVzc2FnZSBlbGVtZW50LlxuICAgICAgY29uc3QgJG1lc3NhZ2UgPSAkKCc8ZGl2Lz4nKS5odG1sKGNvbnZlcnROZXdMaW5lVG9CUihlcnJvci5tZXNzYWdlKSlcbiAgICAgICAgLmFkZENsYXNzKGBqcy1hamF4LXZhbGlkYXRpb24gbWVzc2FnZSAke2Vycm9yLm1lc3NhZ2VUeXBlfWApO1xuICAgICAgaWYgKGlzQmFja2VuZEZvcm0oKSkge1xuICAgICAgICAkbWVzc2FnZS5pbnNlcnRCZWZvcmUoJGZpZWxkKTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgICRmaWVsZC5hZGRDbGFzcygnaG9sZGVyLXJlcXVpcmVkJyk7XG4gICAgICAgICRtZXNzYWdlLmFkZENsYXNzKCdmb3JtX19tZXNzYWdlIGZvcm1fX21lc3NhZ2UtLXJlcXVpcmVkJyk7XG4gICAgICAgICRtZXNzYWdlLmluc2VydEFmdGVyKCRmaWVsZCk7XG4gICAgICB9XG4gICAgfVxuICAgIGNvbnN0IHN0YXR1c01zZyA9IHByb3ZpZGVUcmFuc2xhdGlvbk9yRGVmYXVsdChcbiAgICAgICdTaWduaWZ5X0FqYXhDb21wb3NpdGVWYWxpZGF0b3IuVkFMSURBVElPTl9FUlJPUl9UT0FTVCcsXG4gICAgICAnVmFsaWRhdGlvbiBFcnJvcicsXG4gICAgKTtcbiAgICBzdGF0dXNNZXNzYWdlKHN0YXR1c01zZywgJ2Vycm9yJywgJGZvcm0pO1xuICB9XG5cbiAgLyoqXG4gICAqIENsZWFyIGFsbCBwcmV2aW91cyB2YWxpZGF0aW9uIGVycm9yIG1lc3NhZ2VzLlxuICAgKiBAcGFyYW0ge2pRdWVyeVN1Yn0gJGZvcm1cbiAgICovXG4gIGZ1bmN0aW9uIGNsZWFyVmFsaWRhdGlvbigkZm9ybSkge1xuICAgIHNldEZvcm1FcnJvck1zZygkZm9ybSwgJycpO1xuICAgICRmb3JtLnJlbW92ZUNsYXNzKCd2YWxpZGF0aW9uZXJyb3InKTtcbiAgICAkZm9ybS5maW5kKCcuaG9sZGVyLXJlcXVpcmVkJykucmVtb3ZlQ2xhc3MoJ2hvbGRlci1yZXF1aXJlZCcpO1xuICAgICRmb3JtLmZpbmQoJy5qcy1hamF4LXZhbGlkYXRpb24nKS5yZW1vdmUoKTtcbiAgICBjb25zdCAkdGFicyA9ICRmb3JtLmZpbmQoJ2EudWktdGFicy1hbmNob3InKTtcbiAgICAkdGFicy5lYWNoKChpbmRleCwgZWxlbSkgPT4ge1xuICAgICAgJChlbGVtKS5yZW1vdmVDbGFzcygnZm9udC1pY29uLWF0dGVudGlvbi0xIHRhYi12YWxpZGF0aW9uJyk7XG4gICAgICAvKiBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tcGFyYW0tcmVhc3NpZ24gKi9cbiAgICAgIGVsZW0uY2xhc3NOYW1lID0gZWxlbS5jbGFzc05hbWUucmVwbGFjZShuZXcgUmVnRXhwKC90YWItdmFsaWRhdGlvbi0tXFx3Ki9nLCAnJykpO1xuICAgIH0pO1xuICB9XG5cbiAgZnVuY3Rpb24gZmluYWxseVN1Ym1pdCgkZm9ybSwgZXZlbnQsIGJ1dHRvbikge1xuICAgIGlmIChpc0JhY2tlbmRGb3JtKCkpIHtcbiAgICAgIC8vIElmIHdlJ3JlIGluIHRoZSBDTVMgYW5kIHdlJ3ZlIGJlZW4gcHJvdmlkZWQgYSBidXR0b24gYWN0aW9uLCB3ZSBuZWVkIHRvIHRlbGwgdGhlXG4gICAgICAvLyBjb250YWluZXIgdG8gc3VibWl0IHRoZSBmb3JtLiBUaGlzIGVuc3VyZXMgdGhhdCB0aGUgY29ycmVjdCBzZXF1ZW5jZSBvZiBldmVudHMgb2NjdXJzLlxuICAgICAgLy8gUmVseWluZyBvbiBidWJibGluZyB0aGlzIGV2ZW50IGNhbiByZXN1bHQgaW4gZXJyb3JzLlxuICAgICAgaWYgKGJ1dHRvbikge1xuICAgICAgICBjb25zdCBjbXNDb250YWluZXIgPSAkZm9ybS5jbG9zZXN0KCcuY21zLWNvbnRhaW5lcicpO1xuICAgICAgICBpZiAoY21zQ29udGFpbmVyLmxlbmd0aCkge1xuICAgICAgICAgIGNtc0NvbnRhaW5lci5zdWJtaXRGb3JtKCRmb3JtLCBidXR0b24pO1xuICAgICAgICAgIGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICAgIC8vIEknbSBob25lc3RseSBub3Qgc3VyZSBieSB3aGF0IG1hZ2ljIGl0IGhhcHBlbnMgYnV0IHRoZSBmb3JtIHN1Ym1pdHMgY29ycmVjdGx5IG9uIHRoZVxuICAgICAgLy8gYmFja2VuZC5cbiAgICB9IGVsc2Uge1xuICAgICAgLy8gT24gdGhlIGZyb250LWVuZCB3ZSBoYXZlIHRvIG1ha2UgdGhlIGZvcm0gc3VibWl0LlxuICAgICAgJGZvcm0uZ2V0KDApLnN1Ym1pdFdpdGhvdXRFdmVudCgpO1xuICAgIH1cbiAgfVxuXG4gIC8qKlxuICAgKiBGb3JtIHN1Ym1pdCBoYW5kbGVyIGZvciBhamF4IHZhbGlkYXRpb24uXG4gICAqL1xuICBmdW5jdGlvbiBvbkZvcm1TdWJtaXQoZXZlbnQsIGJ1dHRvbiwgZW50d2luZSwganF1ZXJ5ID0gJCkge1xuICAgIC8vIERvbid0IGFsbG93IHRoZSBmb3JtIHRvIHN1Ym1pdCBvbiBpdHMgb3duIC0gYmVjYXVzZSB3ZSdyZSB1c2luZyBBSkFYIHdlIGhhdmUgdG8gZG8gdGhpbmdzXG4gICAgLy8gYXN5bmNyb25vdXNseSBhbmQgdW5mb3J0dW5hdGVseSBJIGNhbid0IGZpbmQgYSB3YXkgdG8gYXN5bmNyb25vdXNseSBwcmV2ZW50RGVmYXVsdC5cbiAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuICAgIGlmICghYnV0dG9uKSB7XG4gICAgICAvLyBHZXQgYnV0dG9uIHRoYXQgc3Bhd25lZCB0aGUgc3VibWl0IGV2ZW50LlxuICAgICAgY29uc3QgZGVsZWdhdGVkRXZlbnQgPSBldmVudC5kZWxlZ2F0ZWRFdmVudCA/PyBldmVudDtcbiAgICAgIGNvbnN0IG9yaWdpbmFsRXZlbnQgPSBkZWxlZ2F0ZWRFdmVudC5vcmlnaW5hbEV2ZW50ID8/IGRlbGVnYXRlZEV2ZW50O1xuICAgICAgY29uc3QgJGNsaWNrZWQgPSBqcXVlcnkob3JpZ2luYWxFdmVudC5zdWJtaXR0ZXIgPz8gb3JpZ2luYWxFdmVudC50YXJnZXQpO1xuICAgICAgaWYgKCRjbGlja2VkLmhhc0NsYXNzKCdlbGVtZW50LWVkaXRvcl9faG92ZXItYmFyLWFyZWEnKSkge1xuICAgICAgICAvLyBBZGQgZWxlbWVudGFsIGJsb2NrIGJ1dHRvbiBjbGlja2VkLiBEb24ndCB2YWxpZGF0ZSBvciBzdWJtaXQgZm9ybS5cbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgfVxuICAgICAgaWYgKCRjbGlja2VkLmF0dHIoJ25hbWUnKT8uc3RhcnRzV2l0aCgnYWN0aW9uXycpKSB7XG4gICAgICAgIC8vIFNldCBidXR0b24gaWYgdGhlIGNsaWNrZWQgYnV0dG9uIGlzIGEgdmFsaWQgRm9ybUFjdGlvbi5cbiAgICAgICAgLyogZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLXBhcmFtLXJlYXNzaWduICovXG4gICAgICAgIGJ1dHRvbiA9ICRjbGlja2VkLmdldCgwKTtcbiAgICAgIH1cbiAgICB9XG4gICAganF1ZXJ5KGJ1dHRvbikuYXR0cignZGlzYWJsZWQnLCB0cnVlKTtcbiAgICBjb25zdCAkZm9ybSA9IGVudHdpbmUgIT09IHVuZGVmaW5lZCA/IGVudHdpbmUgOiBqcXVlcnkodGhpcyk7XG4gICAgJGZvcm0uYWRkQ2xhc3MoJ2pzLXZhbGlkYXRpbmcnKTtcbiAgICBjbGVhclZhbGlkYXRpb24oJGZvcm0pO1xuXG4gICAgLy8gUGVyZm9ybSB0aGVzZSBhY3Rpb25zIGlmIHRoZSB2YWxpZGF0aW9uIFBPU1QgcmVxdWVzdCBpcyBzdWNjZXNzZnVsLlxuICAgIGZ1bmN0aW9uIHN1Y2Nlc3NGbihkYXRhKSB7XG4gICAgICBsZXQgaGFzRXJyb3JzID0gZmFsc2U7XG4gICAgICBpZiAoZGF0YSAhPT0gdHJ1ZSkge1xuICAgICAgICBpZiAoZGF0YS5sZW5ndGgpIHtcbiAgICAgICAgICBoYXNFcnJvcnMgPSB0cnVlO1xuICAgICAgICB9XG4gICAgICB9XG5cbiAgICAgIC8vIENvbmZpcm0gcmVjYXB0Y2hhIHYyIGlzIGNvbXBsZXRlZCBpZiBwcmVzZW50LlxuICAgICAgY29uc3QgJHJlY2FwdGNoYUZpZWxkID0gJGZvcm0uZmluZCgnZGl2LmctcmVjYXB0Y2hhJyk7XG4gICAgICBpZiAoJHJlY2FwdGNoYUZpZWxkLmxlbmd0aCA+IDAgJiYgdHlwZW9mIGdyZWNhcHRjaGEgPT09ICdvYmplY3QnKSB7XG4gICAgICAgIGNvbnN0IHdpZGdldElkID0gJHJlY2FwdGNoYUZpZWxkLmRhdGEoJ3dpZGdldGlkJyk7XG4gICAgICAgIC8vIElmIHRoZXJlJ3Mgbm8gd2lkZ2V0SWQgdGhpcyBpcyBwcm9iYWJseSByZWNhcHRjaGEgdjMuXG4gICAgICAgIGlmICh3aWRnZXRJZCAhPT0gbnVsbCAmJiB3aWRnZXRJZCAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgLy8gSWYgdGhlcmUncyBubyByZXNwb25zZSwgdGhlIHVzZXIgaGFzbid0IGNvbXBsZXRlZCB0aGUgY2FwdGNoYS5cbiAgICAgICAgICAvKiBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tdW5kZWYgKi9cbiAgICAgICAgICBpZiAoIWdyZWNhcHRjaGEuZ2V0UmVzcG9uc2Uod2lkZ2V0SWQpKSB7XG4gICAgICAgICAgICBpZiAoZGF0YSA9PT0gdHJ1ZSkge1xuICAgICAgICAgICAgICAvKiBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tcGFyYW0tcmVhc3NpZ24gKi9cbiAgICAgICAgICAgICAgZGF0YSA9IFtdO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBjb25zdCBjYXB0Y2hhTXNnID0gcHJvdmlkZVRyYW5zbGF0aW9uT3JEZWZhdWx0KFxuICAgICAgICAgICAgICAnU2lnbmlmeV9BamF4Q29tcG9zaXRlVmFsaWRhdG9yLkNBUFRDSEFfVkFMSURBVElPTl9FUlJPUicsXG4gICAgICAgICAgICAgICdQbGVhc2UgYW5zd2VyIHRoZSBjYXB0Y2hhLicsXG4gICAgICAgICAgICApO1xuICAgICAgICAgICAgZGF0YS5wdXNoKHtcbiAgICAgICAgICAgICAgZmllbGROYW1lOiBudWxsLFxuICAgICAgICAgICAgICBtZXNzYWdlOiBjYXB0Y2hhTXNnLFxuICAgICAgICAgICAgICBtZXNzYWdlVHlwZTogJ3JlcXVpcmVkJyxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgaGFzRXJyb3JzID0gdHJ1ZTtcbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgIH1cblxuICAgICAgLy8gRmluaXNoIHZhbGlkYXRpb24sIGRpc3BsYXkgZXJyb3JzIG9yIHN1Ym1pdCBmb3JtLlxuICAgICAgJGZvcm0ucmVtb3ZlQ2xhc3MoJ2pzLXZhbGlkYXRpbmcnKTtcbiAgICAgIGlmIChoYXNFcnJvcnMpIHtcbiAgICAgICAgZGlzcGxheVZhbGlkYXRpb25FcnJvcnMoZGF0YSwgJGZvcm0pO1xuICAgICAgICBqcXVlcnkoYnV0dG9uKS5hdHRyKCdkaXNhYmxlZCcsIGZhbHNlKTtcbiAgICAgICAganF1ZXJ5KGJ1dHRvbikucmVtb3ZlQ2xhc3MoJ2xvYWRpbmcnKTtcbiAgICAgICAgLy8gRG9uJ3Qgc3VibWl0IHRoZSBmb3JtIGlmIHRoZXJlIGFyZSBlcnJvcnMuXG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgIH1cbiAgICAgIHJldHVybiBmaW5hbGx5U3VibWl0KCRmb3JtLCBldmVudCwgYnV0dG9uLCBlbnR3aW5lKTtcbiAgICB9XG5cbiAgICAvLyBQZXJmb3JtIHRoZXNlIGFjdGlvbnMgaWYgdGhlcmUgaXMgYW4gZXJyb3IgaW4gdGhlIHZhbGlkYXRpb24gUE9TVCByZXF1ZXN0LlxuICAgIGZ1bmN0aW9uIGVycm9yRm4ocmVxdWVzdCwgc3RhdHVzLCBlcnJvcikge1xuICAgICAgY29uc3QgY2Fubm90VmFsaWRhdGVNc2cgPSBwcm92aWRlVHJhbnNsYXRpb25PckRlZmF1bHQoXG4gICAgICAgICdTaWduaWZ5X0FqYXhDb21wb3NpdGVWYWxpZGF0b3IuQ0FOTk9UX1ZBTElEQVRFJyxcbiAgICAgICAgJ0NvdWxkIG5vdCB2YWxpZGF0ZS4gQWJvcnRpbmcgQUpBWCB2YWxpZGF0aW9uLicsXG4gICAgICApO1xuICAgICAgc3RhdHVzTWVzc2FnZShjYW5ub3RWYWxpZGF0ZU1zZywgJ2Vycm9yJyk7XG4gICAgICAvKiBlc2xpbnQtZGlzYWJsZS1uZXh0LWxpbmUgbm8tY29uc29sZSAqL1xuICAgICAgY29uc29sZS5lcnJvcihgRXJyb3Igd2l0aCBBSkFYIHZhbGlkYXRpb24gcmVxdWVzdDogJHtzdGF0dXN9OiAke2Vycm9yfWApO1xuICAgIH1cblxuICAgIC8vIFZhbGlkYXRlLlxuICAgIGNvbnN0IHZhbGlkYXRlVXJsID0gJGZvcm0uZGF0YSgndmFsaWRhdGlvbi1saW5rJyk7XG4gICAgY29uc3Qgc2VyaWFsaXNlZCA9ICRmb3JtLnNlcmlhbGl6ZUFycmF5KCk7XG4gICAgc2VyaWFsaXNlZC5wdXNoKHsgbmFtZTogJ2FjdGlvbl9hcHBfYWpheFZhbGlkYXRlJywgdmFsdWU6ICcxJyB9KTtcbiAgICBpZiAoYnV0dG9uKSB7XG4gICAgICBzZXJpYWxpc2VkLnB1c2goeyBuYW1lOiAnX29yaWdpbmFsX2FjdGlvbicsIHZhbHVlOiBidXR0b24uZ2V0QXR0cmlidXRlKCduYW1lJykgfSk7XG4gICAgfVxuICAgIGpxdWVyeS5hamF4KHtcbiAgICAgIHR5cGU6ICdQT1NUJyxcbiAgICAgIHVybDogdmFsaWRhdGVVcmwsXG4gICAgICBkYXRhOiBzZXJpYWxpc2VkLFxuICAgICAgc3VjY2Vzczogc3VjY2Vzc0ZuLFxuICAgICAgZXJyb3I6IGVycm9yRm4sXG4gICAgfSk7XG5cbiAgICByZXR1cm4gZmFsc2U7XG4gIH1cblxuICBpZiAoIWlzQmFja2VuZEZvcm0oKSkge1xuICAgIC8vIFRoaXMgaXMgYSBmcm9udCBlbmQgZm9ybS4gRW50d2luZSB3b24ndCB3b3JrIGJ1dCBpc24ndCBuZWVkZWQuXG4gICAgJCgnZm9ybS5qcy1tdWx0aS12YWxpZGF0b3ItYWpheCcpLm9uKCdzdWJtaXQnLCBvbkZvcm1TdWJtaXQpO1xuICAgIC8vIEVuc3VyZSB0aGF0IGNhbGxpbmcgdmFsaWRhdGUoKSBvbiB0aGUgZm9ybSB3aWxsIHRyaWdnZXIgYSBzdWJtaXQgZXZlbnRcbiAgICAvLyBTZWUgaHR0cHM6Ly9kZXZlbG9wZXIubW96aWxsYS5vcmcvZW4tVVMvZG9jcy9XZWIvQVBJL0hUTUxGb3JtRWxlbWVudC9zdWJtaXRfZXZlbnRcbiAgICAkKCdmb3JtLmpzLW11bHRpLXZhbGlkYXRvci1hamF4JykuZWFjaCgoaSwgZWxlbSkgPT4ge1xuICAgICAgLyogZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLXBhcmFtLXJlYXNzaWduICovXG4gICAgICBlbGVtLnN1Ym1pdFdpdGhvdXRFdmVudCA9IGVsZW0uc3VibWl0O1xuICAgICAgLyogZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLXBhcmFtLXJlYXNzaWduICovXG4gICAgICBlbGVtLnN1Ym1pdCA9IGZ1bmN0aW9uIHN1Ym1pdCgpIHtcbiAgICAgICAgbGV0IGV2ZW50O1xuICAgICAgICBpZiAodHlwZW9mIEV2ZW50ID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgZXZlbnQgPSBuZXcgRXZlbnQoJ3N1Ym1pdCcsIHsgYnViYmxlczogdHJ1ZSwgY2FuY2VsYWJsZTogdHJ1ZSB9KTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICBldmVudCA9IGRvY3VtZW50LmNyZWF0ZUV2ZW50KCdFdmVudCcpO1xuICAgICAgICAgIGV2ZW50LmluaXRFdmVudCgnc3VibWl0JywgdHJ1ZSwgdHJ1ZSk7XG4gICAgICAgIH1cbiAgICAgICAgLy8gV2UgY2FuJ3Qga25vdyB3aGF0IGJ1dHRvbiB3YXMgdXNlZCB0byBzdWJtaXQgdGhlIGZvcm0gYnV0IHdlIGNhbiBndWVzcy5cbiAgICAgICAgZXZlbnQuc3VibWl0dGVyID0gJChlbGVtKS5maW5kKCdidXR0b25bdHlwZT1cInN1Ym1pdFwiXSwgaW5wdXRbdHlwZT1cInN1Ym1pdFwiXScpLmdldCgwKTtcbiAgICAgICAgZWxlbS5kaXNwYXRjaEV2ZW50KGV2ZW50KTtcbiAgICAgIH07XG4gICAgfSk7XG4gICAgcmV0dXJuO1xuICB9XG5cbiAgJC5lbnR3aW5lKCdzcycsIChqcXVlcnkpID0+IHtcbiAgICAvKipcbiAgICAgKiBVc2UgZW50d2luZSB0byBiaW5kIGZvcm0gc3VibWl0IGhhbmRsZXIuXG4gICAgICovXG4gICAganF1ZXJ5KCdmb3JtLmpzLW11bHRpLXZhbGlkYXRvci1hamF4JykuZW50d2luZSh7XG4gICAgICBvbnN1Ym1pdChldmVudCwgYnV0dG9uKSB7XG4gICAgICAgIHJldHVybiBvbkZvcm1TdWJtaXQoZXZlbnQsIGJ1dHRvbiwgdGhpcywganF1ZXJ5KTtcbiAgICAgIH0sXG4gICAgfSk7XG4gIH0pO1xufShqUXVlcnkpKTtcbiJdLCJuYW1lcyI6WyIkIiwiaXNCYWNrZW5kRm9ybSIsImlzRnVuY3Rpb24iLCJlbnR3aW5lIiwiZXNjYXBlSHRtbCIsInRleHQiLCJodG1sIiwiY29udmVydE5ld0xpbmVUb0JSIiwicmVwbGFjZSIsInByb3ZpZGVUcmFuc2xhdGlvbk9yRGVmYXVsdCIsInRyYW5zbGF0aW9uS2V5IiwiZGVmYXVsdFN0ciIsInN1YnN0aXR1dGlvbnMiLCJzcyIsImkxOG4iLCJpbmplY3QiLCJfdCIsInJlZ2V4IiwiUmVnRXhwIiwibWF0Y2giLCJrZXkiLCJzdGF0dXNNZXNzYWdlIiwidHlwZSIsIiRmb3JtIiwic2FmZVRleHQiLCJub3RpY2VBZGQiLCJzdGF5VGltZSIsImluRWZmZWN0IiwibGVmdCIsIm9wYWNpdHkiLCJhbmltYXRlIiwic2Nyb2xsVG9wIiwib2Zmc2V0IiwidG9wIiwic2V0Rm9ybUVycm9yTXNnIiwibXNnIiwiaWRQcmVmaXgiLCJhdHRyIiwiJGVsZW0iLCJmaW5kIiwiYWRkQ2xhc3MiLCJzaG93IiwicmVtb3ZlQ2xhc3MiLCJoaWRlIiwiZGlzcGxheVZhbGlkYXRpb25FcnJvcnMiLCJkYXRhIiwiaG9sZGVyU3VmZml4IiwiaSIsImxlbmd0aCIsImVycm9yIiwiZmllbGROYW1lIiwiJG1lc3NhZ2UiLCJtZXNzYWdlIiwibWVzc2FnZVR5cGUiLCJpbnNlcnRBZnRlciIsImlkIiwiJGhvbGRlciIsIiRmaWVsZCIsInRhYklEIiwicGFyZW50cyIsImluc2VydEJlZm9yZSIsInN0YXR1c01zZyIsImNsZWFyVmFsaWRhdGlvbiIsInJlbW92ZSIsIiR0YWJzIiwiZWFjaCIsImluZGV4IiwiZWxlbSIsImNsYXNzTmFtZSIsImZpbmFsbHlTdWJtaXQiLCJldmVudCIsImJ1dHRvbiIsImNtc0NvbnRhaW5lciIsImNsb3Nlc3QiLCJzdWJtaXRGb3JtIiwicHJldmVudERlZmF1bHQiLCJnZXQiLCJzdWJtaXRXaXRob3V0RXZlbnQiLCJvbkZvcm1TdWJtaXQiLCJqcXVlcnkiLCJkZWxlZ2F0ZWRFdmVudCIsIm9yaWdpbmFsRXZlbnQiLCIkY2xpY2tlZCIsInN1Ym1pdHRlciIsInRhcmdldCIsImhhc0NsYXNzIiwic3RhcnRzV2l0aCIsInVuZGVmaW5lZCIsInN1Y2Nlc3NGbiIsImhhc0Vycm9ycyIsIiRyZWNhcHRjaGFGaWVsZCIsImdyZWNhcHRjaGEiLCJ3aWRnZXRJZCIsImdldFJlc3BvbnNlIiwiY2FwdGNoYU1zZyIsInB1c2giLCJlcnJvckZuIiwicmVxdWVzdCIsInN0YXR1cyIsImNhbm5vdFZhbGlkYXRlTXNnIiwiY29uc29sZSIsInZhbGlkYXRlVXJsIiwic2VyaWFsaXNlZCIsInNlcmlhbGl6ZUFycmF5IiwibmFtZSIsInZhbHVlIiwiZ2V0QXR0cmlidXRlIiwiYWpheCIsInVybCIsInN1Y2Nlc3MiLCJvbiIsInN1Ym1pdCIsIkV2ZW50IiwiYnViYmxlcyIsImNhbmNlbGFibGUiLCJkb2N1bWVudCIsImNyZWF0ZUV2ZW50IiwiaW5pdEV2ZW50IiwiZGlzcGF0Y2hFdmVudCIsIm9uc3VibWl0IiwialF1ZXJ5Il0sInNvdXJjZVJvb3QiOiIifQ==