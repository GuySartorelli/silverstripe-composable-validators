# Optional configuration

You may want to replace the default `CompositeValidator` that all `DataObject`s have (see `DataObject::getCMSCompositeValidator()`) with this module's [`AjaxCompositeValidator`](./01-validators.md#ajaxcompositevalidator).

```yml
Injector:
  SilverStripe\Forms\Validation\CompositeValidator:
    class: 'Signify\ComposableValidators\Validators\AjaxCompositeValidator'
```

# Optional extensions

These extensions are not applied by default, but it is strongly recommended you do apply them in your project.

## DataObjectValidationExemptionExtension and GridFieldItemRequestValidationExemptionExtension

```yml
SilverStripe\ORM\DataObject:
  extensions:
    - Signify\ComposableValidators\Extensions\DataObjectValidationExemptionExtension

SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest:
  extensions:
    - Signify\ComposableValidators\Extensions\GridFieldItemRequestValidationExemptionExtension
```

For whatever reason, the "delete", "archive", and "restore" actions in Silverstripe are _not_ validation exempt actions. This can cause issues with the [`AjaxCompositeValidator`](./01-validators.md#ajaxcompositevalidator) which won't let you perform those actions if the data doesn't pass validation.

**These extensions are necessary** if you're using the `AjaxCompositeValidator`, but aren't applied by default in case they cause issues in some projects.

# Default extensions

These extensions are already applied by default. They shouldn't interfere with any project or vendor code, and are necessary for certain features to function correctly.

## FormExtension

Provides the action used for AJAX validation via the [`AjaxCompositeValidator`](./01-validators.md#ajaxcompositevalidator).

## FormFieldExtension

Provides the `setOmitFieldValidation()` and `getOmitFieldValidation()` methods to determine if `validate()` should be called on the `FormField` instances in `Form::validate()`
