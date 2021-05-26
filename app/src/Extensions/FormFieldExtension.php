<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Extension;

class FormFieldExtension extends Extension
{
    private $omitSimpleValidation = false;

    public function setOmitSimpleValidation(bool $omit)
    {
        $this->omitSimpleValidation = $omit;
        return $this->owner;
    }

    public function getOmitSimpleValidation()
    {
        return $this->omitSimpleValidation;
    }
}
