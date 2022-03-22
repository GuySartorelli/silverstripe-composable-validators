<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ValidatesMultipleFieldsWithConfig;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\FormField;

/**
 * A validator used to require field values to match a specific regex pattern.
 * Often it will make sense to have this validation inside a custom FormField implementation,
 * but for one-of specific pattern validation of fields that don't warrant their own FormField
 * this validator is perfect.
 *
 * Configuration arrays for this validator are an array of regex patterns and the validation
 * message to display if that pattern is not matched. Note that the message will already be prefixed
 * with a generic portion of the message like 'The value for "NotOnlyNumbers" field '. For example:
 * $validator->addField('NotOnlyNumbersField', ['/(?!^\d+$)^.*?$/' => 'must not consist entirely of numbers']);
 *
 * This validator is best used within an AjaxCompositeValidator in conjunction with
 * a SimpleFieldsValidator.
 */
class RegexFieldsValidator extends BaseValidator
{
    use ValidatesMultipleFieldsWithConfig;

    /**
     * Validates that the fields match their regular expressions.
     *
     * @param array $data
     * @return bool
     */
    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        foreach ($this->getFields() as $fieldName => $regexArray) {
            if (!$field = $this->getFormField($fields, $fieldName)) {
                continue;
            }
            $hasMatch = false;
            $messages = [];
            $value = isset($data[$fieldName]) ? $data[$fieldName] : null;
            // If the value cannot be a string, ignore it.
            if (!$this->valueCanBeString($value)) {
                continue;
            }
            // Check if the value matches at least one regex pattern.
            foreach ($regexArray as $regex => $validationMessage) {
                if (is_numeric($regex)) {
                    $regex = $validationMessage;
                    $validationMessage = _t(
                        self::class . '.DEFAULT_PATTERN_MISMATCH',
                        'must match the pattern {regex}',
                        ['regex' => $regex]
                    );
                }
                if (preg_match($regex, (string)$value)) {
                    $hasMatch = true;
                    break;
                }
                $messages[] = $validationMessage;
            }
            // If there was no match, mark the error.
            if (!$hasMatch) {
                $valid = false;
                $fieldLabel = '"' . $this->getFieldLabel($field) . '"';
                $namespace = rtrim(str_replace(ClassInfo::shortName(self::class), '', self::class), '\\');
                $delimiter = _t($namespace . '.DELIMITER_OR', ' or ');
                $errorMessage = _t(
                    self::class . '.VALIDATION_ERROR',
                    'The value for {name} {requirements}',
                    [
                        'name' => $fieldLabel,
                        'requirements' => implode($delimiter, $messages),
                    ]
                );
                $this->validationError(
                    $fieldName,
                    $errorMessage,
                    'validation'
                );
            }
        }

        return $valid;
    }

    /**
     * Check if a value can be casted to string.
     *
     * @param mixed $value
     * @return bool
     */
    protected function valueCanBeString($value)
    {
        return $value === null || is_scalar($value) || (is_object($value) && method_exists($value, '__toString'));
    }

    public function getValidationHintForField(FormField $formField): ?array
    {
        $fieldName = $formField->getName();
        $fields = $this->getFields();
        if (array_key_exists($fieldName, $fields)) {
            $regex = [];
            foreach ($fields[$fieldName] as $expression => $config) {
                if (is_numeric($expression)) {
                    $regex[] = $config;
                } else {
                    $regex[] = $expression;
                }
            }
            return [
                'regex' => $regex,
            ];
        }
        return null;
    }
}
