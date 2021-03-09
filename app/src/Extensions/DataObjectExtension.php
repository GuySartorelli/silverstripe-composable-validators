<?php

namespace App\Extensions;

use App\Validators\AjaxCompositeValidator;
use App\Validators\SimpleFieldValidator;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CompositeValidator;

class DataObjectExtension extends Extension
{
    public function updateCMSCompositeValidator(CompositeValidator &$compositeValidator)
    {
        if (!$compositeValidator instanceof AjaxCompositeValidator) {
            $validators = $compositeValidator->getValidators();
            $validators[] = SimpleFieldValidator::create();
            $compositeValidator = AjaxCompositeValidator::create($validators);
        }
    }
}
