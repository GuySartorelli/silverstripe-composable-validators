<?php

namespace Signify\ComposableValidators\Traits;

use InvalidArgumentException;
use SilverStripe\ORM\ArrayLib;

trait ValidatesMultipleFieldsWithConfig
{
    use ValidatesMultipleFields;

    public function __construct(array $fields = [])
    {
        $this->addFields($fields);
        parent::__construct();
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
     * @return $this
     */
    public function addFields(array $fields)
    {
        foreach ($fields as $field => $config) {
            $this->addField($field, $config);
        }
        return $this;
    }

    /**
     * Adds a single field to be validated.
     *
     * @param string $field Name of the field to add as a dependent required field.
     * @param string[] $config The config for the field. See documentation for the validator as to what is valid.
     * @return $this
     */
    public function addField(string $field, array $config)
    {
        if (empty($config)) {
            throw new InvalidArgumentException('$config cannot be empty.');
        }
        $this->fields[$field] = $config;
        return $this;
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
        foreach ($this->getFields() as $fieldName => $config) {
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
}
