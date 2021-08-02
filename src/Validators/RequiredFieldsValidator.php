<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ChecksIfFieldHasValue;

/**
 * An implementation of {@link RequiredFields} that doesn't validate fields internally.
 * This is for use within a {@link CompositeValidator} in conjunction with a {@link SimpleFieldsValidator}.
 */
class RequiredFieldsValidator extends MultiFieldValidator
{
    use ChecksIfFieldHasValue;

    /**
     * Validates that the required fields have values.
     *
     * @param array $data
     *
     * @return boolean
     */
    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        if (!$this->fields) {
            return $valid;
        }

        foreach ($this->fields as $fieldName) {
            if (!$fieldName) {
                continue;
            }

            $formField = $this->getFormField($fields, $fieldName);
            if ($formField && !$this->fieldHasValue($data, $formField, $fieldName)) {
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
     * Returns true if the named field is "required".
     *
     * Used by {@link FormField} to return a value for FormField::Required(),
     * to do things like show *s on the form template.
     *
     * @param string $fieldName
     *
     * @return boolean
     */
    public function fieldIsRequired($fieldName)
    {
        return isset($this->fields[$fieldName]);
    }
}
