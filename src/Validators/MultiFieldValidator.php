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
    protected $fields;

    public function __construct()
    {
        $fields = func_get_args();
        if (isset($fields[0]) && is_array($fields[0])) {
            $fields = $fields[0];
        }
        if (!empty($fields)) {
            $this->fields = ArrayLib::valuekey($fields);
        } else {
            $this->fields = array();
        }

        parent::__construct();
    }

    /**
     * Get the list of fields that will be validated.
     *
     * @return string[] $fields
     */
    public function getFields()
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
    public function addFields($fields)
    {
        $this->fields = array_merge($this->fields, $fields);

        return $this;
    }

    /**
     * Adds a single field to be validated.
     *
     * @param string $field
     *
     * @return $this
     */
    public function addField($field)
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
    public function removeField($field)
    {
        unset($this->fields[$field]);

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
}
