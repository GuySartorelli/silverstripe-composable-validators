<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\Validator;
use SilverStripe\Forms\FormField;

/**
 * Ensure that has-one fields actually have a value.
 *
 * Requires a class like SimpleFieldsValidator to check all fields for internal
 * consistency.
 *
 * This class is essentially a RequiredFields for $has_one fields.
 */
class HasOneValidator extends Validator
{

    /** @var string[] */
    private $fields;

    /**
     * Constructor
     *
     * @param string[] $fields  A list of has-one fields, e.g. 'PromoImageID'.
     */
    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    /**
     * Validate that specified has-one fields actually has-one.
     *
     * This DOES NOT validate the rest of the form. Use SimpleValidator  with
     * CompositeValidator for that.
     *
     * Most of this code comes from RequiredFields::php().
     *
     * {@inheritDoc}
     * @see \SilverStripe\Forms\Validator::php()
     * @see \SilverStripe\Forms\RequiredFields::php()
     */
    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        foreach ($this->fields as $fieldName) {
            if ($fieldName instanceof FormField) {
                $formField = $fieldName;
                $fieldName = $fieldName->getName();
            } else {
                $formField = $fields->dataFieldByName($fieldName);
            }

            if (!isset($data[$fieldName]) || $data[$fieldName] == 0) {
                $errorMessage = _t(
                    'SilverStripe\\Forms\\Form.FIELDISREQUIRED',
                    '{name} is required',
                    array(
                        'name' => strip_tags(
                            '"' . ($formField->Title() ? $formField->Title() : $fieldName) . '"'
                        )
                    )
                );

                if ($msg = $formField->getCustomValidationMessage()) {
                    $errorMessage = $msg;
                }

                $this->validationError(
                    $fieldName,
                    $errorMessage,
                    "required"
                );
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Adds multiple has one fields to fields stack.
     *
     * @param string[] $fields
     *
     * @return $this
     */
    public function addHasOneFields($fields)
    {
        $this->fields = array_merge($this->fields, $fields);

        return $this;
    }

    /**
     * Adds a single has one field to fields stack.
     *
     * @param string $field
     *
     * @return $this
     */
    public function addHasOneField($field)
    {
        $this->fields[$field] = $field;

        return $this;
    }

    /**
     * Removes a has one field
     *
     * @param string $field
     *
     * @return $this
     */
    public function removeHasOneField($field)
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
