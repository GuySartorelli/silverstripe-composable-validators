<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ChecksIfFieldHasValue;
use SilverStripe\ORM\ValidationResult;

/**
 * Similar to {@link \App\Validators\RequiredFieldsValidator} but produces a warning rather than a validation error.
 * This is for use within a {@link CompositeValidator} in conjunction with a {@link SimpleFieldsValidator}.
 */
class WarningFieldsValidator extends MultiFieldValidator
{
    use ChecksIfFieldHasValue;

    public function php($data)
    {
        $warning = false;
        $fields = $this->form->Fields();

        if (!$this->fields) {
            return true;
        }

        foreach ($this->fields as $fieldName) {
            if (!$fieldName) {
                continue;
            }

            $formField = $this->getFormField($fields, $fieldName);
            if ($formField && !$this->fieldHasValue($data, $formField)) {
                $name = strip_tags('"' . ($formField->Title() ? $formField->Title() : $fieldName) . '"');
                $errorMessage = "$name has no value and will not display";
                $this->result->addFieldMessage($fieldName, $errorMessage, ValidationResult::TYPE_WARNING);

                $warning = true;
            }
        }

        if ($warning) {
            $this->form->setSessionValidationResult($this->result);
        }

        return true;
    }
}
