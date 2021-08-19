<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Extensions\DataObjectDefaultAjaxExtension;
use Signify\ComposableValidators\Validators\AjaxCompositeValidator;
use Signify\ComposableValidators\Validators\SimpleFieldsValidator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;

class DataObjectDefaultAjaxExtensionTest extends SapphireTest
{
    protected static $required_extensions = [
        DataObject::class => [DataObjectDefaultAjaxExtension::class],
    ];

    public function testValidatorIsReplaced()
    {
        $dataObject = new TestElementalBlock();
        $validator = $dataObject->getCMSCompositeValidator();
        $simpleValidators = $validator->getValidatorsByType(SimpleFieldsValidator::class);

        // Should have AjaxCompositeValidator by default.
        $this->assertInstanceOf(AjaxCompositeValidator::class, $validator);
        // Should contain SimpleFieldsValidator by default.
        $this->assertInstanceOf(SimpleFieldsValidator::class, reset($simpleValidators));
        $this->assertCount(1, $simpleValidators);
    }
}
