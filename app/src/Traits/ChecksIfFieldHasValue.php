<?php

namespace App\Traits;

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