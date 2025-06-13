<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;

class FormExtension extends Extension
{
    /**
     * The actual action used to trigger AJAX validation.
     */
    public function app_ajaxValidate(array $data, Form $form): HTTPResponse
    {
        $msg = null;
        $form->getRequestHandler()->setButtonClicked(null);
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
}
