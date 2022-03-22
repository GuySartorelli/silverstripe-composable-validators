# Automated Client-Side Tests

If your project has any automated client-side tests, be it with [Behat](https://github.com/silverstripe/silverstripe-behat-extension), [Selenium](https://www.selenium.dev), or some other framework, it may be useful to know before you submit a form what validation is required for which fields.

The [`AjaxCompositeValidator`](./01-validators.md#ajaxcompositevalidator) adds a `data-signify-validation-hints` attribute to any form it is added to. The value of this attribute is an empty JSON array if there is no validation, or a JSON object if there is. The JSON object has field IDs for keys with the validation requirements for that field in JSON objects for values.

The abstract [`BaseValidator`](./01-validators.md#basevalidator) class provides an abstract `getValidationHints` method which must return an array. This array should contain the full validation requirements of each field that will be validated by a given validator. **You should implement this method in any custom validators you create which you intend to include in an `AjaxCompositeValidator`**.

All of the validators included in this module that perform their own validation implementations implement the `getValidationHints` method, so their validation requirements are included in the validation hints attribute.

## Syntax for `data-signify-validation-hints` and `getValidationHints`

As mentioned above, the value of this attribute is an empty JSON array if there is no validation, or a JSON object if there is. The JSON object has field IDs for keys with the validation requirements for that field in JSON objects for values. In PHP these are represented as associative arrays.

All validators include the field's name in the JSON object for that field. The key for that data is 'name'.

All validators include a `tab` key (using the `getTabForField` method provided by `BaseValidator`) for each field being validated if the field is inside a tab. The value is the return value from calling `ID` on the tab. Note that the `TabSet.ss` template prepends this value with "tab-" for the actual ID attribute of the tab link element.  
**See also [the known issue](#tab-sometimes-missing) relating to this.**

When implementing `getValidationHints` in your own validators, make sure to document the syntax outputted by those validators so that anyone writing tests for them knows what to expect.

### [RequiredFieldsValidator](./01-validators.md#requiredfieldsvalidator)

This validator simply provides a `required` key, which is always true for all fields that are required in this validator.

```JSON
{
    "Form_EditForm_SomeField": {
        "name": "SomeField",
        "tab": "Root_Main",
        "required": true
    }
}
```

### [DependentRequiredFieldsValidator](./01-validators.md#dependentrequiredfieldsvalidator)

This validator provides a `dependencies` key, the value for which is another JSON object. This JSON object is a direct output from the dependencies php array entered into the validator for that field, with its `SearchFilter` syntax as keys and value(s) for the filter to match against as the values.

```JSON
{
    "Form_EditForm_SomeField": {
        "name": "SomeField",
        "tab": "Root_Main",
        "dependencies": {
            "SomeOtherField:PartialMatch:nocase": [
                "hello world",
                "awawa"
            ],
            "SomeOtherField:not:nocase": "hello world"
        }
    }
}
```

### [RequiredBlocksValidator](./01-validators.md#requiredblocksvalidator)

This validator provides a `required-elements` key, the value for which is a JSON object, which has the configuration for any elemental blocks being validated against that elemental area. The key for the object is the fully namespaced class name for the block, and the value is a JSON object that represents the validation requirements for that block.

The validation requirement keys are always present for each validated block, but have a null value if they won't be checked (e.g. in the below example, there must be at least 2 content blocks, with no maximum limit, and at least one of those blocks must be in the second position from the top). The name of the block as presented in the "Add block" list is also provided, with `name` as its key.

**NOTE:** Make sure to read the documentation for this validator carefully, as it has specific functionality for when more than one elemental area exists in the same form.

```JSON
{
    "Form_EditForm_ElementalArea": {
        "name": "ElementalArea",
        "tab": "Root_Main",
        "required-elements": {
            "DNADesign\\Elemental\\Models\\ElementContent": {
                "name": "Content",
                "min": 2,
                "max": null,
                "pos": 1
            }
        }
    }
}
```

### [RegexFieldsValidator](./01-validators.md#regexfieldsvalidator)

This validator provides a `regex` key. The value will be an array of strings representing a regex patterns. Only one of the patterns needs to match for the value to be valid. Note that the regex is in php syntax, which differs slightly from the syntax used by javascript.

```JSON
{
    "Form_EditForm_SomeField": {
        "name": "SomeField",
        "tab": "Root_Main",
        "regex": [
            "/(?!^\\d+$)^.*?$/",
            "/^[\\d]$/"
        ]
    },
    "Form_EditForm_SomeOtherField": {
        "name": "SomeOtherField",
        "tab": "Root_Main",
        "regex": [
            "/^[a-zA-Z]+$/"
        ]
    }
}
```

## Disable Validation Hints

If for some reason you really don't want validation hints to be provided, you can disable them either globally or per `AjaxCompositeValidator` instance.

```yml
# Disable validation hints globally
Signify\ComposableValidators\Validators\AjaxCompositeValidator
  add_validation_hint: false
```

```php
// Disable validation hints for a specific AjaxCompositeValidator instance.
AjaxCompositeValidator::create()->setAddValidationHint(false);
```

## Known Issues

### `validate` method on `FormField`s or `DataObject`s

Any validation in the `validate` method of a `FormField` or `DataObject` is not currently covered by the validation hints. For any built-in `FormField`s that shouldn't be much of a problem, as the type of the field element and, for `input` elements the `type` attribute (and for non html-5 implementations of some fields the class selectors), already give clues as to the type of data that is valid.

For custom validation this could be worked around by adding a `Validator` that implements `getValidationHints` and simply returns hints for that internal validation, without performing any validation of its own.

### Tab Sometimes Missing

Sometimes the `tab` key may be missing from a field's validation hint even though the field is definitely in a tab. Unfortunately whenever `flattenFields` is called on a `FieldList`, this changes the `containerFieldList` value for that field, so while the form and the field list know where the field resides, the field thinks it is sitting inside the flattened list and doesn't know anything about the original list inside the form.

This has been [raised as an issue](https://github.com/silverstripe/silverstripe-framework/issues/10054) against `silverstripe/framework` but until it is resolved, tests will have to have an alternative way of finding the field other than relying on the `tab` key in the hint object.
