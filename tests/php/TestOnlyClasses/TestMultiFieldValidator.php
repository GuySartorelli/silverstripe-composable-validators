<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Traits\ValidatesMultipleFields;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Validator;

class TestMultiFieldValidator extends Validator implements TestOnly
{
    use ValidatesMultipleFields;

    public function php($data)
    {
        return true;
    }

    public function getValidationHintForField(FormField $field): ?array
    {
        return null;
    }
}
