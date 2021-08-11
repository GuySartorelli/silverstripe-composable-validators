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
            // Replace the CompositeValidator with an AjaxCompositeValidator
            $validators = $compositeValidator->getValidators();
            $compositeValidator = AjaxCompositeValidator::create($validators);
            // Ensure a SimpleFieldsValidator is added if one wasn't already there.
            $compositeValidator->getOrAddValidatorByType(SimpleFieldsValidator::class);
        }
    }
}
