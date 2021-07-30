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
            $formField = $fieldName;
            $fieldName = $fieldName->getName();
            return $formField;
        } else {
            return $fields->dataFieldByName($fieldName);
        }
    }

    protected function fieldHasValue($data, $formField, $fieldName)
    {
        // submitted data for grid field and file upload fields come back as an array
        $value = isset($data[$fieldName]) ? $data[$fieldName] : null;

        // Allow projects to add their own definitions of fields with values (e.g. for custom fields)
        $extendedHas = $this->extend('updateFieldHasValue', $value);
        if ($extendedHas !== null) {
            return $extendedHas;
        }

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
}
