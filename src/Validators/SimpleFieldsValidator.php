<?php

namespace Signify\ComposableValidators\Validators;

use SilverStripe\Forms\Validator;

/**
 * A validator to ensure that all form fields are internally valid.
 *
 * This is intended for use with CompositeValidator and validators that only check
 * the fields that they are responsible for.
 *
 * This class is to avoid the use of, say, RequiredFields::create([]), which
 * relies on an implementation detail to ensure that fields are validated.
 */

class SimpleFieldsValidator extends Validator
{
    /**
     * Check all fields to ensure they are internally valid.
     *
     * @param array $data
     *
     * @return boolean
     */
    public function php($data)
    {
        $valid = true;
        $fields = $this->form->Fields();

        foreach ($fields as $field) {
            if ($field->getOmitFieldValidation()) {
                continue;
            }
            $valid = ($field->validate($this) && $valid);
        }

        return $valid;
    }
}
