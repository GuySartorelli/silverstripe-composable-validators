<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\SimpleFieldsValidator;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\ArrayList;

class FieldHasValueValidatorTest extends FunctionalTest
{
    protected $usesDatabase = true;

    protected static $extra_dataobjects = [
        TestSiteTree::class,
    ];

    protected static $extra_controllers = [
        TestValidationController::class,
    ];

    private function submitTestForm($fields, $validateFields, $data)
    {
        $controller = Controller::singleton(TestValidationController::class);
        TestValidationController::$fields = $fields;
        TestValidationController::$validators[] = new SimpleFieldsValidator();
        TestValidationController::$validators[] = $validator = new TestFieldHasValueValidator();
        $validator->fields = $validateFields;
        $this->get($controller->Link());
        $data = array_merge($data, ['action_submit' => 1]);
        return $this->post($controller->Link('testForm'), $data);
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        TestSiteTree::create()->publishSingle();
    }

    public function testSubmittedTextFieldValueDetection(): void
    {
        $result = $this->submitTestForm(
            [new TextField('TextFieldError')],
            ['TextFieldError'],
            ['TextFieldError' => '']
        );
        $this->assertNotEquals('success', $result->getBody());

        $result = $this->submitTestForm(
            [new TextField('TextFieldPass')],
            ['TextFieldPass'],
            ['TextFieldPass' => 'someValue']
        );
        $this->assertEquals('success', $result->getBody());
    }

    public function testSubmittedSelectFieldValueDetection(): void
    {
        $result = $this->submitTestForm(
            [new DropdownField('DropdownFieldError', null, [1,2,3])],
            ['DropdownFieldError'],
            ['DropdownFieldError' => '']
        );
        $this->assertNotEquals('success', $result->getBody());

        $result = $this->submitTestForm(
            [new DropdownField('DropdownFieldPass', null, [1,2,3])],
            ['DropdownFieldPass'],
            ['DropdownFieldPass' => '1']
        );
        $this->assertEquals('success', $result->getBody());
    }

    public function testSubmittedMultiSelectFieldValueDetection(): void
    {
        $result = $this->submitTestForm(
            [new ListboxField('ListboxFieldError', null, [1,2,3])],
            ['ListboxFieldError'],
            ['ListboxFieldError' => '']
        );
        $this->assertNotEquals('success', $result->getBody());

        $result = $this->submitTestForm(
            [new ListboxField('ListboxFieldPass', null, [1,2,3])],
            ['ListboxFieldPass'],
            ['ListboxFieldPass' => ['1', '2']]
        );
        $this->assertEquals('success', $result->getBody());
    }

    public function testSubmittedDatetimeFieldValueDetection(): void
    {
        $result = $this->submitTestForm(
            [new DatetimeField('DatetimeFieldError')],
            ['DatetimeFieldError'],
            ['DatetimeFieldError' => '']
        );
        $this->assertNotEquals('success', $result->getBody());

        $result = $this->submitTestForm(
            [new DatetimeField('DatetimeFieldPass')],
            ['DatetimeFieldPass'],
            ['DatetimeFieldPass' => '2021-08-30T17:02:35']
        );
        $this->assertEquals('success', $result->getBody());
    }

    public function testSubmittedTreeDropdownFieldValueDetection(): void
    {
        $result = $this->submitTestForm(
            [new TreeDropdownField('TreeDropdownFieldError', null, SiteTree::class)],
            ['TreeDropdownFieldError'],
            ['TreeDropdownFieldError' => '0']
        );
        $this->assertNotEquals('success', $result->getBody());

        $result = $this->submitTestForm(
            [new TreeDropdownField('TreeDropdownFieldPass', null, SiteTree::class)],
            ['TreeDropdownFieldPass'],
            ['TreeDropdownFieldPass' => '1']
        );
        $this->assertEquals('success', $result->getBody());
    }

    public function testSubmittedGridFieldValueDetection(): void
    {
        $emptyList = new ArrayList();
        $emptyList->setDataClass(SiteTree::class);
        $result = $this->submitTestForm(
            [new GridField('GridFieldError', null, $emptyList)],
            ['GridFieldError'],
            ['GridFieldError' => [1]]
        );
        $this->assertNotEquals('success', $result->getBody());

        $result = $this->submitTestForm(
            [new GridField('GridFieldPass', null, SiteTree::get())],
            ['GridFieldPass'],
            ['GridFieldPass' => [1]]
        );
        $this->assertEquals('success', $result->getBody());
    }

    /**
     * If an extension class implements updateFieldHasValue and returns a boolean value, that
     * value should be respected. If it returns null, the normal value checking logic proceeds.
     */
    public function testUpdateFieldHasValue(): void
    {
        TestFieldHasValueValidator::add_extension(TestFieldValueModifierExtension::class);

        $data = [
            'EmailField' => null,
            'NumericField' => 123456,
            'TextField1' => null,
            'TextField2' => 'value',
        ];
        $validator = new TestFieldHasValueValidator();
        $emailField = new EmailField('EmailField');
        $numericField = new NumericField('NumericField');
        $textField1 = new TextField('TextField1');
        $textField2 = new TextField('TextField2');

        $this->assertTrue($validator->checkfieldHasValue($data, $emailField));
        $this->assertFalse($validator->checkfieldHasValue($data, $numericField));
        $this->assertFalse($validator->checkfieldHasValue($data, $textField1));
        $this->assertTrue($validator->checkfieldHasValue($data, $textField2));

        TestFieldHasValueValidator::remove_extension(TestFieldValueModifierExtension::class);
    }
}
