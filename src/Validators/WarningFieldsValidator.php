<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ValidatesMultipleFields;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ValidationResult;

/**
 * Similar to {@link \App\Validators\RequiredFieldsValidator} but produces a warning rather than a validation error.
 * This is for use within a {@link CompositeValidator} in conjunction with a {@link SimpleFieldsValidator}.
 */
class WarningFieldsValidator extends FieldHasValueValidator
{
    use ValidatesMultipleFields;

    public function php($data)
    {
        $warning = false;
        $fields = $this->form->Fields();

        // Validate each field.
        foreach ($this->fields as $fieldName) {
            if (!$fieldName) {
                continue;
            }
            $warning = $this->validateField($data, $fields, $fieldName) || $warning;
        }

        // Use session validation to ensure the warning displays after form submission.
        if ($warning) {
            $this->form->setSessionValidationResult($this->result);
        }

        // Always return true, to avoid blocking the values from being saved.
        return true;
    }

    /**
     * Check if the field has a value, and prepare a warning if not.
     *
     * @param array $data
     * @param FieldList $fields
     * @param string $fieldName
     * @return boolean True if a warning is prepared for the field.
     */
    protected function validateField($data, FieldList $fields, string $fieldName): bool
    {
        $formField = $this->getFormField($fields, $fieldName);
        if ($formField && !$this->fieldHasValue($data, $formField)) {
            $name = strip_tags('"' . $this->getFieldLabel($formField) . '"');
            $errorMessage = _t(
                self::class . '.WARNING',
                '{name} has no value and will not display or be used',
                ['name' => $name]
            );
            $this->result->addFieldMessage($fieldName, $errorMessage, ValidationResult::TYPE_WARNING);
            return true;
        }
        return false;
    }
}
