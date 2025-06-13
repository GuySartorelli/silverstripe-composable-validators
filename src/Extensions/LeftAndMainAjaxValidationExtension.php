<?php

namespace Signify\ComposableValidators\Extensions;

use Signify\ComposableValidators\Validators\AjaxCompositeValidator;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;

/**
 * If the form's validator is an AJAX validator, add a validation-except action.
 * This means we get to control the validation flow, which allows us to avoid
 * unexpected errors that would otherwise occur.
 */
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
            // Name matches the method in FormExtension that gets called to handle ajax validation.
            FormAction::create('app_ajaxValidate')->setValidationExempt(true)->setTemplate('HiddenFormAction')
        );
    }
}
