<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Extension;

class FormFieldExtension extends Extension
{
    private $omitFieldValidation = [];

    /**
     * Determine whether this field should be ommitted in SimpleFieldValidator validation.
     *
     * @param bool $omit
     * @return FormField
     */
    public function setOmitFieldValidation(bool $omit)
    {
        $this->omitFieldValidation[$this->owner->getName()] = $omit;
        return $this->owner;
    }

    /**
     * Get whether this field should be ommitted in SimpleFieldValidator validation.
     *
     * @return bool
     */
    public function getOmitFieldValidation(): bool
    {
        if (isset($this->omitFieldValidation[$this->owner->getName()])) {
            return $this->omitFieldValidation[$this->owner->getName()];
        }
        return false;
    }
}
