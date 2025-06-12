<?php

namespace Signify\ComposableValidators\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Signify\ComposableValidators\Validators\ConstraintsValidator;
use SilverStripe\Dev\SapphireTest;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConstraintsValidatorTest extends SapphireTest
{
    public static function provideValidation(): array
    {
        return [
            [
                'fields' => ['FieldOne' => 'someValue'],
                'constraints' => ['FieldOne' => [new Ip()]],
                'isValid' => false,
            ],
            [
                'fields' => ['FieldOne' => 'someValue'],
                'constraints' => ['FieldOne' => [new NotBlank()]],
                'isValid' => true,
            ],
        ];
    }

    #[DataProvider('provideValidation')]
    public function testValidation(array $fields, array $constraints, bool $isValid): void
    {
        $form = TestFormGenerator::getForm($fields, new ConstraintsValidator($constraints));
        $result = $form->validate();
        $this->assertSame($isValid, $result->isValid());
        $messages = $result->getMessages();
        if ($isValid) {
            $this->assertEmpty($messages);
        } else {
            $this->assertNotEmpty($messages);
            foreach ($messages as $message) {
                $this->assertSame(array_key_first($fields), $message['fieldName']);
                // It's up to the constraint what the message says, so testing it here could mean I have to update the
                // test if symfony changes their mind about it. For my purposes it's fine to just check that a message
                // exists
                $this->assertNotEmpty($message['message']);
            }
        }
    }
}
