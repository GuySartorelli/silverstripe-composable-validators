# Silverstripe Composable Validators

Thank you to [Signify](https://github.com/signify-nz) for making this module possible.

This module provides a number of reusable composable validators (including AJAX validation) for use both in the CMS and in frontend forms.

Make sure you check out the [extensions documentation][0] at a minimum - some of these should be applied in almost all projects using this module.

If your project has any automated client-side tests, or you are implementing a validator to be compatible with this module, please make sure you read the [client side tests documentation][1].

## Install

Install via [composer][2]:

```bash
composer require guysartorelli/silverstripe-composable-validators
```

## Gotchas

### Form submission with an `AjaxCompositeValidator`

The `AjaxCompositeValidator` adds a submit handler to your form. This doesn't always interact well with other submit handlers, and can result in either front-end validation being skipped or the form not submitting the way you expect it to, depending on which submit handler gets the event first. For best results, don't add additional submit handlers to the form.

If you're using the `AjaxCompositeValidator` on a form that uses [undefinedoffset/silverstripe-nocaptcha][3] 2.3.0 or higher, you should disable form submission handling for the `NocaptchaField` in that form (see instructions in the nocaptcha docs).

## [Available Validators][4]

- **[`AjaxCompositeValidator`][5]**  
Subclass of [`CompositeValidator`][6] that provides AJAX validation. Resolves [an issue with losing data][7], faster turn-around for fixing validation problems, and provides a way to use the same validation for 'client-side' validation of frontend forms.
- **[`SimpleFieldsValidator`][8]**  
Ensures the internal validation of form fields by calling `validate()` on them.
- **[`RequiredFieldsValidator`][9]**  
Like Silverstripe's [`RequiredFields`][10] validator, but more convenient for use in a `CompositeValidator`.
- **[`WarningFieldsValidator`][11]**  
Displays a warning if some field(s) doesn't have a value. Useful for alerting users about data that is technically valid but may not provide the results they expect
- **[`DependentRequiredFieldsValidator`][12]**  
Uses [`SearchFilter`s][13] to define fields as required conditionally, based on the values of other fields (e.g. only required if `OtherField` has a value greater than 25).
- **[`RequiredBlocksValidator`][14]**  
Require a specific [elemental block(s)][15] to exist in the `ElementalArea`, with optional minimum and maximum numbers of blocks and optional positional validation.
- **[`RegexFieldsValidator`][16]**  
Ensure some field(s) matches a specified regex pattern.

### [Abstract Validators][17]

- **[`BaseValidator`][18]**  
Includes methods useful for getting the actual `FormField` and its label.
- **[`FieldHasValueValidator`][19]**  
Subclass of `BaseValidator`. Useful for validators that require logic to check if a field has any value or not.

## [Traits][20]

- **[`ValidatesMultipleFields`][21]**  
Useful for validators that can be fed an array of field names to be validated.
- **[`ValidatesMultipleFieldsWithConfig`][22]**  
Like `ValidatesMultipleFields` but requires a configuration array for each field to be validated.

[0]: docs/en/02-extensions.md
[1]: docs/en/03-client-side-tests.md
[2]: https://getcomposer.org
[3]: https://github.com/UndefinedOffset/silverstripe-nocaptcha
[4]: docs/en/01-validators.md
[5]: docs/en/01-validators.md#ajaxcompositevalidator
[6]: https://api.silverstripe.org/4/SilverStripe/Forms/CompositeValidator.html
[7]: https://github.com/silverstripe/silverstripe-elemental/issues/764
[8]: docs/en/01-validators.md#simplefieldsvalidator
[9]: docs/en/01-validators.md#requiredfieldsvalidator
[10]: https://api.silverstripe.org/4/SilverStripe/Forms/RequiredFields.html
[11]: docs/en/01-validators.md#warningfieldsvalidator
[12]: docs/en/01-validators.md#dependentrequiredfieldsvalidator
[13]: https://docs.silverstripe.org/en/4/developer_guides/model/searchfilters/
[14]: docs/en/01-validators.md#requiredblocksvalidator
[15]: https://github.com/silverstripe/silverstripe-elemental
[16]: docs/en/01-validators.md#regexfieldsvalidator
[17]: docs/en/01-validators.md#abstract-validators
[18]: docs/en/01-validators.md#basevalidator
[19]: docs/en/01-validators.md#fieldhasvaluevalidator
[20]: docs/en/01-validators.md#traits
[21]: docs/en/01-validators.md#validatesmultiplefields
[22]: docs/en/01-validators.md#validatesmultiplefieldswithconfig
