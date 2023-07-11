<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Extensions\FormFieldExtension;
use Signify\ComposableValidators\Validators\SimpleFieldsValidator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CompositeValidator;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormField;

class SimpleFieldsValidatorTest extends SapphireTest
{
    protected static $required_extensions = [
        FormField::class => [FormFieldExtension::class],
    ];

    private function getForm($value = null, $withValidator = true): Form
    {
        $fieldList = new FieldList([
            $emailField = new EmailField('EmailField'),
        ]);
        $emailField->setValue($value);
        $validator = $withValidator ? new SimpleFieldsValidator() : null;
        return new Form(null, 'testForm', $fieldList, new FieldList([/* no actions */]), $validator);
    }

    /**
     * If the field has an invalid value, there should be a validation error.
     */
    public function testValidationErrorWithInvalidValue(): void
    {
        $form = $this->getForm('not an email address');
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If the field has a valid value, there should be no validation error.
     */
    public function testNoValidationErrorWithValidValue(): void
    {
        $form = $this->getForm('email@example.com');
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the form has just a CompositeValidator, the invalid value shouldn't be validated.
     *
     * This test is basically to check if this validator is even still needed.
     */
    public function testNoValidationErrorWithNoValidator(): void
    {
        $form = $this->getForm('not an email address', false);
        $form->setValidator(new CompositeValidator());
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the field is omitting field validation, the invalid value shouldn't be validated.
     */
    public function testNoValidationErrorWithOmitValidation(): void
    {
        $form = $this->getForm('not an email address');
        $field = $form->Fields()->dataFieldByName('EmailField');
        $this->assertFalse($field->getOmitFieldValidation());
        $field->setOmitFieldValidation(true);
        $this->assertTrue($field->getOmitFieldValidation());
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }
}
