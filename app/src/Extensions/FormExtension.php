<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;

class FormExtension extends Extension
{

    public function app_ajaxValidate($data, $form)
    {
        $msg = null;
        $result = $form->getValidator()->validate(true);
        if ($result->isValid()) {
            $msg = true;
        } else {
            $msg = $result->getMessages();
        }
        $response = new HTTPResponse(json_encode($msg));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }
}
