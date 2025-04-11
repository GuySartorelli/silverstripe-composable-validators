<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FormField;

/**
 * @extends Extension<FormField>
 */
class FormFieldExtension extends Extension
{
    private array $omitFieldValidation = [];

    /**
     * Determine whether this field should be ommitted in SimpleFieldValidator validation.
     */
    public function setOmitFieldValidation(bool $omit): FormField
    {
        $owner = $this->getOwner();
        $this->omitFieldValidation[$owner->getName()] = $omit;
        return $owner;
    }

    /**
     * Get whether this field should be ommitted in SimpleFieldValidator validation.
     */
    public function getOmitFieldValidation(): bool
    {
        $ownerName = $this->getOwner()->getName();
        if (isset($this->omitFieldValidation[$ownerName])) {
            return $this->omitFieldValidation[$ownerName];
        }
        return false;
    }
}
