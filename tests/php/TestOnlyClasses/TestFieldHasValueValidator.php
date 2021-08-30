<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\FieldHasValueValidator;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FormField;

/**
 * Simple validator that provides public access to FieldHasValueValidator methods for testing.
 */
class TestFieldHasValueValidator extends FieldHasValueValidator implements TestOnly
{
    public $fields = [];
    /**
     * Provides public access to fieldHasValue() for testing.
     *
     * @param array $data
     * @param FormField $formField
     * @return bool
     */
    public function checkfieldHasValue(array $data, FormField $formField)
    {
        return $this->fieldHasValue($data, $formField);
    }

    public function php($data)
    {
        $valid = true;
        $formFields = $this->form->Fields();
        foreach ($this->fields as $fieldName) {
            $formField = $this->getFormField($formFields, $fieldName);
            if ($formField && !$this->fieldHasValue($data, $formField)) {
                $valid = false;
                $this->validationError($fieldName, 'error');
            }
        }

        return $valid;
    }

    public function getValidationHints(): array
    {
        return [];
    }
}
