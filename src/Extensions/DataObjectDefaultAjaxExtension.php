<?php

namespace Signify\ComposableValidators\Extensions;

use Signify\ComposableValidators\Validators\AjaxCompositeValidator;
use Signify\ComposableValidators\Validators\SimpleFieldsValidator;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CompositeValidator;

class DataObjectDefaultAjaxExtension extends Extension
{
    /**
     * Replaces CompositeValidator with AjaxCompositeValidator and ensures a SimpleFieldsValidator is added.
     *
     * @param CompositeValidator $compositeValidator
     */
    public function updateCMSCompositeValidator(CompositeValidator &$compositeValidator)
    {
        if (!$compositeValidator instanceof AjaxCompositeValidator) {
            $validators = $compositeValidator->getValidators();
            $validators[] = SimpleFieldsValidator::create();
            $compositeValidator = AjaxCompositeValidator::create($validators);
        }
    }
}
