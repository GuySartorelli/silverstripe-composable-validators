<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ChecksIfFieldHasValue;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\FieldList;

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

        // Validate each field.
        foreach ($this->fields as $fieldName) {
            if (!$fieldName) {
                continue;
            }
            $valid = $this->validateField($data, $fields, $fieldName) && $valid;
        }

        return $valid;
    }

    /**
     * Check if the field has a value, and prepare a validation error if not.
     *
     * @param array $data
     * @param FieldList $fields
     * @param string $fieldName
     * @return boolean True if the field has a value.
     */
    protected function validateField($data, FieldList $fields, string $fieldName): bool
    {
        $formField = $this->getFormField($fields, $fieldName);
        if ($formField && !$this->fieldHasValue($data, $formField)) {
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
                'required'
            );

            return false;
        }
        return true;
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
        $required = isset($this->fields[$fieldName]);
        // Ensure UploadFields have the correct aria-required attribute if they're required.
        if ($required && $this->form && $this->form->Fields()) {
            if (!$field = $this->form->Fields()->fieldByName($fieldName)) {
                $field = $this->form->Fields()->dataFieldByName($fieldName);
            }
            if ($field && $field instanceof UploadField) {
                $field->setAttribute('aria-required', 'true');
            }
        }
        return $required;
    }
}
