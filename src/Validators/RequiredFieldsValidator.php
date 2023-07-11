<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ValidatesMultipleFields;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;

/**
 * A validator used to ensure certain required fields have values.
 *
 * This validator is best used within an AjaxCompositeValidator in conjunction with
 * a SimpleFieldsValidator.
 */
class RequiredFieldsValidator extends FieldHasValueValidator
{
    use ValidatesMultipleFields;

    /**
     * Validates that the required fields have values.
     *
     * @param array $data
     * @return bool
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
     * @return bool True if the field has a value.
     */
    protected function validateField($data, FieldList $fields, string $fieldName): bool
    {
        $formField = $this->getFormField($fields, $fieldName);
        if ($formField && !$this->fieldHasValue($data, $formField)) {
            $errorMessage = _t(
                self::class . '.FIELD_IS_REQUIRED',
                '{name} is required',
                [
                    'name' => strip_tags(
                        '"' . $this->getFieldLabel($formField) . '"'
                    )
                ]
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
     * @return bool
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

    public function getValidationHintForField(FormField $formField): ?array
    {
        if (in_array($formField->getName(), $this->getFields())) {
            return [
                'required' => true,
            ];
        }
        return null;
    }
}
