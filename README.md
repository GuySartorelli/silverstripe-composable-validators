# SilverStripe Composable Validators

Provides number of reusable composable validators (including AJAX validation) for use both in the CMS and in frontend forms.

Make sure you check out the [extensions documentation](docs/en/02-extensions.md) at a minimum - some of these should be applied in almost all projects using this module.

If your project has any automated client-side tests, or you are implementing a validator to be compatible with this module, please make sure you read the [client side tests documentation](docs/en/03-client-side-tests.md).

## Install

Install via [composer](https://getcomposer.org):

```bash
composer require signify-nz/silverstripe-composable-validators
```

## Gotchas
### Form submission with an `AjaxCompositeValidator`
The `AjaxCompositeValidator` adds a submit handler to your form. This doesn't always interact well with other submit handlers, and can result in either front-end validation being skipped or the form not submitting the way yo expect it to, depending on which submit handler gets the event first. For best results, don't add additional submit handlers to the form.

If you're using the `AjaxCompositeValidator` on a form that uses [undefinedoffset/silverstripe-nocaptcha](https://github.com/UndefinedOffset/silverstripe-nocaptcha) 2.3.0 or higher, you should disable form submission handling for the `NocaptchaField` in that form (see instructions in the nocaptcha docs).

## [Available Validators](docs/en/01-validators.md)
- **[AjaxCompositeValidator](docs/en/01-validators.md#ajaxcompositevalidator)**  
Subclass of [CompositeValidator](https://api.silverstripe.org/4/SilverStripe/Forms/CompositeValidator.html) that provides AJAX validation. Resolves [an issue with losing data](https://github.com/silverstripe/silverstripe-elemental/issues/764), faster turn-around for fixing validation problems, and provides a way to use the same validation for 'client-side' validation of frontend forms.
- **[SimpleFieldsValidator](docs/en/01-validators.md#simplefieldsvalidator)**  
Ensures the internal validation of form fields by calling `validate` on them.
- **[RequiredFieldsValidator](docs/en/01-validators.md#requiredfieldsvalidator)**  
Like Silverstripe's [RequiredFields](https://api.silverstripe.org/4/SilverStripe/Forms/RequiredFields.html) validator, but more convenient for use in a `CompositeValidator`.
- **[WarningFieldsValidator](docs/en/01-validators.md#warningfieldsvalidator)**  
Displays a warning if some field(s) doesn't have a value. Useful for alerting users about data that is technically valid but may not provide the results they expect
- **[DependentRequiredFieldsValidator](docs/en/01-validators.md#dependentrequiredfieldsvalidator)**  
Uses [SearchFilters](https://docs.silverstripe.org/en/4/developer_guides/model/searchfilters/) to define fields as required conditionally, based on the values of other fields (e.g. only required if `OtherField` has a value greater than 25).
- **[RequiredBlocksValidator](docs/en/01-validators.md#requiredblocksvalidator)**  
Require a specific [elemental block(s)](https://github.com/silverstripe/silverstripe-elemental) to exist in the `ElementalArea`, with optional minimum and maximum numbers of blocks and optional positional validation.
- **[RegexFieldsValidator](docs/en/01-validators.md#regexfieldsvalidator)**  
Ensure some field(s) matches a specified regex pattern.
### [Abstract Validators](docs/en/01-validators.md#abstract-validators)
- **[BaseValidator](docs/en/01-validators.md#basevalidator)**  
Includes methods useful for getting the actual `FormField` and its label.
- **[FieldHasValueValidator](docs/en/01-validators.md#fieldhasvaluevalidator)**  
Subclass of `BaseValidator`. Useful for validators that require logic to check if a field has any value or not.
## [Traits](docs/en/01-validators.md#traits)
- **[ValidatesMultipleFields](docs/en/01-validators.md#validatesmultiplefields)**  
Useful for validators that can be fed an array of field names to be validated.
- **[ValidatesMultipleFieldsWithConfig](docs/en/01-validators.md#validatesmultiplefieldswithconfig)**  
Like ValidatesMultipleFields but requires a configuration array for each field to be validated.
