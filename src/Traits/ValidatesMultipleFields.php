<?php

namespace Signify\ComposableValidators\Traits;

use SilverStripe\Core\ArrayLib;
use SilverStripe\Forms\FormField;

trait ValidatesMultipleFields
{
    /**
     * List of fields which will be validated.
     */
    protected array $fields = [];

    public function __construct()
    {
        $fields = func_get_args();
        if (isset($fields[0]) && is_array($fields[0])) {
            $fields = $fields[0];
        }
        if (!empty($fields)) {
            $this->fields = ArrayLib::valuekey($fields);
        }

        parent::__construct();
    }

    /**
     * Get the list of fields that will be validated.
     *
     * @return string[]
     */
    public function getFields(): array
    {
        return array_values($this->fields);
    }

    /**
     * Adds multiple fields to be validated.
     *
     * @param string[] $fields
     */
    public function addFields(array $fields): self
    {
        $this->fields = array_merge($this->fields, ArrayLib::valuekey($fields));
        return $this;
    }

    /**
     * Adds a single field to be validated.
     */
    public function addField(string $field): self
    {
        $this->fields[$field] = $field;
        return $this;
    }

    /**
     * Removes a field from the validator.
     */
    public function removeField(string $field): self
    {
        unset($this->fields[$field]);
        return $this;
    }

    /**
     * Removes multiple fields from the validator.
     *
     * @param string[] $fields
     */
    public function removeFields(array $fields): self
    {
        foreach ($fields as $field) {
            unset($this->fields[$field]);
        }
        return $this;
    }

    /**
     * Clears all the validation from this object.
     */
    public function removeValidation(): self
    {
        parent::removeValidation();
        $this->fields = [];
        return $this;
    }

    /**
     * Declare that this validator can be cached if there are no fields to validate.
     */
    public function canBeCached(): bool
    {
        return count($this->getFields()) === 0;
    }

    /**
     * Debug helper
     */
    public function debug(): string
    {
        if (!is_array($this->fields)) {
            return false;
        }

        $result = "<ul>";
        foreach ($this->fields as $name) {
            $result .= "<li>$name</li>";
        }

        $result .= "</ul>";
        return $result;
    }

    /**
     * Get an associative array indicating what fields in which tabs (if any)
     * have what validation requirements.
     *
     * @return string[]
     */
    public function getValidationHints(): array
    {
        $fields = $this->form->Fields();
        $hints = [];
        foreach ($this->getFields() as $fieldName) {
            if ($formField = $this->getFormField($fields, $fieldName)) {
                if ($fieldArray = $this->getValidationHintForField($formField)) {
                    if ($tab = $this->getTabForField($formField)) {
                        $fieldArray['tab'] = $tab->ID();
                    }
                    $hints[$formField->ID()]['name'] = $fieldName;
                    $hints[$formField->ID()] = ArrayLib::array_merge_recursive($hints[$formField->ID()], $fieldArray);
                }
            }
        }
        return $hints;
    }

    abstract protected function getValidationHintForField(FormField $field): ?array;
}
