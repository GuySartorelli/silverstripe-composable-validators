<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\Validator;

abstract class BaseValidator extends Validator
{
    /**
     * Get the form field from a field list.
     *
     * @param FieldList $fields
     * @param string $fieldName
     * @return FormField|null
     */
    protected function getFormField(FieldList $fields, string $fieldName): ?FormField
    {
        return $fields->dataFieldByName($fieldName) ?? $fields->fieldByName($fieldName);
    }

    /**
     * Get the appropriate field label for use in validation messages.
     *
     * @param FormField $field
     * @return string
     */
    protected function getFieldLabel(FormField $field): string
    {
        return $field->Title() ? $field->Title() : $field->getName();
    }
}
