<?php

namespace Signify\ComposableValidators\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validation\Validator;

class TestFormGenerator implements TestOnly
{
    /**
     * Common method for setting up a test form.
     *
     * @param string[] $fieldNames
     */
    public static function getForm(array $fieldNames = [], ?Validator $validator = null, ?string $tab = null): Form
    {
        $fieldList = new FieldList();
        if ($tab) {
            $root = explode('.', $tab)[0];
            $fieldList->add(new TabSet($root));
            $fieldList->findOrMakeTab($tab);
        }
        foreach ($fieldNames as $name => $value) {
            if (is_numeric($name)) {
                $name = $value;
                $value = null;
            }
            $field = new TextField($name);
            $field->setValue($value);
            if ($tab) {
                $fieldList->addFieldToTab($tab, $field);
            } else {
                $fieldList->add($field);
            }
        }

        return new Form(null, 'testForm', $fieldList, new FieldList([/* no actions */]), $validator);
    }
}
