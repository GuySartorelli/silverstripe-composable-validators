<?php

namespace Signify\ComposableValidators\Tests;

use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TextField;

class TestFormGenerator implements TestOnly
{
    /**
     * Common method for setting up a test form.
     *
     * @param string[] $fieldNames
     * @return Form
     */
    public static function getForm(array $fieldNames = [])
    {
        $fieldList = new FieldList();
        foreach ($fieldNames as $name => $value) {
            if (is_numeric($name)) {
                $name = $value;
                $value = null;
            }
            $fieldList->add(TextField::create($name)->setValue($value));
        }

        return new Form(null, 'testForm', $fieldList, new FieldList([/* no actions */]));
    }
}
