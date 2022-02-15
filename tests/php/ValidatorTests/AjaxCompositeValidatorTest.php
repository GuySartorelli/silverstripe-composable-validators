<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\AjaxCompositeValidator;
use Signify\ComposableValidators\Validators\RequiredFieldsValidator;
use Signify\ComposableValidators\Validators\SimpleFieldsValidator;
use SilverStripe\Dev\SapphireTest;

// TODO set up behat tests for the actual ajax functionality for front and backend.
class AjaxCompositeValidatorTest extends SapphireTest
{

    public function testAddValidators()
    {
        $compositeValidator = new AjaxCompositeValidator();
        $compositeValidator->addValidators([
            new SimpleFieldsValidator(),
            new RequiredFieldsValidator(),
        ]);

        $this->assertCount(2, $compositeValidator->getValidators());
    }

    public function testGetOrAddValidatorByType()
    {
        $compositeValidator = new AjaxCompositeValidator();
        // Confirm validator starts empty.
        $this->assertCount(0, $compositeValidator->getValidators());
        // One validator should be created and added.
        $simpleFieldsValidator1 = $compositeValidator->getOrAddValidatorByType(SimpleFieldsValidator::class);
        $this->assertCount(1, $compositeValidator->getValidators());
        // The validator previously created should be fetched, rather than instantiating a new one.
        $simpleFieldsValidator2 = $compositeValidator->getOrAddValidatorByType(SimpleFieldsValidator::class);
        $this->assertCount(1, $compositeValidator->getValidators());
        // Confirm both simple fields validators are the exact same instance.
        $this->assertTrue($simpleFieldsValidator1 === $simpleFieldsValidator2);
    }

}
