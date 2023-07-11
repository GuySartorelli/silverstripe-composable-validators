<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ValidatesMultipleFields;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\ValidationResult;

/**
 * A validator used to display warnings if certain fields do not have values.
 *
 * This validator is best used within an AjaxCompositeValidator in conjunction with
 * a SimpleFieldsValidator.
 */
class WarningFieldsValidator extends FieldHasValueValidator
{
    use ValidatesMultipleFields;

    /**
     * Validates that the required fields have values.
     * If any field doesn't have a value, a warning message is displayed.
     *
     * @param array $data
     * @return true
     */
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
     * @return bool True if a warning is prepared for the field.
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

    public function getValidationHintForField(FormField $formField): ?array
    {
        return null;
    }
}
