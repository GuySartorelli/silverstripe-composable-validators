<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Extension;

class FormFieldExtension extends Extension
{
    private $omitFieldValidation = false;

    public function setOmitFieldValidation(bool $omit)
    {
        $this->omitFieldValidation = $omit;
        return $this->owner;
    }

    public function getOmitFieldValidation(): bool
    {
        return $this->omitFieldValidation;
    }
}
