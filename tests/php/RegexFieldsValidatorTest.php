<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\RegexFieldsValidator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\FieldType\DBField;

class RegexFieldsValidatorTest extends SapphireTest
{
    /**
     * If the value doesn't match the regex pattern, there should be a validation error message.
     */
    public function testValidationMessageIfRegexDoesntMatch()
    {
        $form = TestFormGenerator::getForm(
            ['FieldOne' => 'value1'],
            new RegexFieldsValidator(['FieldOne' => ['/no match/']])
        );
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $message = $messages[0];
        $this->assertEquals('validation', $message['messageType']);
        $this->assertEquals(
            'The value for "' . FormField::name_to_label('FieldOne')
            . '" must match the pattern /no match/', $message['message']
        );
    }

    /**
     * If the value doesn't match any regex pattern, validation error messages should correctly concatenate.
     */
    public function testValidationMessageConcatenation()
    {
        $form = TestFormGenerator::getForm(
            ['FieldOne' => 'value1'],
            new RegexFieldsValidator([
                'FieldOne' => [
                    '/no match/' => 'must not match',
                    '/also not match/' => 'must pass testing',
                ]
            ])
        );
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $message = $messages[0];
        $this->assertEquals('validation', $message['messageType']);
        $this->assertEquals(
            'The value for "' . FormField::name_to_label('FieldOne')
            . '" must not match or must pass testing', $message['message']
        );
    }

    /**
     * If the value matches the regex pattern, there should be no validation error message.
     */
    public function testNoValidationMessageIfRegexMatches()
    {
        $form = TestFormGenerator::getForm(
            ['FieldOne' => 'value1'],
            new RegexFieldsValidator(['FieldOne' => ['/1$/']])
        );
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the value matches ANY regex pattern, there should be no validation error message.
     */
    public function testNoValidationMessageIfRegexMatchesAny()
    {
        $form = TestFormGenerator::getForm(
            ['FieldOne' => 'value1'],
            new RegexFieldsValidator([
                'FieldOne' => [
                    '/no match/',
                    '/1$/',
                    '/no match 2/',
                ],
            ])
        );
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the regex field doesn't exist, there should be no validation error message.
     */
    public function testNoValidationMessageIfFieldMissing()
    {
        $form = TestFormGenerator::getForm(
            ['FieldOne' => 'value1'],
            new RegexFieldsValidator(['MissingField' => ['/no match/']])
        );
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * Objects that can be cast to string should be correctly validated.
     */
    public function testStringableObjectValue()
    {
        TestFormGenerator::getForm(
            ['FieldOne'],
            $validator = new RegexFieldsValidator(['FieldOne' => ['/^Value1$/']])
        );
        $data = ['FieldOne' => DBField::create_field('Varchar', 'Value1')];
        // Valid when it matches.
        $valid = $validator->php($data);
        $this->assertTrue($valid);
        $messages = $validator->getResult()->getMessages();
        $this->assertEmpty($messages);
        // Invalid when it doesn't match.
        $validator->addField('FieldOne', ['/no match/']);
        $valid = $validator->php($data);
        $this->assertFalse($valid);
        $messages = $validator->getResult()->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * Null values should be correctly validated.
     */
    public function testNullValue()
    {
        TestFormGenerator::getForm(
            ['FieldOne'],
            $validator = new RegexFieldsValidator(['FieldOne' => ['/^$/']])
        );
        $data = ['FieldOne' => null];
        // Valid when it matches.
        $valid = $validator->php($data);
        $this->assertTrue($valid);
        $messages = $validator->getResult()->getMessages();
        $this->assertEmpty($messages);
        // Invalid when it doesn't match.
        $validator->addField('FieldOne', ['/no match/']);
        $valid = $validator->php($data);
        $this->assertFalse($valid);
        $messages = $validator->getResult()->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * Numeric values should be correctly validated.
     */
    public function testNumericValue()
    {
        TestFormGenerator::getForm(
            ['FieldOne'],
            $validator = new RegexFieldsValidator(['FieldOne' => ['/12345/']])
        );
        $data = ['FieldOne' => 12345];
        // Valid when it matches.
        $valid = $validator->php($data);
        $this->assertTrue($valid);
        $messages = $validator->getResult()->getMessages();
        $this->assertEmpty($messages);
        // Invalid when it doesn't match.
        $validator->addField('FieldOne', ['/no match/']);
        $valid = $validator->php($data);
        $this->assertFalse($valid);
        $messages = $validator->getResult()->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * Objects that cannot be cast to string should be ignored.
     */
    public function testNonStringableObjectValueIsIgnored()
    {
        TestFormGenerator::getForm(
            ['FieldOne'],
            $validator = new RegexFieldsValidator(['FieldOne' => ['/no match/']])
        );
        $valid = $validator->php(['FieldOne' => new TestUnstringable()]);
        $this->assertTrue($valid);
        $messages = $validator->getResult()->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * Arrays cannot be cast to string and should be ignored.
     */
    public function testArrayValueIsIgnored()
    {
        TestFormGenerator::getForm(
            ['FieldOne'],
            $validator = new RegexFieldsValidator(['FieldOne' => ['/no match/']])
        );
        $valid = $validator->php(['FieldOne' => ['Arbitrary value in an array']]);
        $this->assertTrue($valid);
        $messages = $validator->getResult()->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * All of the fields that are in both the form AND the validator should have the correct 'regex' validation
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
            $validator = new RegexFieldsValidator(
                $configFields = [
                    'Title' => [
                        '/[a-z][A-Z]/' => 'contain any letter',
                    ],
                    'Content' => [
                        '/^some value$/',
                        '/^[\d]$/',
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
            switch ($hint['name']) {
                case 'Title':
                    $this->assertEquals(['/[a-z][A-Z]/'], $hint['regex']);
                    break;
                case 'Content':
                    $this->assertEquals(['/^some value$/', '/^[\d]$/'], $hint['regex']);
                    break;
            }
        }

        // All of the fields that are in BOTH the form and validator should have hints.
        $inFormAndValidator = array_intersect($formFields, array_keys($configFields));
        sort($inFormAndValidator);
        sort($hit);
        $this->assertEquals($inFormAndValidator, $hit);
    }
}
