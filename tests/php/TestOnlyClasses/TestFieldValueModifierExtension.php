<?php

namespace Signify\ComposableValidators\Tests;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\NumericField;

class TestFieldValueModifierExtension extends Extension
{
    public function updateFieldHasValue(FormField $formField, $value)
    {
        if ($formField instanceof EmailField) {
            return true;
        }
        if ($formField instanceof NumericField) {
            return false;
        }
        return null;
    }
}
