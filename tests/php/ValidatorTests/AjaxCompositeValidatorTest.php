<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Validators\AjaxCompositeValidator;
use Signify\ComposableValidators\Validators\RequiredFieldsValidator;
use Signify\ComposableValidators\Validators\SimpleFieldsValidator;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\Form;

// TODO:
// - set up behat tests for the actual ajax functionality for front and backend.
// - consider setting up integration tests in PHP for AJAX vs non-AJAX requests for validation forms.
class AjaxCompositeValidatorTest extends SapphireTest
{
    private $dataAttribute = 'data-signify-validation-hints';

    /**
     * Validation hints for all validators should be added to the form.
     * If setting a new form, the validation hints should be removed from the old form.
     */
    public function testHasValidatorHints(): void
    {
        $validator = $this->getNewValidatorInstance();
        // Test form has validation hints from all validators.
        $form1 = TestFormGenerator::getForm(['FieldOne', 'FieldTwo'], $validator);
        $this->validateHints($form1, $form1->getAttribute($this->dataAttribute));

        // Also test swapping forms removes from old form and adds to new.
        $form2 = TestFormGenerator::getForm(['FieldOne', 'FieldTwo'], $validator);
        $this->assertNull($form1->getAttribute($this->dataAttribute));
        $this->validateHints($form2, $form2->getAttribute($this->dataAttribute));
    }

    /**
     * Setting setAddValidationHint to false should result in no validation hints being set.
     */
    public function testInstanceOmitsValidatorHints(): void
    {
        $validator = $this->getNewValidatorInstance();
        $validator->setAddValidationHint(false);
        $form = TestFormGenerator::getForm(['FieldOne', 'FieldTwo'], $validator);
        $this->assertNull($form->getAttribute($this->dataAttribute));
    }

    /**
     * Setting add_validation_hint to false should result in no validation hints being set.
     */
    public function testConfigOmitsValidatorHints(): void
    {
        Config::modify()->set(AjaxCompositeValidator::class, 'add_validation_hint', false);
        $validator = $this->getNewValidatorInstance();
        $form = TestFormGenerator::getForm(['FieldOne', 'FieldTwo'], $validator);
        $this->assertNull($form->getAttribute($this->dataAttribute));
        Config::modify()->set(AjaxCompositeValidator::class, 'add_validation_hint', true);
    }

    /**
     * All validators added through the addValidators method should be present in the validator.
     */
    public function testAddValidators(): void
    {
        $compositeValidator = new AjaxCompositeValidator();
        $compositeValidator->addValidators([
            $validator1 = new SimpleFieldsValidator(),
            $validator2 = new RequiredFieldsValidator(),
        ]);

        // Check that two validators are added.
        $this->assertCount(2, $compositeValidator->getValidators());
        // Check that the exact instances exist in the validator.
        $this->assertTrue($validator1 === array_values(
            $compositeValidator->getValidatorsByType(SimpleFieldsValidator::class)
        )[0]);
        $this->assertTrue($validator2 === array_values(
            $compositeValidator->getValidatorsByType(RequiredFieldsValidator::class)
        )[0]);
    }

    /**
     * If there is no validator of that type, one should be created and added to the composite validator.
     * Otherwise the existing validator should be returned.
     */
    public function testGetOrAddValidatorByType(): void
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

    /**
     * Get an instance of AjaxCompositeValidator with two RequiredFieldsValidators.
     *
     * @return AjaxCompositeValidator
     */
    private function getNewValidatorInstance(): AjaxCompositeValidator
    {
        $compositeValidator = new AjaxCompositeValidator();
        $compositeValidator->addValidators([
            new RequiredFieldsValidator('FieldOne'),
            new RequiredFieldsValidator('FieldTwo'),
        ]);
        return $compositeValidator;
    }

    /**
     * Check if the validation hints for a form are correct.
     * Assumes tests are all using the same form setup.
     *
     * @param Form $form The form which has the hints
     * @param string $hints The hints on the form
     */
    private function validateHints(Form $form, string $hints): void
    {
        $hints = json_decode($hints, true);
        $expectedHints = [
            $form->Fields()->dataFieldByName('FieldOne')->ID() => [
                'name' => 'FieldOne',
                'required' => true,
            ],
            $form->Fields()->dataFieldByName('FieldTwo')->ID() => [
                'name' => 'FieldTwo',
                'required' => true,
            ],
        ];
        $this->assertSame($expectedHints, $hints);
    }
}
