# SilverStripe Composable Validators

Provides number of reusable composable validators (including AJAX validation) for use both in the CMS and in frontend forms.

Make sure you check out the [extensions documentation](docs/en/02-extensions.md) at a minimum - some of these should be applied in almost all projects using this module.

## Install

Install via [composer](https://getcomposer.org):

```bash
composer require signify-nz/silverstripe-composable-validators
```

## [Available Validators](docs/en/01-validators.md)
- **[AjaxCompositeValidator](docs/en/01-validators.md#ajaxcompositevalidator)**  
Subclass of [CompositeValidator](https://api.silverstripe.org/4/SilverStripe/Forms/CompositeValidator.html) that provides AJAX validation. Resolves [an issue with losing data](https://github.com/silverstripe/silverstripe-elemental/issues/764), faster turn-around for fixing validation problems, and provides a way to use the same validation for 'client-side' validation of frontend forms.
- **[SimpleFieldsValidator](docs/en/01-validators.md#simplefieldsvalidator)**  
Ensures the internal validation of form fields by calling `validate` on them.
- **[FieldHasValueValidator](docs/en/01-validators.md#fieldhasvaluevalidator)**  
Abstract class - useful for validators that require logic to check if a field has any value or not.
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
## [Traits](docs/en/01-validators.md#traits)
- **[ValidatesMultipleFields](docs/en/01-validators.md#validatesmultiplefields)**  
Useful for validators that can be fed an array of field names to be validated.
- **[ValidatesMultipleFieldsWithConfig](docs/en/01-validators.md#validatesmultiplefieldswithconfig)**  
Like ValidatesMultipleFields but requires a configuration array for each field to be validated.
