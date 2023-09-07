<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TreeDropdownField;

/**
 * A validator that checks whether fields have values.
 */
abstract class FieldHasValueValidator extends BaseValidator
{
    /**
     * Check if a field has a value in the given data array.
     *
     * @param array $data
     * @param FormField $formField
     * @return bool
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
        return (strlen($value ?? '')) ? true : false;
    }

    /**
     * Call an extension method and if any Extension returns a boolean value, return that value.
     * If any Extension returns false, that takes priority over any Extensions returning true. This
     * way any Extension saying the field is invalid will ensure a validation error message displays.
     *
     * @param string $methodName
     * @param FormField $formField
     * @param mixed $value
     * @return bool|null
     */
    private function extendedHas(string $methodName, FormField $formField, $value)
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
