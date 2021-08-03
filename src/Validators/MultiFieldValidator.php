<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ArrayLib;

abstract class MultiFieldValidator extends Validator
{
    /**
     * List of fields which will be validated.
     *
     * @var array
     */
    protected $fields = [];

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
     * @return string[] $fields
     */
    public function getFields(): array
    {
        return array_values($this->fields);
    }

    /**
     * Adds multiple fields to be validated.
     *
     * @param string[] $fields
     *
     * @return $this
     */
    public function addFields(array $fields)
    {
        $this->fields = array_merge($this->fields, ArrayLib::valuekey($fields));
        return $this;
    }

    /**
     * Adds a single field to be validated.
     *
     * @param string $field
     *
     * @return $this
     */
    public function addField(string $field)
    {
        $this->fields[$field] = $field;
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
        $this->fields = array();
        return $this;
    }

    /**
     * Add the fields from another {@link MultiFieldValidator}.
     *
     * @param MultiFieldValidator $validator
     * @return $this
     */
    public function appendFields(MultiFieldValidator $validator)
    {
        $this->fields = $this->fields + $validator->getFields();
        return $this;
    }

    public function canBeCached(): bool
    {
        return count($this->getFields()) === 0;
    }

    /**
     * Debug helper
     * @return string
     */
    public function debug()
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
}
