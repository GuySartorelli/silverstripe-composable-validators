<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\Validator;

abstract class FieldHasValueValidator extends Validator
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
        return $fields->dataFieldByName($fieldName);
    }

    protected function getFieldLabel(FormField $field): string
    {
        return $field->Title() ? $field->Title() : $field->getName();
    }

    /**
     * Check if a field has a value in the given data array.
     *
     * @param array $data
     * @param FormField $formField
     * @return boolean
     */
    protected function fieldHasValue(array $data, FormField $formField): bool
    {
        $fieldName = $formField->getName();
        // submitted data for grid field and file upload fields come back as an array
        $value = isset($data[$fieldName]) ? $data[$fieldName] : null;

        // Allow projects to add their own definitions of fields with values (e.g. for custom fields)
        $extendedHas = $this->extendedHas('updateFieldHasValue', $formField, $value);
        if ($extendedHas !== null) {
            return $extendedHas;
        }

        // TreeDropdownFields give a value of '0' when no item is selected.
        if ($formField instanceof TreeDropdownField && $value === '0') {
            return false;
        }

        // If the value is an array, there are a few different things it could represent. Check each in turn.
        if (is_array($value)) {
            if ($formField instanceof FileField && isset($value['error']) && $value['error']) {
                return false;
            } elseif ($formField instanceof GridField && $formField->getList()->count() === 0) {
                return false;
            } else {
                return (count($value)) ? true : false;
            }
        }

        // assume a string or integer
        return (strlen($value)) ? true : false;
    }

    private function extendedHas($methodName, $formField, $value)
    {
        $results = $this->extend($methodName, $formField, $value);
        if ($results && is_array($results)) {
            // Remove NULLs
            $results = array_filter($results, function ($v) {
                return !is_null($v);
            });
            // If there are any non-NULL responses, then return the lowest one of them.
            // If any explicitly say there is no value, then there is no value.
            if ($results) {
                return min($results);
            }
        }
        return null;
    }
}
