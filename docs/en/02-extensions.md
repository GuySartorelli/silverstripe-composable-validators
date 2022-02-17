# Extensions
These extensions are not applied by default, but it is strongly recommended you do apply them in your project.

## DataObjectDefaultAjaxExtension
```yml
SilverStripe\ORM\DataObject:
  extensions:
    - Signify\ComposableValidators\Extensions\DataObjectDefaultAjaxExtension
```
Replaces the default `CompositeValidator` that all `DataObject`s have (see `DataObject::getCMSCompositeValidator()`) with this module's [AjaxCompositeValidator](./01-validators.md#ajaxcompositevalidator).
Unfortunately at the time of writing these docs, the `CompositeValidator` is instantiated using the `new` keyword instead of using the `create` method, so you can't just replace it outright in the `Injector` - but even if you could, we'd strongly recommend including a `SimpleFieldsValidator`, which would be tricky if possible at all to do via the `Injector`.

This extension also automatically adds a [SimpleFieldsValidator](./01-validators.md#simplefieldsvalidator) to ensure all form fields have valid data.

## DataObjectValidationExemptionExtension and GridFieldItemRequestValidationExemptionExtension
```yml
SilverStripe\ORM\DataObject:
  extensions:
    - Signify\ComposableValidators\Extensions\DataObjectValidationExemptionExtension

SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest:
  extensions:
    - Signify\ComposableValidators\Extensions\GridFieldItemRequestValidationExemptionExtension
```
For whatever reason, the "delete", "archive", and "restore" actions in Silverstripe are _not_ validation exempt actions. This can cause issues with the [AjaxCompositeValidator](./01-validators.md#ajaxcompositevalidator) which won't let you perform those actions if the data doesn't pass validation.

**These extensions are necessary** if you're using the `AjaxCompositeValidator`, but aren't applied by default in case they cause issues in some projects.

## GridFieldMessagesExtension
```yml
SilverStripe\Forms\GridField\GridField:
  extensions:
    - Signify\ComposableValidators\Extensions\GridFieldMessagesExtension
```
Ensures validation messages display for a GridField. GridFields didn't display validation messages prior to 4.10.0.

# Default extensions
These extensions are already applied by default. They shouldn't interfere with any project or vendor code, and are necessary for certain features to function correctly.

## FormExtension
Provides the action used for AJAX validation via the [AjaxCompositeValidator](./01-validators.md#ajaxcompositevalidator).

## FormFieldExtension
Provides the `setOmitFieldValidation` and `getOmitFieldValidation` methods to determine if fields should be validated by the [SimpleFieldsValidator](./01-validators.md#simplefieldsvalidator).
