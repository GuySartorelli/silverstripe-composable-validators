<?php

namespace Signify\ComposableValidators\Traits;

use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;

trait ChecksIfFieldHasValue
{
    protected function getFormField($fields, &$fieldName)
    {
        if ($fieldName instanceof FormField) {
            $fieldName = $fieldName->getName();
        }
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

        // If the value is an array, there are a few different things it could represent. Check each in turn.
        if (is_array($value)) {
            if ($formField instanceof FileField && isset($value['error']) && $value['error']) {
                return true;
            } else if ($formField instanceof GridField && $formField->getList()->count() === 0) {
                return true;
            } else {
                return (count($value)) ? false : true;
            }
        }
        // assume a string or integer
        return (strlen($value)) ? false : true;
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
            // If any explicitly deny the permission, then we don't get access
            if ($results) {
                return min($results);
            }
        }
        return null;
    }
}
