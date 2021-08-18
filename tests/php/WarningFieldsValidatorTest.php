<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\WarningFieldsValidator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FormField;

class WarningFieldsValidatorTest extends SapphireTest
{
    /**
     * If the warning field has no value, there should be a validation warning message
     * but the validation result should be valid.
     */
    public function testValidationMessageIfEmpty()
    {
        $form = TestFormGenerator::getForm(['FieldOne'], new WarningFieldsValidator(['FieldOne']));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertCount(1, $messages);
        $message = $messages[0];
        $this->assertEquals('warning', $message['messageType']);
        $this->assertEquals(
            '"' . FormField::name_to_label('FieldOne')
            . '" has no value and will not display or be used', $message['message']
        );
    }

    /**
     * If the warning field has a value, there should be no validation warning message.
     */
    public function testNoValidationMessageIfNotEmpty()
    {
        $form = TestFormGenerator::getForm(['FieldOne' => 'someValue'], new WarningFieldsValidator(['FieldOne']));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the warning field doesn't exist, there should be no validation warning message.
     */
    public function testNoValidationMessageIfFieldMissing()
    {
        $form = TestFormGenerator::getForm(['FieldOne'], new WarningFieldsValidator(['MissingField']));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * There should be no validation hints for warning field validation.
     */
    public function testValidationHints()
    {
        TestFormGenerator::getForm(
            [
                'NotRequired',
                'Title',
                'Content',
            ],
            $validator = new WarningFieldsValidator([
                'Title',
                'Content',
                'NotInForm',
            ])
        );
        $this->assertEmpty($validator->getValidationHints());
    }
}
