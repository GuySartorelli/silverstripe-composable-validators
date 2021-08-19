<?php

namespace Signify\ComposableValidators\Tests;

use DNADesign\Elemental\Forms\ElementalAreaField;
use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use DNADesign\Elemental\Models\ElementContent;
use Signify\ComposableValidators\Validators\RequiredBlocksValidator;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TabSet;

class RequiredBlocksValidatorTest extends SapphireTest
{
    private function getForm($elementalAreas, $validator = null)
    {
        $areas = [];
        foreach ($elementalAreas as $fieldName => $blockClasses) {
            if (is_numeric($fieldName)) {
                $fieldName = $blockClasses;
                $blockClasses = [];
            }
            $area = new ElementalArea();
            foreach ($blockClasses as $blockClass) {
                $block = new $blockClass();
                $area->Elements()->add($block);
            }
            $areas[] = new ElementalAreaField($fieldName, $area, []);
        }
        return new Form(null, 'testForm', new FieldList($areas), new FieldList([/* no actions */]), $validator);
    }

    /**
     * If a block is implicitly required but missing, a validation error is expected.
     */
    public function testDefaultConfigFailure()
    {
        $form = $this->getForm([
            'AreaField',
        ], new RequiredBlocksValidator([
            ElementContent::class,
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If a block is implicitly required and present missing, there should be no validation error.
     */
    public function testDefaultConfigPass()
    {
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class,
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If there are too many of the given block type, a validation error is expected.
     */
    public function testTooManyBlocks()
    {
        // One block allowed.
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'max' => 1,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);

        // Zero blocks allowed.
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'max' => 0,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If there are not too many of the given block type, there should be no validation error.
     */
    public function testNotTooManyBlocks()
    {
        // Allowed 3, have <= 3.
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'max' => 3,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);

        // Allowed 0, have 0.
        $form = $this->getForm([
            'AreaField',
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'max' => 0,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If there are not enough of the given block type, a validation error is expected.
     */
    public function testNotEnoughBlocks()
    {
        // One block, but less than the minimum expected.
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'min' => 2,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);

        // No blocks at all.
        $form = $this->getForm([
            'AreaField',
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'min' => 1,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If there are enough of the given block type, there should be no validation error.
     */
    public function testEnoughBlocks()
    {
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'min' => 2,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the block is out of position, a validation error is expected.
     */
    public function testBlockOutOfPositionFromTop()
    {
        // Not AT top.
        $form = $this->getForm([
            'AreaField' => [
                TestElementalBlock::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => 0,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);

        // Not in another position FROM top.
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => 1,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If the block is in position, there should be no validation error.
     */
    public function testBlockInPositionFromTop()
    {
        // At top.
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => 0,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);

        // At another position from top.
        $form = $this->getForm([
            'AreaField' => [
                TestElementalBlock::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => 1,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);

        // Block doesn't exist, so no validation error.
        $form = $this->getForm([
            'AreaField' => [
                TestElementalBlock::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => 1,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the block is out of position, a validation error is expected.
     */
    public function testBlockOutOfPositionFromBottom()
    {
        // Not AT bottom.
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => -1,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);

        // Not in another position FROM bottom.
        $form = $this->getForm([
            'AreaField' => [
                TestElementalBlock::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => -2,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If the block is in position, there should be no validation error.
     */
    public function testBlockInPositionFromBottom()
    {
        // At bottom.
        $form = $this->getForm([
            'AreaField' => [
                TestElementalBlock::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => -1,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);

        // At another position from bottom.
        $form = $this->getForm([
            'AreaField' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => -2,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);

        // Block doesn't exist, so no validation error.
        $form = $this->getForm([
            'AreaField' => [
                TestElementalBlock::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => -1,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If the block is out of position in any area, a validation error is expected.
     */
    public function testMultipleAreasBlockOutOfPosition()
    {
        $form = $this->getForm([
            'AreaField1' => [
                TestElementalBlock::class,
                ElementContent::class,
            ],
            'AreaField2' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => 0,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If the block in position in all areas, there should be no validation error.
     */
    public function testMultipleAreasBlockInPosition()
    {
        $form = $this->getForm([
            'AreaField1' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
            'AreaField2' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'pos' => 0,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If there are not enough blocks across all areas, a validation error is expected.
     */
    public function testMultipleAreasNotEnoughBlocks()
    {
        $form = $this->getForm([
            'AreaField1' => [
                TestElementalBlock::class,
                ElementContent::class,
            ],
            'AreaField2' => [
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'min' => 2,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If there are enough blocks across all areas, there should be no validation error.
     */
    public function testMultipleAreasEnoughBlocks()
    {
        $form = $this->getForm([
            'AreaField1' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
            'AreaField2' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'min' => 2,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * If there are too many blocks across all areas, a validation error is expected.
     */
    public function testMultipleAreasTooManyBlocks()
    {
        $form = $this->getForm([
            'AreaField1' => [
                TestElementalBlock::class,
                ElementContent::class,
            ],
            'AreaField2' => [
                ElementContent::class,
                ElementContent::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'max' => 2,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertNotEmpty($messages);
    }

    /**
     * If there are not too many blocks across all areas, there should be no validation error.
     */
    public function testMultipleAreasNotTooManyBlocks()
    {
        $form = $this->getForm([
            'AreaField1' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
            'AreaField2' => [
                ElementContent::class,
                TestElementalBlock::class,
            ],
        ], new RequiredBlocksValidator([
            ElementContent::class => [
                'max' => 2,
            ]
        ]));
        $result = $form->validationResult();
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertEmpty($messages);
    }

    /**
     * All of the fields that are in both the form AND the validator should have 'required-elements' validation hints.
     */
    public function testValidationHints()
    {
        $fieldList = new FieldList();
        $fieldList->add(new TabSet('Root'));
        $fieldList->addFieldToTab('Root.Test', new ElementalAreaField('ElementalArea', new ElementalArea(), []));
        $validator = new RequiredBlocksValidator($validation = [
            ElementContent::class => [
                'max' => 2,
            ],
            TestElementalBlock::class => [
                'min' => 2,
                'pos' => -2,
            ],
            BaseElement::class,
        ]);
        $form = new Form(null, 'testForm', $fieldList, new FieldList([/* no actions */]), $validator);

        $hint = $validator->getValidationHints()[$form->Fields()->dataFieldByName('ElementalArea')->ID()];
        // The field name must be included in the validation hint.
        $this->assertEquals('ElementalArea', $hint['name']);
        // The tab ID must be included in the validation hint.
        $this->assertEquals('Root_Test', $hint['tab']);
        // The 'required-elements' validation hint should declare all validation requirements.
        foreach ($validation as $class => &$config) {
            if (is_numeric($class)) {
                unset($validation[$class]);
                $validation[$config] = [
                    'name' => $config::singleton()->getType(),
                    'min' => 1,
                    'max' => null,
                    'pos' => null,
                ];
                continue;
            }
            $config['name'] = $class::singleton()->getType();
            if (!array_key_exists('min', $config)) {
                $config['min'] = null;
            }
            if (!array_key_exists('max', $config)) {
                $config['max'] = null;
            }
            if (!array_key_exists('pos', $config)) {
                $config['pos'] = null;
            }
        }
        $this->assertEquals($validation, $hint['required-elements']);
    }
}
