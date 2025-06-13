<?php

namespace Signify\ComposableValidators\Extensions;

use Signify\ComposableValidators\Validators\AjaxCompositeValidator;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormField;

class FormExtension extends Extension
{
    /**
     * The actual action used to trigger AJAX validation.
     */
    public function app_ajaxValidate(array $data, Form $form): HTTPResponse
    {
        $msg = null;
        $form->getRequestHandler()->setButtonClicked(null);
        $this->removeSpecificFormFields($form);
        $result = $form->validate();
        $form->getRequestHandler()->setButtonClicked('action_app_ajaxValidate');
        if ($result->isValid()) {
            $msg = true;
        } else {
            $msg = $result->getMessages();
        }
        $response = HTTPResponse::create(json_encode($msg));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    private function removeSpecificFormFields(Form $form): void
    {
        $removeFieldClasses = AjaxCompositeValidator::config()->get('remove_before_ajax_validation');
        if (empty($removeFieldClasses)) {
            return;
        }
        $fieldsToRemove = [];
        $form->Fields()->recursiveWalk(function (FormField $field) use (&$fieldsToRemove, $removeFieldClasses) {
            foreach ($removeFieldClasses as $class) {
                if (is_a($field, $class)) {
                    $fieldsToRemove[] = $field->getName();
                }
            }
        });
        if (empty($fieldsToRemove)) {
            return;
        }
        $form->Fields()->removeByName($fieldsToRemove);
    }
}
