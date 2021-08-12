# Validators
The validators in this module are designed to be very generic and reusable, and as such are easily added into a `CompositeValidator` along-side any project-specific or model-specific validators you implement yourself.

All of these validators can be used in the CMS _and_ in the front-end.

## AjaxCompositeValidator
**Important:** See the [extensions docs](./02-extensions.md) for extensions that are highly recommended if you intend to use this validator.

Note that to use this validator on the frontend, you will need to expose `jQuery` as a global variable. To avoid providing outdated or redundant copies of jQuery this module doesn't come packaged with it.

As of Silverstripe 4.7.0, all `DataObject`s have a [CompositeValidator](https://api.silverstripe.org/4/SilverStripe/Forms/CompositeValidator.html) automatically for CMS forms. The `AjaxCompositeValidator` is a subclass of that validator and provides AJAX validation that takes affect prior to form submission. When you click a form action that isn't validation exempt, an AJAX request is made to validate the form _prior_ to form submission. If there are any validation errors, form submission will be blocked and validation messages displayed.

This is useful for situations where data can be lost with the normal Silverstripe validation pipeline (e.g. validating fields on a page that has an [elemental area](https://github.com/silverstripe/silverstripe-elemental) - see [this issue](https://github.com/silverstripe/silverstripe-elemental/issues/764)), and is also faster as it doesn't need to reload the form after validating to display the error messages.

This validator is also extremely useful for front-end forms, as it provides client-side-esque validation without having to re-write all of your validation logic. It even checks for Google Recaptcha v2, and will produce an error if it identifies that a Recaptcha v2 exists for the form but was not completed.

### Usage
If the [DataObjectDefaultAjaxExtension](./02-extensions.md#dataobjectdefaultajaxextension) extension has been applied, calling `parent::getCMSCompositeValidator` inside the `getCMSCompositeValidator` method will return an `AjaxCompositeValidator`, which can then be manipulated.

You can also opt to just return a new `AjaxCompositeValidator` from that method - and in front-end situations, you can instantiate a new `AjaxCompositeValidator` (preferably [via injection](https://docs.silverstripe.org/en/4/developer_guides/extending/injector/) i.e. `AjaxCompositeValidator::create()`).

Ajax validation can also be disabled at any stage, if there is a cause for doing so.
```PHP
// This example assumes use of the validator in the CMS, with the DataObjectDefaultAjaxExtension extension applied.
// If this was for frontend use, you would be well-advised to explicitly include a SimpleFieldsValidator.
public function getCMSCompositeValidator(): CompositeValidator
{
    $validator = parent::getCMSCompositeValidator();
    // Add new validators.
    $validator->addValidators([
        WarningFieldsValidator::create(['Introduction']),
        SomeOtherValidator::create(),
    ]);
    // Get an existing validator - or if no validator of that type exists, create a new one.
    $requiredFields = $validator->getOrAddValidatorByType(RequiredFieldsValidator::class);
    $requiredFields->addFields([
        'Title',
        'Image',
        'SomeObjectID',
    ]);
    // Turn AJAX off if it is causing issues.
    $validator->setAjax(false);
    return $validator;
}
```

### Known Issues
Some actions (such as delete, archive, and restore) should be allowed even if the data is not valid (we should be allowed to delete an object _especially_ if its data is invalid), but those actions are not validation exempt by default. [Two extensions](./02-extensions.md#dataobjectvalidationexemptionextension-and-gridfielditemrequestvalidationexemptionextension) are provided with this module to remedy this and we strongly recommend applying them.

## SimpleFieldsValidator
This validator simply calls validate on all all fields in the form, ensuring the internal validation of form fields. It should _always_ be included in an `AjaxCompositeValidator` unless some other validator being used also performs that function (such as Silverstripe's own `RequiredFields` validator - though you should generally use this module's `RequiredFieldsValidator` instead).

### Usage
In most situations this validator will require no configuration at all.

However, this validator comes with [an extension](02-extensions.md#formfieldextension) which adds `setOmitSimpleValidation()` and `getOmitSimpleValidation()` methods to all `FormField`s. This can be used if, for specific use cases, internal field validation should be conditional. In that case you can set `OmitSimpleValidation` to false, and handle the conditional validation of the field in a separate validator.

```PHP
// In the DataObject which needs the custom validation
public function getCMSFields()
{
    $fields = parent::getCMSFields();
    $fields->add(SomeField::create('FieldName')->setOmitSimpleValidation(false));
    return $fields;
}
public function getCMSCompositeValidator() : CompositeValidator
{
    $validator = parent::getCMSCompositeValidator();
    $validator->addValidator(CustomValidator::create());
    return $validator;
}

// In your CustomValidator
public function php($data)
{
    $valid = true;
    /* Other custom validation here */
    if ($fieldNeedsValidation) {
        $someField = $this->form->Fields()->dataFieldByName('FieldName');
        $valid = $someField->validate($this) && $valid;
    }
    return $valid;
}
```

You may also want to omit certain `FormField` subclasses from validation during AJAX validation calls (assuming you're using the `AjaxCompositeValidator`), and only validate them during the final form submission. This can be useful if (as in the case of the [undefinedoffset/silverstripe-nocaptcha](https://github.com/UndefinedOffset/silverstripe-nocaptcha) module's `NocaptchaField`) the field cannot be validated more than once with the same value.

You can do this by setting the class name for that `FormField` in the `SimpleFieldsValidator`'s `ignore_field_classes_on_ajax` config array.
```yml
Signify\ComposableValidators\Validators\SimpleFieldsValidator:
  ignore_field_classes_on_ajax:
    - UndefinedOffset\NoCaptcha\Forms\NocaptchaField
```

That specific class is already added by default, but you can add others if you find similar situations.

## FieldHasValueValidator
This abstract class is useful as a superclass for any validator that needs to check if fields have a value. This functionality is used in the `RequiredFieldsValidator`, `WarningFieldsValidator`, and `DependentRequiredFieldsValidator`.

It also has methods for getting a FormField from a Fieldlist and getting the appropriate field label for use in validation messages.
```php
// Get the actual FormField for the named field.
$fields = $this->form->Fields();
$formField = $this->getFormField($fields, $fieldName);
// Check if the field has a value.
$valid = !$this->fieldHasValue($data, $formField);
// Use the appropriate field label for the validation message.
$errorMessage = '"' . $this->getFieldLabel($formField) . '" is required';
$this->validationError($fieldName, $errorMessage, 'required');
```
The `fieldHasValue` method should correctly identify whether a field has a value or not for all `FormField`s that come packaged in Silverstripe framework itself. It's possible however that some module, or your own code, has a value format that isn't correctly covered by this module. For those situations, you can extend the functionality by implementing `updateFieldHasValue` in an `Extension` class:

```yml
Signify\ComposableValidators\Validators\FieldHasValueValidator:
  extensions:
    - MyFieldValueExtension
```
```php
class MyFieldValueExtension extends Extension
{
    /**
     * Determine whether a specific field has a value. Implement this method when either your custom code,
     * or some vendor code, has a field that isn't correctly handled by FieldHasValueValidator::fieldHasValue().
     *
     * @param FormField $formField
     * @param mixed $value
     */
    public function updateFieldHasValue(FormField $formField, $value)
    {
        // Return true or false to explicitly declare that the field does or does not have a value.
        if ($formField instanceof MyCustomFormField) {
            return /* some boolean logic to determine if the field has a value */;
        }

        // Either return nothing or explicitly return null if you do not want to affect the default functionality.
        return null;
    }
}
```


## RequiredFieldsValidator
This is a composable replacement for [RequiredFields](https://api.silverstripe.org/4/SilverStripe/Forms/RequiredFields.html). It doesn't perform the internal field validation that validator does, with the assumption that it will be paired with a `SimpleFieldsValidator`. Its usage is identical to [ValidatesMultipleFields](#validatesmultiplefields).

Displays a validation error if the field(s) has no value.

### Known Issues
While this validator can be used to require data in `GridField`s, as of writing this documentation GridFields don't display validation errors. This [has been raised](https://github.com/silverstripe/silverstripe-framework/issues/10014) in Silverstripe's issue tracker but in the meantime [an extension](./02-extensions.md#gridfieldmessagesextension) is included with this module to fix this problem - and the `AjaxCompositeValidator` will display validation error messages against GridFields even without that extension.  
This applies to the `WarningFieldsValidator` as well.

## WarningFieldsValidator
Similar to `RequiredFieldsValidator` except instead of blocking the item from saving, this allows the item to save and displays a warning rather than a full validation error. Its usage is identical to [ValidatesMultipleFields](#validatesmultiplefields).

This can be very useful for alerting users about data that is technically valid but may not provide the results they expect.

Displays a validation warning if the field(s) has no value.

## DependentRequiredFieldsValidator
Allows you to define fields as being required conditionally based on the values of other fields. It uses [SearchFilters](https://docs.silverstripe.org/en/4/developer_guides/model/searchfilters/) to provide a variety of ways to compare values, depending on what causes the fields to be required.

In the below example, we have fields with various levels of dependency on whether they are required or not.
- ExactValueField will only be required if the value of DependencyField exactly equals someExactValue. It is implicitly case sensitive by not including the `nocase` modifier, and implicitly an exact match by not declaring a SearchFilter.
- StartsEndsWithField will only be required if the value of DependencyField starts with the string "some" and _does not_ end with the string "s" - both case insensitive.
```php
DependentRequiredFieldsValidator::create([
    'ExactValueField' => ['DependencyField' => 'SomeExactValue'],
    'StartsEndsWithField' => [
        'DependencyField:StartsWith:nocase' => 'some',
        'DependencyField:EndsWith:not:nocase' => 's',
    ],
]);
```
**Note:** All of the dependencies must be met for a field to be considered required. So in the example above, if `DependencyField` had the value "someValues" the `StartsEndsWithField` would not be marked required, because only one of its dependencies is met.

This validator has all of the same methods provided by `ValidatesMultipleFields` except that `addField` requires two arguments (the field to mark as required, and its dependency array), and `addFields` requires an array similar to that in the example above.

The conditional checking functionality is powered by [signify-nz/silverstripe-searchfilter-arraylist](https://github.com/signify-nz/silverstripe-searchfilter-arraylist), which does provide some extensibility. You may want to check the documentation of that module.

All of the `SearchFilter`s and modifiers documented in [Silverstripe's SearchFilter documentation](https://docs.silverstripe.org/en/4/developer_guides/model/searchfilters/) should be supported - if you find that isn't the case, please [raise an issue](https://github.com/signify-nz/silverstripe-searchfilter-arraylist/issues) (or, better yet, a pull request) against the `signify-nz/silverstripe-searchfilter-arraylist` module.

## RequiredBlocksValidator
This validator checks for optional minimum and maximum numbers of a given [elemental block](https://github.com/silverstripe/silverstripe-elemental) class.

### Features
- Define blocks that _must_ exist (at least one) on the page (either in a specific position or just in general).
- Define positions that specific blocks must be in, if there is a block of that type on the page.
- Define a minimum or maximum number of blocks of a specific type to be on a page.

### Usage
The block classes to validate against, and their minimum or maximum numbers must be provided at instantiation of the validator.  
Configuration for this validator is case insensitive, so `min` is identical to `Min` and `MIN`, etc.

Note that this validator has no concept of inheritance - the class of the block must match exactly to be counted. Subclasses of required blocks do not count.

If no configuration is supplied, the defaults are to require at least 1 block of that type, with no maximum number.
```PHP
// Require at least one ElementContent block.
RequiredBlocksValidator::create([
    ElementContent::class,
]);
```

A minimum and/or maximum number of blocks of this type can be defined, as well as a position the block must be in.  
If a position is specified, and more than one block of this type is present, one must be in that position and the others can be in any position.  
Positions are 0 indexed (meaning the first position is 0, the second is 1, etc).  
Positions can also be defined from the bottom using negative numbers (where -1 is the bottom, -2 is second from the bottom, etc).  
All of these options are optional, but if none of these are defined a default min value of 1 will be implicitly set with no required position.
```PHP
RequiredBlocksValidator::create([
    // Require at least 3 ElementContent blocks.
    // One of these blocks must be 3rd from the top.
    ElementContent::class => [
        'min' => 3,
        'pos' => 2,
    ],
    // Do not allow more than 2 blocks of this type.
    // If any blocks of this type are present, one of them must be the last block.
    SomeOtherBlock::class => [
        'Max' => 2,
        'Pos' => -1,
    ],
]);
```
Note that if a maximum number of blocks is defined, blocks can still be created, saved, and published independently of the page since that is handled via graphql and doesn't trigger validation against the `DataObject` which owns the `ElementalArea`. Therefore this validation only stops users from saving the `DataObject` which owns the `ElementalArea` if the number of blocks is exceeded.

If more than one `ElementalArea` exists on the `DataObject` being validated, you can define which area(s) the validation applies to.  
Note that this validation is spread across all areas validated against. This means if you set a minimum of 3 blocks and two elemental areas, there must be 3 blocks spread however the user likes across both areas (e.g. 1 in area 1 and 2 in area 2) rather than requiring 3 blocks in each area.  
Positional validation is per area, so if a position is defined and a block exists in each area, it must be in the defined position in each of those areas.  
If the only configuration set for a block class is the AreaFieldName, a default min value of 1 will be implicitly set.
```PHP
RequiredBlocksValidator::create([
    ElementContent::class => [
        'Min' => 3,
        'AreaFieldName' => [
            'ElementalArea',
            'ElementalAreaTwo',
        ],
    ],
]);
```

### Known Issues
The `ElementalArea` field holder template doesn't currently render validation error messages. A [pull request](https://github.com/silverstripe/silverstripe-elemental/pull/921) has been created to remedy this, but in the meantime you must either use the `RequiredBlocksValidator` inside an `AjaxCompositeValidator` (which will display the message regardless of the template) or override the `ElementalAreaField_holder.ss` template in your project.

This validator validates when the page (or other DataObject that has an ElementalArea) is saved or published - but not necessarily when the blocks within the ElementalArea are saved or published. This means content authors can work around the validation errors if they really want to.

# Traits
## ValidatesMultipleFields
This trait is used in both the [RequiredFieldsValidator](#requiredfieldsvalidator) and [WarningFieldsValidator](#warningfieldsvalidator). It is useful for any validator that can be fed an array of field names that need to be validated.

### Usage
This usage applies to all validators that use `ValidatesMultipleFields`. It is intentionally very similar to Silverstripe's `RequiredFields` usage.
```php
// You can pass fields to be validated into the constructor as strings or as an array (or not pass any fields at all to the constructor).
$validator = SomeMultiFieldValidator::create();
$validator = SomeMultiFieldValidator::create('SomeField');
$validator = SomeMultiFieldValidator::create('Somefield', 'SomeOtherField');
$validator = SomeMultiFieldValidator::create([
    'SomeField',
    'SomeOtherField',
]);

// Add fields later either one at a time or all at once. The argument for addFields must be an array.
$validator->addField('SomeNewField');
$validator->addFields([
    'SomeNewField',
    'YetAnotherField',
]);

// Remove fields from the validator one at a time, many at once, or all at once. The argument for removeFields must be an array.
$validator->removeField('SomeNewField');
$validator->removeFields([
    'SomeNewField',
    'YetAnotherField',
]);
$validator->removeValidation();

// Add the fields from another validator that uses ValidatesMultipleFields.
$requiredFieldsValidator = RequiredFieldsValidator::create('SomeNewField');
$validator->appendFields($requiredFieldsValidator);

// Get the names of all fields to be validated.
$fields = $validator->getFields();
```
Note that the field name passed should _always_ be the name of the `FormField`. This is especially important for fields representing a `has_one` relation where the name of an `UploadField` will omit the 'ID' (e.g. 'SomeImage', not 'SomeImageID'), but a `DropdownField`, `TreeDropdownField`, or `OptionSetField` will include the 'ID' (e.g. 'SomeImageID, not 'SomeImage').
