<?php

namespace Signify\ComposableValidators\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validator;

class TestFormGenerator implements TestOnly
{
    /**
     * Common method for setting up a test form.
     *
     * @param string[] $fieldNames
     * @param Validator|null $validator
     * @param string|null $tab
     * @return Form
     */
    public static function getForm(array $fieldNames = [], ?Validator $validator = null, $tab = null)
    {
        $fieldList = new FieldList();
        if ($tab) {
            $root = explode('.', $tab)[0];
            $fieldList->add(TabSet::create($root));
            $fieldList->findOrMakeTab($tab);
        }
        foreach ($fieldNames as $name => $value) {
            if (is_numeric($name)) {
                $name = $value;
                $value = null;
            }
            $field = TextField::create($name)->setValue($value);
            if ($tab) {
                $fieldList->addFieldToTab($tab, $field);
            } else {
                $fieldList->add($field);
            }
        }

        return new Form(null, 'testForm', $fieldList, new FieldList([/* no actions */]), $validator);
    }
}
