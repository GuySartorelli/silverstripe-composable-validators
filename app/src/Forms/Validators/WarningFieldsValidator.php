<?php

namespace App\Validators;

use SilverStripe\Forms\Validator;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\FileField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Forms\GridField\GridField;

/**
 * Similar to {@link \SilverStripe\Forms\RequiredFields} but produces a warning rather than a validation error.
 * Doesn't validate each form fields - use RequiredFields or SimpleValidator in a MultiValidator for that.
 */
class WarningFieldsValidator extends Validator
{

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

            if ($fieldName instanceof FormField) {
                $formField = $fieldName;
                $fieldName = $fieldName->getName();
            } else {
                $formField = $fields->dataFieldByName($fieldName);
            }

            $value = isset($data[$fieldName]) ? $data[$fieldName] : null;

            // If there are no items in a gridfield's list, or there is on item that isn't saved,
            // the value is considered empty.
            if ($formField instanceof GridField) {
                $value = $formField->getList()->count() ?: null;
                if ($value === 1) {
                    $value = $formField->getList()->first()->ID != 0 ?? null;
                }
            }

            // submitted data for file upload fields come back as an array
            if (is_array($value)) {
                if ($formField instanceof FileField && isset($value['error']) && $value['error']) {
                    $error = true;
                } else {
                    $error = (count($value)) ? false : true;
                }
            } else {
                // assume a string or integer
                $error = (strlen($value)) ? false : true;
            }

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
