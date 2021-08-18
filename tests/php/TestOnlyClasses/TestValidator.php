<?php

namespace Signify\ComposableValidators\Tests;

use SilverStripe\Dev\TestOnly;
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
     * @return false
     */
    public function php($data)
    {
        foreach ($data as $field => $data) {
            $this->validationError($field, 'error');
        }

        return false;
    }
}
