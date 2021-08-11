<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;

class FormExtension extends Extension
{
    /**
     * The actual action used to trigger AJAX validation.
     *
     * @param array $data
     * @param Form $form
     * @return HTTPResponse
     */
    public function app_ajaxValidate($data, $form)
    {
        $msg = null;
        $result = $form->getValidator()->validate(true);
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
