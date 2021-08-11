<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Extension;

class FormFieldExtension extends Extension
{
    private $omitFieldValidation = false;

    /**
     * Determine whether this field should be ommitted in SimpleFieldValidator validation.
     *
     * @param boolean $omit
     * @return FormField
     */
    public function setOmitFieldValidation(bool $omit)
    {
        $this->omitFieldValidation = $omit;
        return $this->owner;
    }

    /**
     * Get whether this field should be ommitted in SimpleFieldValidator validation.
     *
     * @return boolean
     */
    public function getOmitFieldValidation(): bool
    {
        return $this->omitFieldValidation;
    }
}
