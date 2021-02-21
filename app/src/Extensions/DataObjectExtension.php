<?php

namespace App\Extensions;

use App\Validators\AjaxCompositeValidator;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CompositeValidator;

class DataObjectExtension extends Extension
{
    public function updateCMSCompositeValidator(CompositeValidator &$compositeValidator)
    {
        if (!$compositeValidator instanceof AjaxCompositeValidator) {
            $validators = $compositeValidator->getValidators();
            $compositeValidator = AjaxCompositeValidator::create($validators);
        }
    }
}
