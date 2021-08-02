<?php

namespace Signify\ComposableValidators\Traits;

use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\TreeDropdownField;

trait ChecksIfFieldHasValue
{
    protected function getFormField($fields, &$fieldName)
    {
        return $fields->dataFieldByName($fieldName);
    }

    protected function fieldHasValue($data, $formField, $fieldName)
    {
        // submitted data for grid field and file upload fields come back as an array
        $value = isset($data[$fieldName]) ? $data[$fieldName] : null;

        // Allow projects to add their own definitions of fields with values (e.g. for custom fields)
        $extendedHas = $this->extendedHas('updateFieldHasValue', $value);
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
            } else if ($formField instanceof GridField && $formField->getList()->count() === 0) {
                return false;
            } else {
                return (count($value)) ? true : false;
            }
        }

        // assume a string or integer
        return (strlen($value)) ? true : false;
    }

    private function extendedHas($methodName, $value)
    {
        $results = $this->extend($methodName, $value);
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
