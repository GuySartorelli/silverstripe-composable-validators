<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\RequiredFieldsValidator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FormField;

class RequiredFieldsValidatorTest extends SapphireTest
{
    /**
     * If the required field has no value, there should be a validation error message
     * and the validation result should be invalid.
     */
    public function testValidationMessageIfEmpty()
    {
        $form = TestFormGenerator::getForm(['FieldOne'], new RequiredFieldsValidator(['FieldOne']));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertCount(1, $messages);
        $message = $messages[0];
        $this->assertEquals('required', $message['messageType']);
        $this->assertEquals(
            '"' . FormField::name_to_label('FieldOne')
            . '" is required', $message['message']
        );
    }

    /**
     * If the required field has a value, there should be no validation error message.
     */
    public function testNoValidationMessageIfNotEmpty()
    {
        $form = TestFormGenerator::getForm(['FieldOne' => 'someValue'], new RequiredFieldsValidator(['FieldOne']));
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
        $form = TestFormGenerator::getForm(['FieldOne'], new RequiredFieldsValidator(['MissingField']));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * All of the fields that are in both the form AND the validator should have 'required' validation hints.
     */
    public function testValidationHints()
    {
        $form = TestFormGenerator::getForm(
            $formFields = [
                'NotRequired',
                'Title',
                'Content',
            ],
            $validator = new RequiredFieldsValidator(
                $fieldNames = [
                    'Title',
                    'Content',
                    'NotInForm',
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
            $this->assertTrue($hint['required']);
        }

        // All of the fields that are in BOTH the form and validator should have hints.
        $inFormAndValidator = array_intersect($formFields, $fieldNames);
        sort($inFormAndValidator);
        sort($hit);
        $this->assertEquals($inFormAndValidator, $hit);
    }

    /**
     * Required fields need to 'know' they are required.
     */
    public function testFieldIsRequired()
    {
        // Get the validator.
        $validator = new RequiredFieldsValidator(
            $fieldNames = [
                'Title',
                'Content',
                'Image',
                'AnotherField'
            ]
        );

        foreach ($fieldNames as $field) {
            $this->assertTrue(
                $validator->fieldIsRequired($field),
                sprintf("Failed to find '%s' field in required list", $field)
            );
        }

        // Add a new field.
        $validator->addField('ExtraField1');
        // Check the new field is required.
        $this->assertTrue(
            $validator->fieldIsRequired('ExtraField1'),
            "Failed to find 'ExtraField1' field in required list after adding it to the list"
        );
        // Check a non-existent field returns false.
        $this->assertFalse(
            $validator->fieldIsRequired('DoesntExist'),
            "Unexpectedly returned true for a non-existent field"
        );
    }
}
