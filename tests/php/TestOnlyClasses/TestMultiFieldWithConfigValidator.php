<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Traits\ValidatesMultipleFieldsWithConfig;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Validator;

class TestMultiFieldWithConfigValidator extends Validator implements TestOnly
{
    use ValidatesMultipleFieldsWithConfig;

    public function php($data)
    {
        return true;
    }

    public function getValidationHintForField(FormField $field): ?array
    {
        return null;
    }
}
