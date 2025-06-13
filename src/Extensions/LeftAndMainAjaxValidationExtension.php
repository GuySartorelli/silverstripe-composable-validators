<?php

namespace Signify\ComposableValidators\Extensions;

use Signify\ComposableValidators\Validators\AjaxCompositeValidator;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;

class LeftAndMainAjaxValidationExtension extends Extension
{
    protected function updateEditForm(?Form $form): void
    {
        if (!$form) {
            return;
        }
        if (!is_a($form->getValidator(), AjaxCompositeValidator::class)) {
            return;
        }
        $form->Actions()->add(
            FormAction::create('app_ajaxValidate')->setValidationExempt(true)->setTemplate('HiddenFormAction')
        );
    }
}
