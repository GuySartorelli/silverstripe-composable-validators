<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\Validator;

/**
 * A validator to ensure that all form fields are internally valid.
 *
 * This is intended for use with AjaxCompositeValidator and other validators which only
 * perform specific validity checks.
 *
 * This class is to avoid the use of, say, RequiredFields::create([]), which
 * relies on an implementation detail to ensure that fields are validated.
 */

class SimpleFieldsValidator extends Validator
{
    /**
     * Array of FormField subclasses that shouldn't be validated in AJAX validation calls.
     *
     * @var string[]
     * @config
     */
    private static $ignore_field_classes_on_ajax = [];

    /**
     * Check all fields to ensure they are internally valid.
     *
     * @param array $data
     * @return bool
     */
    public function php($data, bool $isAjax = false)
    {
        $valid = true;
        $fields = $this->form->Fields();

        $ignoreFieldClasses = (array)$this->config()->get('ignore_field_classes_on_ajax');
        foreach ($fields as $field) {
            if (
                ($field->hasMethod('getOmitFieldValidation') && $field->getOmitFieldValidation())
                || ($isAjax && in_array(get_class($field), $ignoreFieldClasses))
            ) {
                continue;
            }
            $valid = ($field->validate($this) && $valid);
        }

        return $valid;
    }

    public function validate(bool $isAjax = false)
    {
        $this->resetResult();
        if ($this->getEnabled()) {
            $this->php($this->form->getData(), $isAjax);
        }
        return $this->result;
    }
}
