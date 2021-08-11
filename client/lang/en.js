/* eslint-disable no-undef */
if (typeof ss === 'undefined' || typeof ss.i18n === 'undefined') {
  // On the front-end for many applications the silverstripe/admin i18n script won't be loaded.
  return;
}
ss.i18n.addDictionary('en', {
  'Signify_AjaxCompositeValidator.CANNOT_VALIDATE': 'Could not validate. Aborting AJAX validation.',
  'Signify_AjaxCompositeValidator.CAPTCHA_VALIDATION_ERROR': 'Please answer the captcha.',
  'Signify_AjaxCompositeValidator.VALIDATION_ERROR_TOAST': 'Validation Error',
  'Signify_AjaxCompositeValidator.VALIDATION_ERRORS': 'There are validation errors on this form, please fix them before saving or publishing.',
  'ElementPublishAction.SUCCESS_NOTIFICATION': "Published '{title}' successfully",
});
