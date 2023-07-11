<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Extensions\GridFieldMessagesExtension;
use Signify\ComposableValidators\Validators\SimpleFieldsValidator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\ValidationResult;

class GridFieldMessagesExtensionTest extends SapphireTest
{
    protected static $required_extensions = [
        GridField::class => [GridFieldMessagesExtension::class],
    ];

    /**
     * A gridfield that fails validation should display the validation error in the description.
     */
    public function testValidationMessageDisplaysWithMessages(): void
    {
        $gridField = new GridField('testfield', 'testfield', new ArrayList(), new GridFieldConfig());
        $fieldList = new FieldList([$gridField]);
        $validator = new TestValidator();
        $form = new Form(null, "testForm", $fieldList, new FieldList(), $validator);

        $form->validationResult();
        $gridField->Field();
        $this->assertStringContainsString(
            '<p class="message ' . ValidationResult::TYPE_ERROR . '">error</p>',
            $gridField->getDescription()
        );
    }

    /**
     * A gridfield that passes validation should not display a validation error in the description.
     */
    public function testValidationMessageDoesntDisplayWithoutMessages(): void
    {
        $gridField = new GridField('testfield', 'testfield', new ArrayList(), new GridFieldConfig());
        $fieldList = new FieldList([$gridField]);
        $validator = new SimpleFieldsValidator();
        $form = new Form(null, "testForm", $fieldList, new FieldList(), $validator);

        $form->validationResult();
        $gridField->Field();
        $this->assertStringNotContainsString(
            '<p class="message ' . ValidationResult::TYPE_ERROR . '">',
            (string)$gridField->getDescription()
        );
    }
}
