<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\DependentRequiredFieldsValidator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;

class DependentRequiredFieldsValidatorTest extends SapphireTest
{
    /**
     * If the dependency is not met, the field should not be required.
     *
     * Note we do not need to test all SearchFilter dependency combinations, as
     * that functionality comes from the signify-nz/silverstripe-searchfilter-arraylist
     * module which has unit tests for that.
     */
    public function testFieldNotRequiredIfDependencyNotMet()
    {
        $form = TestFormGenerator::getForm(
            [
                'FieldOne',
                'FieldTwo',
            ],
            new DependentRequiredFieldsValidator(['FieldOne' => ['FieldTwo' => 'SomeValue']])
        );
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the dependency is met, the field should be required.
     *
     * Note we do not need to test all SearchFilter dependency combinations, as
     * that functionality comes from the signify-nz/silverstripe-searchfilter-arraylist
     * module which has unit tests for that.
     */
    public function testFieldRequiredIfDependencyMet()
    {
        $form = TestFormGenerator::getForm(
            [
                'FieldOne',
                'FieldTwo' => 'SomeValue',
            ],
            new DependentRequiredFieldsValidator(['FieldOne' => ['FieldTwo:StartsWith:nocase' => 'some']])
        );
        // Check that when the required field is empty, there is a validation error.
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertCount(1, $messages);
        $message = $messages[0];
        $this->assertEquals('required', $message['messageType']);

        // Check that when the required field has a value, there is no validation error.
        $form->Fields()->dataFieldByName('FieldOne')->setValue('anything');
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the nullish dependency is met, the field should be required.
     *
     * Note we do not need to test all SearchFilter dependency combinations, as
     * that functionality comes from the signify-nz/silverstripe-searchfilter-arraylist
     * module which has unit tests for that.
     */
    public function testFieldRequiredIfNullishDependencyMet()
    {
        $form = TestFormGenerator::getForm(
            [
                'FieldOne',
                'FieldTwo',
            ],
            new DependentRequiredFieldsValidator(['FieldOne' => ['FieldTwo' => null]])
        );
        // Check that when the required field is empty, there is a validation error.
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertCount(1, $messages);
        $message = $messages[0];
        $this->assertEquals('required', $message['messageType']);
    }

    /**
     * If the dependency field is missing, the field should not be required.
     * There's no way for the user to set the field if the field doesn't exist.
     */
    public function testFieldNotRequiredIfDependencyIsMissing()
    {
        $form = TestFormGenerator::getForm(
            [
                'FieldOne',
            ],
            new DependentRequiredFieldsValidator(['FieldOne' => ['MissingField' => null]])
        );
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the required field doesn't exist, there should be no validation error message.
     */
    public function testNoValidationMessageIfFieldMissing()
    {
        $form = TestFormGenerator::getForm(
            [
                'FieldOne',
                'FieldTwo',
            ],
            new DependentRequiredFieldsValidator(['MissingField' => ['FieldTwo' => null]])
        );
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * Check that validation messages are correctly constructed to reflect the dependencies.
     */
    public function testSearchFilterValidationMessages()
    {
        $fields = $this->setupMessageFields();
        $validator = new DependentRequiredFieldsValidator([
            'DependentField1' => ['NullField' => [null]],
            'DependentField2' => ['TrueField' => true],
            'DependentField3' => ['FalseField' => false],
            'DependentField4' => [
                'StringField:StartsWith' => 'str',
                'StringField:PartialMatch:not' => 'nothing',
                'StringField:EndsWith' => [
                    'ing',
                    'blah blah',
                ],
            ],
            'DependentField5' => ['NumericField:GreaterThan' => 0],
        ]);
        $form = new Form(null, 'testForm', $fields, new FieldList([/* no actions */]), $validator);
        $expectedMessages = $this->setupExpectedMessages($fields);
        $messages = $form->validationResult()->getMessages();
        foreach ($messages as $message) {
            $this->assertEquals($expectedMessages[$message['fieldName']], $message['message']);
        }
    }

    /**
     * Prepare the fields for the testSearchFilterValidationMessages method.
     *
     * @return FieldList
     */
    private function setupMessageFields()
    {
        return FieldList::create([
            TextField::create('DependentField1'),
            TextField::create('DependentField2'),
            TextField::create('DependentField3'),
            TextField::create('DependentField4'),
            TextField::create('DependentField5'),
            TextField::create('NullField')->setValue(null),
            CheckboxField::create('TrueField')->setValue(true),
            CheckboxField::create('FalseField')->setValue(false),
            TextField::create('StringField')->setValue('string'),
            NumericField::create('NumericField')->setValue(123),
        ]);
    }

    /**
     * Prepare the expected messages for the testSearchFilterValidationMessages method.
     *
     * @param FieldList $fields
     * @return string[]
     */
    private function setupExpectedMessages($fields)
    {
        $messages = [];
        $fieldName = 'DependentField1';
        $field = $fields->dataFieldByName($fieldName);
        $nullField = $fields->dataFieldByName('NullField');
        $messages[$fieldName] = '"' . $field->Title() . '" is required when the value for "'
            . $nullField->Title() . '" is NULL';

        $fieldName = 'DependentField2';
        $field = $fields->dataFieldByName($fieldName);
        $trueField = $fields->dataFieldByName('TrueField');
        $messages[$fieldName] = '"' . $field->Title() . '" is required when the value for "'
            . $trueField->Title() . '" is TRUE';

        $fieldName = 'DependentField3';
        $field = $fields->dataFieldByName($fieldName);
        $falseField = $fields->dataFieldByName('FalseField');
        $messages[$fieldName] = '"' . $field->Title() . '" is required when the value for "'
            . $falseField->Title() . '" is FALSE';

        $fieldName = 'DependentField4';
        $field = $fields->dataFieldByName($fieldName);
        $stringFieldTitle = $fields->dataFieldByName('StringField')->Title();
        $messages[$fieldName] = '"' . $field->Title() . "\" is required when the value for \"$stringFieldTitle\" starts with"
            . " 'str', and the value for \"$stringFieldTitle\" does not contain 'nothing', and the"
            . " value for \"$stringFieldTitle\" ends with 'ing' or 'blah blah'";

        $fieldName = 'DependentField5';
        $field = $fields->dataFieldByName($fieldName);
        $numericField = $fields->dataFieldByName('NumericField');
        $messages[$fieldName] = '"' . $field->Title() . '" is required when the value for "'
            . $numericField->Title() . '" is greater than 0';

        return $messages;
    }

    /**
     * All of the fields that are in both the form AND the validator should have the correct 'dependencies' validation
     * hints.
     */
    public function testValidationHints()
    {
        $form = TestFormGenerator::getForm(
            $formFields = [
                'NotRequired',
                'Title',
                'Content',
            ],
            $validator = new DependentRequiredFieldsValidator(
                $configFields = [
                    'Title' => [
                        'NotRequired' => 'some value',
                    ],
                    'Content' => [
                        'NotRequired:PartialMatch:not:nocase' => 'some value1',
                    ],
                    'NotInForm' => [
                        'NotRequired' => 'some value',
                    ],
                ]
            ),
            'Root.Test'
        );

        $hit = [];
        foreach ($validator->getValidationHints() as $id => $hint) {
            $hit[] = $hint['name'];
            // The tab ID must be included in the validation hints.
            $this->assertEquals('Root_Test', $hint['tab']);
            // The key for the field's hint should be its HTML element id.
            $this->assertEquals($form->Fields()->dataFieldByName($hint['name'])->ID(), $id);
            // Every field in the form that is required should have a 'required' validation hint.
            if ($hint['name'] == 'Content') {
                $this->assertEquals(['NotRequired:PartialMatch:not:nocase' => 'some value1'], $hint['dependencies']);
            } else {
                $this->assertEquals(['NotRequired' => 'some value'], $hint['dependencies']);
            }
        }

        // All of the fields that are in BOTH the form and validator should have hints.
        $inFormAndValidator = array_intersect($formFields, array_keys($configFields));
        sort($inFormAndValidator);
        sort($hit);
        $this->assertEquals($inFormAndValidator, $hit);
    }
}
