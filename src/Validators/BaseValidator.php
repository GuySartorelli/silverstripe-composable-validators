<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\Validation\Validator;

abstract class BaseValidator extends Validator
{
    /**
     * Get an associative array indicating what fields in which tabs (if any)
     * have what validation requirements.
     *
     * @return string[]
     */
    abstract public function getValidationHints(): array;

    /**
     * Get the form field from a field list.
     */
    protected function getFormField(FieldList $fields, string $fieldName): ?FormField
    {
        return $fields->dataFieldByName($fieldName) ?? $fields->fieldByName($fieldName);
    }

    /**
     * Get the appropriate field label for use in validation messages.
     */
    protected function getFieldLabel(FormField $field): string
    {
        return $field->Title() ? $field->Title() : $field->getName();
    }

    /**
     * Get the Tab the field resides in, if any.
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
