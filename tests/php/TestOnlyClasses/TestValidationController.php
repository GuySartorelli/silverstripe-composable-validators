<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\AjaxCompositeValidator;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;

class TestValidationController extends Controller implements TestOnly
{
    public static $fields = [];
    public static $validators = [];

    private static $url_segment = 'test-controller';

    private static $allowed_actions = [
        'testForm',
        'submit',
    ];

    public function index()
    {
        return [
            'Form' => $this->testForm(),
        ];
    }

    public function testForm()
    {
        return new Form(
            $this,
            'testForm',
            $this->getFields(),
            new FieldList([
                new FormAction('submit'),
            ]),
            $this->getValidator()
        );
    }

    public function getFields()
    {
        $fields = new FieldList();
        foreach (static::$fields as $field) {
            $fields->add($field);
        }
        return $fields;
    }

    public function getValidator()
    {
        $validator = new AjaxCompositeValidator();
        $validator->addValidators(static::$validators);
        return $validator;
    }

    public function submit()
    {
        return 'success';
    }
}
