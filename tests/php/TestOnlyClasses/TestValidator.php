<?php

namespace Signify\ComposableValidators\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\Validator;

/**
 * Simple validator that always fails in the same way.
 */
class TestValidator extends Validator implements TestOnly
{

    /**
     * Requires a specific field for test purposes.
     *
     * @param array $data
     * @return null
     */
    public function php($data)
    {
        foreach ($data as $field => $data) {
            $this->validationError($field, 'error');
        }

        return null;
    }

    /**
     * Allow us to access the form for test purposes.
     *
     * @return Form|null
     */
    public function getForm(): ?Form
    {
        return $this->form;
    }
}
