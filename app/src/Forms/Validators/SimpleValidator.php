<?php
namespace App\Validators;

use SilverStripe\Forms\Validator;
use SilverStripe\Forms\FormField;

/**
 * A validator to ensure that all form fields are internally valid.
 *
 * This is intended for use with MultiValidator and validators that only check
 * the fields that they are responsible for, such as HasOneValidator.
 *
 * This class is to avoid the use of, say, RequiredFields::create([]), which
 * relies on an implementation detail to ensure that fields are validated.
 */
class SimpleValidator extends Validator
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
            $valid = ($field->validate($this) && $valid);
        }

        return $valid;
    }
}
