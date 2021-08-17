<?php

namespace Signify\ComposableValidators\Validators;

use Signify\ComposableValidators\Traits\ValidatesMultipleFieldsWithConfig;
use Signify\SearchFilterArrayList\SearchFilterableArrayList;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\Filters\SearchFilter;

/**
 * A validator used to ensure certain required fields have values if their dependencies are met.
 *
 * Configuration arrays for this validator are an array of fields with SearchFilter syntax and the
 * corresponding value(s). In this example, 'StartsWithField' will be required only if the value of 'DependencyField' starts
 * with the string 'some':
 * $validator->addField('StartsWithField', ['DependencyField:StartsWith' => 'some']);
 *
 * This validator is best used within an AjaxCompositeValidator in conjunction with
 * a SimpleFieldsValidator.
 */
class DependentRequiredFieldsValidator extends FieldHasValueValidator
{
    use ValidatesMultipleFieldsWithConfig;

    /**
     * Validates that the required fields have values if their dependencies are met.
     *
     * @param array $data
     * @return boolean
     */
    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        foreach ($this->getFields() as $fieldName => $filter) {
            $isRequired = true;
            $dependenciesMissing = 0;
            $uniqueDependencyFields = [];
            foreach ($filter as $filterKey => $filterValue) {
                $dependencyFieldName = explode(':', $filterKey)[0];
                $uniqueDependencyFields[$dependencyFieldName] = true;
                if (!array_key_exists($dependencyFieldName, $data)) {
                    $dependenciesMissing++;
                    continue;
                }
                $dependencyValue = $data[$dependencyFieldName];
                $tempObj = new \stdClass();
                $tempObj->$dependencyFieldName = $dependencyValue;
                $filterList = SearchFilterableArrayList::create([$tempObj]);
                $isRequired = $filterList->filter($filterKey, $filterValue)->count() !== 0;
                // If field is not required, we can stop processing it.
                if (!$isRequired) {
                    break;
                }
            }

            // If the dependency fields don't exist, the user cannot set them so we pretend the dependency isn't there.
            if ($dependenciesMissing === count($uniqueDependencyFields)) {
                $isRequired = false;
            }

            // Only validate the field if it is required, based on the conditional filter.
            if ($isRequired) {
                $valid = $this->validateField($data, $fields, $fieldName, $filter) && $valid;
            }
        }

        return $valid;
    }

    /**
     * Check if the field has a value, and prepare a validation error if not.
     *
     * @param array $data
     * @param FieldList $fields
     * @param string $fieldName
     * @param array $filter
     * @return boolean True if the field has a value.
     */
    protected function validateField($data, FieldList $fields, string $fieldName, array $filter): bool
    {
        $formField = $this->getFormField($fields, $fieldName);
        if ($formField && !$this->fieldHasValue($data, $formField)) {
            if (!$errorMessage = $formField->getCustomValidationMessage()) {
                $errorMessage = $this->buildValidationMessage(
                    $fields,
                    $this->getFieldLabel($formField),
                    $filter
                );
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
     * Build the validation error message for a field based on its dependency filter.
     *
     * @param FieldList $fields
     * @param string $title
     * @param array $filter
     * @return string
     */
    protected function buildValidationMessage(FieldList $fields, string $title, array $filter): string
    {
        $arrayList = SearchFilterableArrayList::create();
        $dependencies = [];
        foreach ($filter as $filterKey => $filterValue) {
            $filter = $arrayList->createSearchFilter($filterKey, $filterValue);
            $filterClass = get_class($filter);
            $dependencyField = $this->getFormField($fields, $filter->getName());
            $negated = in_array('not', $filter->getModifiers()) ? '_NEGATED' : '';
            $dependencies[] = _t(
                self::class . ".DEPENDENCY_$filterClass" . $negated,
                "[ERROR: '$filterClass' has no appropriate dependency translation string]",
                [
                    'dependency' => strip_tags('"' . $this->getFieldLabel($dependencyField) . '"'),
                    'value' => $this->makeValuesString($filter),
                ]
            );
        }

        $namespace = rtrim(str_replace(ClassInfo::shortName(self::class), '', self::class), '\\');
        $delimiter = _t($namespace . '.DELIMITER_AND', ', and ');
        return _t(
            self::class . '.FIELD_IS_REQUIRED',
            '{name} is required when {dependencies}',
            [
                'name' => strip_tags('"' . $title . '"'),
                'dependencies' => implode($delimiter, $dependencies),
            ]
        );
    }

    /**
     * Make a containing all values for a given dependency filter.
     *
     * @param SearchFilter $filter
     * @return string
     */
    protected function makeValuesString(SearchFilter $filter): string
    {
        $stringArray = [];
        $values = (array)$filter->getValue();
        foreach ($values as $value) {
            if (is_string($value)) {
                $stringArray[] = "'$value'";
                continue;
            }
            if (is_numeric($value)) {
                $stringArray[] = (string)$value;
                continue;
            }
            switch ($value) {
                case true:
                    $value = 'TRUE';
                    break;
                case false:
                    $value = 'FALSE';
                    break;
                case null:
                    $value = 'NULL';
                    break;
            }
            $stringArray[] = $value;
        }
        $namespace = rtrim(str_replace(ClassInfo::shortName(self::class), '', self::class), '\\');
        $valueDelimiter =  _t($namespace . '.DELIMITER_OR', ' or ');
        return implode($valueDelimiter, $stringArray);
    }

    public function getValidationHintForField(FormField $formField): ?array
    {
        $fieldName = $formField->getName();
        $fields = $this->getFields();
        if (array_key_exists($fieldName, $fields)) {
            return [
                'dependencies' => $fields[$fieldName],
            ];
        }
        return null;
    }
}
