<?php

namespace Signify\ComposableValidators\Validators;

use InvalidArgumentException;
use Signify\ComposableValidators\Traits\ChecksIfFieldHasValue;
use Signify\SearchFilterArrayList\SearchFilterableArrayList;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\Filters\SearchFilter;

class DependentRequiredFieldsValidator extends Validator
{
    use ChecksIfFieldHasValue;

    /**
     * List of fields which will be validated.
     *
     * @var string[][]
     */
    protected $fields;

    public function __construct(array $fields = [])
    {
        $this->addFields($fields);
        parent::__construct();
    }

    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        foreach ($this->fields as $fieldName => $filter) {
            $isRequired = true;
            foreach ($filter as $filterKey => $filterValue) {
                $dependencyFieldName = explode(':', $filterKey)[0];
                $dependencyValue = isset($data[$dependencyFieldName]) ? $data[$dependencyFieldName] : null;
                $tempObj = new \stdClass();
                $tempObj->$dependencyFieldName = $dependencyValue;
                $filterList = SearchFilterableArrayList::create([$tempObj]);
                $isRequired = $filterList->filter($filterKey, $filterValue)->count() !== 0;
                // If field is not required, we can stop processing it.
                if (!$isRequired) {
                    break;
                }
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
            $dependencies[] = _t(
                self::class . ".DEPENDENCY_$filterClass",
                "[ERROR: '$filterClass' has no appropriate dependency translation string]",
                [
                    // TODO find a way to get the field label instead of the field name for the validation error.
                    'dependency' => strip_tags('"' . $this->getFieldLabel($dependencyField) . '"'),
                    'value' => $this->makeValuesString($filter),
                ]
            );
        }

        $delimiter = _t(self::class . '.DEPENDENCIES_DELIMITER', ', and ');
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
        $valueDelimiter =  _t(self::class . '.DEPENDENCY_VALUE_OR', ' or ');
        return implode($valueDelimiter, $stringArray);
    }

    /**
     * Get the list of fields that will be validated.
     * The key is the field name, the value is the dependency array.
     *
     * @return string[][] $fields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Adds multiple fields to be validated.
     *
     * @param string[][] $fields
     *
     * @return $this
     */
    public function addFields(array $fields)
    {
        foreach ($fields as $field => $dependencies) {
            $this->addField($field, $dependencies);
        }
        return $this;
    }

    /**
     * Adds a single field to be validated.
     *
     * @param string $field Name of the field to add as a dependent required field.
     * @param string[] $dependencies A valid SearchFilter array.
     * example ('StartsWithField' will be required only if the value of 'DependencyField' starts
     * with the string 'some'):
     * addDependentRequiredField('StartsWithField', ['DependencyField:StartsWith' => 'some']);
     *
     * @return $this
     */
    public function addField(string $field, array $dependencies)
    {
        if (empty($dependencies)) {
            throw new InvalidArgumentException('$dependencies cannot be empty.');
        }
        $this->fields[$field] = $dependencies;
        return $this;
    }

    /**
     * Removes a field from the validator.
     *
     * @param string $field
     *
     * @return $this
     */
    public function removeField(string $field)
    {
        unset($this->fields[$field]);
        return $this;
    }

    /**
     * Removes multiple fields from the validator.
     *
     * @param string[] $fields
     *
     * @return $this
     */
    public function removeFields(array $fields)
    {
        foreach ($fields as $field) {
            unset($this->fields[$field]);
        }
        return $this;
    }

    /**
     * Clears all the validation from this object.
     *
     * @return $this
     */
    public function removeValidation()
    {
        parent::removeValidation();
        $this->fields = [];
        return $this;
    }

    /**
     * Add the fields from another {@link MultiFieldValidator}.
     *
     * @param self $validator
     * @return $this
     */
    public function appendFields(DependentRequiredFieldsValidator $validator)
    {
        $this->fields = $this->fields + $validator->getFields();
        return $this;
    }

    public function canBeCached(): bool
    {
        return count($this->getFields()) === 0;
    }
}
