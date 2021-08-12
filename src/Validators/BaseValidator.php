<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\Validator;

abstract class BaseValidator extends Validator
{
    /**
     * Get an associative array indicating what fields in which tabs (if any)
     * have what validation requirements.
     *
     * @return string[]
     */
    public abstract function getValidationHints(): array;

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

    /**
     * Get the Tab the field resides in, if any.
     *
     * @param FormField $field
     * @return Tab|null
     */
    protected function getTabForField(FormField $field): ?Tab
    {
        $tab = null;
        while ($field && $parent = $field->getContainerFieldList()) {
            $field = $parent->getContainerField();
            if ($field instanceof Tab) {
                $tab = $field;
            }
        }
        return $tab;
    }
}
