<?php
namespace App\Validators;

use App\Traits\ChecksIfFieldHasValue;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\RequiredFields;

/**
 * An implementation of {@link RequiredFields} that doesn't validate fields internally.
 * This is for use within a {@link CompositeValidator} in conjunction with a {@link SimpleValidator}.
 */
class RequiredFieldsValidator extends RequiredFields
{
    use ChecksIfFieldHasValue;

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

            $formField = $this->getFormField($fields, $fieldName);
            $error = $this->fieldHasValue($data, $formField, $fieldName);

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

    /**
     * Adds multiple required fields to required fields stack.
     *
     * @param string[] $fields
     *
     * @return $this
     */
    public function addRequiredFields($fields)
    {
        $this->required = array_merge($this->required, $fields);

        return $this;
    }
}
