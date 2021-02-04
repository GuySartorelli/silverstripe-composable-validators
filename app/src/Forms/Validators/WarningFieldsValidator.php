<?php

namespace App\Validators;

use App\Traits\ChecksIfFieldHasValue;
use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\ORM\ValidationResult;

/**
 * Similar to {@link \App\Validators\RequiredFieldsValidator} but produces a warning rather than a validation error.
 * This is for use within a {@link MultiValidator} in conjunction with a {@link SimpleValidator}.
 */
class WarningFieldsValidator extends Validator
{
    use ChecksIfFieldHasValue;

    /**
     * List of fields which get a warning if empty.
     *
     * @var array
     */
    protected $warning_fields;

    public function __construct()
    {
        $warning_fields = func_get_args();
        if (isset($warning_fields[0]) && is_array($warning_fields[0])) {
            $warning_fields = $warning_fields[0];
        }
        if (!empty($warning_fields)) {
            $this->warning_fields = ArrayLib::valuekey($warning_fields);
        } else {
            $this->warning_fields = array();
        }

        parent::__construct();
    }

    /**
     * Clears all the validation from this object.
     *
     * @return $this
     */
    public function removeValidation()
    {
        parent::removeValidation();
        $this->warning_fields = array();

        return $this;
    }

    public function php($data)
    {
        $warning = false;
        $fields = $this->form->Fields();

        if (!$this->warning_fields) {
            return true;
        }

        foreach ($this->warning_fields as $fieldName) {
            if (!$fieldName) {
                continue;
            }

            $formField = $this->getFormField($fields, $fieldName);
            $error = $this->fieldHasValue($data, $formField, $fieldName);

            if ($formField && $error) {
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

    /**
     * Adds a single warning field to warning fields stack.
     *
     * @param string $field
     *
     * @return $this
     */
    public function addWarningField($field)
    {
        $this->warning_fields[$field] = $field;

        return $this;
    }

    /**
     * Removes a warning field
     *
     * @param string $field
     *
     * @return $this
     */
    public function removeWarningField($field)
    {
        unset($this->warning_fields[$field]);

        return $this;
    }
}
