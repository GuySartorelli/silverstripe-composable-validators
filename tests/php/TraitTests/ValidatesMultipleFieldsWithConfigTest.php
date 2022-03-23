<?php

namespace Signify\ComposableValidators\Tests;

use InvalidArgumentException;
use SilverStripe\Dev\SapphireTest;

class ValidatesMultipleFieldsWithConfigTest extends SapphireTest
{
    private function getFieldsForTests(): array
    {
        return [
            'Title' => ['config1'],
            'Content' => ['config2'],
            'Image' => ['config3'],
            'AnotherField' => ['config4'],
        ];
    }

    /**
     * Should be able to instantiate with no arguments or an empty array.
     */
    public function testConstructingWithoutFields(): void
    {
        $validator = new TestMultiFieldWithConfigValidator();
        $this->assertEmpty($validator->getFields());
        $validator = new TestMultiFieldWithConfigValidator([]);
        $this->assertEmpty($validator->getFields());
    }

    /**
     * Should be able to instantiate with an array of field names and configuration arrays.
     */
    public function testConstructingWithArray(): void
    {
        $fields = $this->getFieldsForTests();
        $validator = new TestMultiFieldWithConfigValidator($fields);
        $this->assertEquals($fields, $validator->getFields());
    }

    /**
     * Should not be able to instantiate with an array of field names and empty configuration arrays.
     */
    public function testConstructingWithEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TestMultiFieldWithConfigValidator(['someField' => []]);
    }

    /**
     * Should be able to remove all fields at once.
     */
    public function testRemoveValidation(): void
    {
        $validator = new TestMultiFieldWithConfigValidator($this->getFieldsForTests());
        $validator->removeValidation();
        $this->assertEmpty($validator->getFields());
    }

    /**
     * Should be able to remove fields one at a time, by name.
     */
    public function testRemoveField(): void
    {
        $fields = $this->getFieldsForTests();
        $fieldNames = array_keys($fields);
        $validator = new TestMultiFieldWithConfigValidator($fields);
        // Fields should be easy to remove from the list.
        $removeField = array_shift($fieldNames);
        unset($fields[$removeField]);
        $validator->removeField($removeField);
        $this->assertEquals($fields, $validator->getFields());
        // Check that it wasn't just removing the first item.
        $removeField = array_pop($fieldNames);
        unset($fields[$removeField]);
        $validator->removeField($removeField);
        $this->assertEquals($fields, $validator->getFields());
        // Attempting to remove a field that isn't in the list should not mutate the list.
        $validator->removeField('MissingField');
        $this->assertEquals($fields, $validator->getFields());
    }

    /**
     * Should be able to remove many fields at once by name.
     */
    public function testRemoveFields(): void
    {
        $fields = $this->getFieldsForTests();
        $fieldNames = array_keys($fields);
        $validator = new TestMultiFieldWithConfigValidator($fields);
        // Fields should be easy to remove from the list.
        $removeFields = [array_shift($fieldNames), array_pop($fieldNames)];
        array_shift($fields);
        array_pop($fields);
        $validator->removeFields($removeFields);
        $this->assertEquals($fields, $validator->getFields());
        // Attempting to remove fields that aren't in the list should not mutate the list.
        $validator->removeFields(['MissingField']);
        $this->assertEquals($fields, $validator->getFields());
    }

    /**
     * Should be able to add fields one at a time, by name.
     */
    public function testAddField(): void
    {
        $validator = new TestMultiFieldWithConfigValidator();
        // Add a couple of fields.
        $validator->addField('Title', ['config1']);
        $this->assertEquals(['Title' => ['config1']], $validator->getFields());
        $validator->addField('Content', ['config2']);
        $this->assertEquals(
            [
                'Title' => ['config1'],
                'Content' => ['config2'],
            ],
            $validator->getFields()
        );
        // Make sure that the same field can't be added twice, but config does get updated.
        $validator->addField('Content', ['confignew']);
        $this->assertEquals(
            [
                'Title' => ['config1'],
                'Content' => ['confignew'],
            ],
            $validator->getFields()
        );
    }

    /**
     * Should be able to add many fields at once by name.
     */
    public function testAddFields(): void
    {
        $validator = new TestMultiFieldWithConfigValidator();
        // Add a couple of fields.
        $validator->addFields(['Title' => ['config1']]);
        $this->assertEquals(['Title' => ['config1']], $validator->getFields());
        $validator->addFields([
            'Content' => ['config2'],
            'Image' => ['config3'],
        ]);
        $this->assertEquals(
            [
                'Title' => ['config1'],
                'Content' => ['config2'],
                'Image' => ['config3'],
            ],
            $validator->getFields()
        );
        // Make sure that the same fields can't be added twice, but config is updated.
        $validator->addFields([
            'Content' => ['confignew'],
            'Image' => ['confignew2'],
            'AnotherField' => ['config4'],
        ]);
        $this->assertEquals(
            [
                'Title' => ['config1'],
                'Content' => ['confignew'],
                'Image' => ['confignew2'],
                'AnotherField' => ['config4'],
            ],
            $validator->getFields()
        );
    }
}
