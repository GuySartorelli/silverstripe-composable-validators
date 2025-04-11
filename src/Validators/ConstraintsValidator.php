<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ValidatesMultipleFieldsWithConfig;
use SilverStripe\Forms\FormField;
use SilverStripe\Core\Validation\ConstraintValidator;

/**
 * A validator which Validates values based on symfony validation constraints.
 *
 * Configuration values for this validator is an array of constraints to validate each field value against.
 * For example:
 * $validator->addField(
 *     'IpAddress',
 *     [
 *         new Symfony\Component\Validator\Constraints\Ip(),
 *         new Symfony\Component\Validator\Constraints\NotBlank()
 *     ]
 * );
 *
 * See https://symfony.com/doc/current/reference/constraints.html for a list of constraints.
 *
 * This validator is best used within an AjaxCompositeValidator
 */
class ConstraintsValidator extends BaseValidator
{
    use ValidatesMultipleFieldsWithConfig;

    /**
     * Validates that the required blocks exist in the configured positions.
     *
     * @param array $data
     */
    public function php($data): bool
    {
        foreach ($this->getFields() as $fieldName => $constraint) {
            $value = isset($data[$fieldName]) ? $data[$fieldName] : null;
            $this->result->combineAnd(ConstraintValidator::validate($value, $constraint, $fieldName));
        }

        return $this->result->isValid();
    }

    protected function getValidationHintForField(FormField $field): ?array
    {
        // @TODO decide if there's a nice way to implement this
        return null;
    }
}
