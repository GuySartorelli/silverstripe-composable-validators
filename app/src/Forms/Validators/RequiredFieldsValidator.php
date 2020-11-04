<?php
namespace App\Validators;

use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\RequiredFields;

/**
 * An implementation of {@link RequiredFields} that doesn't validate fields internally.
 * This is for use within a {@link MultiValidator} which does the internal validation.
 */
class RequiredFieldsValidator extends RequiredFields
{
    /**
     * Validates that the required fields have values.
     * Almost a direct copy from {@link RequiredFields::php()}
     *
     * @param array $data
     *
     * @return boolean
     */
    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        if (!$this->required) {
            return $valid;
        }

        foreach ($this->required as $fieldName) {
            if (!$fieldName) {
                continue;
            }

            if ($fieldName instanceof FormField) {
                $formField = $fieldName;
                $fieldName = $fieldName->getName();
            } else {
                $formField = $fields->dataFieldByName($fieldName);
            }

            // submitted data for file upload fields come back as an array
            $value = isset($data[$fieldName]) ? $data[$fieldName] : null;

            if (is_array($value)) {
                if ($formField instanceof FileField && isset($value['error']) && $value['error']) {
                    $error = true;
                } else {
                    $error = (count($value)) ? false : true;
                }
            } else {
                // assume a string or integer
                $error = (strlen($value)) ? false : true;
            }

            if ($formField && $error) {
                $errorMessage = _t(
                    'SilverStripe\\Forms\\Form.FIELDISREQUIRED',
                    '{name} is required',
                    array(
                        'name' => strip_tags(
                            '"' . ($formField->Title() ? $formField->Title() : $fieldName) . '"'
                        )
                    )
                );

                if ($msg = $formField->getCustomValidationMessage()) {
                    $errorMessage = $msg;
                }

                $this->validationError(
                    $fieldName,
                    $errorMessage,
                    "required"
                );

                $valid = false;
            }
        }

        return $valid;
    }
}
