<?php

namespace Signify\ComposableValidators\Tests;

use SilverStripe\Dev\SapphireTest;

class ValidatesMultipleFieldsTest extends SapphireTest
{
    private function getFieldsForTests()
    {
        return [
            'Title',
            'Content',
            'Image',
            'AnotherField',
        ];
    }

    /**
     * Should be able to instantiate with no arguments.
     */
    public function testConstructingWithoutFields()
    {
        $validator = new TestMultiFieldValidator();
        $this->assertEmpty($validator->getFields());
    }

    /**
     * Should be able to instantiate with an array of field names.
     */
    public function testConstructingWithArray()
    {
        $fields = $this->getFieldsForTests();
        $validator = new TestMultiFieldValidator($fields);
        $this->assertEquals($fields, $validator->getFields());
    }

    /**
     * Should be able to instantiate with an argument list of field names.
     */
    public function testConstructingWithArguments()
    {
        $validator = new TestMultiFieldValidator(
            'Title',
            'Content',
            'Image',
            'AnotherField'
        );
        $this->assertEquals($this->getFieldsForTests(), $validator->getFields());
    }

    /**
     * Should be able to remove all fields at once.
     */
    public function testRemoveValidation()
    {
        $validator = new TestMultiFieldValidator($this->getFieldsForTests());
        $validator->removeValidation();
        $this->assertEmpty($validator->getFields());
    }

    /**
     * Should be able to remove fields one at a time, by name.
     */
    public function testRemoveField()
    {
        $fields = $this->getFieldsForTests();
        $validator = new TestMultiFieldValidator($fields);
        // Fields should be easy to remove from the list.
        $removeField = array_shift($fields);
        $validator->removeField($removeField);
        $this->assertEquals($fields, $validator->getFields());
        // Check that it wasn't just removing the first item.
        $removeField = array_pop($fields);
        $validator->removeField($removeField);
        $this->assertEquals($fields, $validator->getFields());
        // Attempting to remove a field that isn't in the list should not mutate the list.
        $validator->removeField('MissingField');
        $this->assertEquals($fields, $validator->getFields());
    }

    /**
     * Should be able to remove many fields at once by name.
     */
    public function testRemoveFields()
    {
        $fields = $this->getFieldsForTests();
        $validator = new TestMultiFieldValidator($fields);
        // Fields should be easy to remove from the list.
        $removeFields = [array_shift($fields), array_pop($fields)];
        $validator->removeFields($removeFields);
        $this->assertEquals($fields, $validator->getFields());
        // Attempting to remove fields that aren't in the list should not mutate the list.
        $validator->removeFields(['MissingField']);
        $this->assertEquals($fields, $validator->getFields());
    }

    /**
     * Should be able to add fields one at a time, by name.
     */
    public function testAddField()
    {
        $validator = new TestMultiFieldValidator();
        // Add a couple of fields.
        $validator->addField('Title');
        $this->assertEquals(['Title'], $validator->getFields());
        $validator->addField('Content');
        $this->assertEquals(
            [
                'Title',
                'Content',
            ],
            $validator->getFields()
        );
        // Make sure that the same field can't be added twice.
        $validator->addField('Content');
        $this->assertEquals(
            [
                'Title',
                'Content',
            ],
            $validator->getFields()
        );
    }

    /**
     * Should be able to add many fields at once by name.
     */
    public function testAddFields()
    {
        $validator = new TestMultiFieldValidator();
        // Add a couple of fields.
        $validator->addFields(['Title']);
        $this->assertEquals(['Title'], $validator->getFields());
        $validator->addFields([
            'Content',
            'Image',
        ]);
        $this->assertEquals(
            [
                'Title',
                'Content',
                'Image',
            ],
            $validator->getFields()
        );
        // Make sure that the same fields can't be added twice.
        $validator->addFields([
            'Content',
            'Image',
            'AnotherField',
        ]);
        $this->assertEquals(
            [
                'Title',
                'Content',
                'Image',
                'AnotherField',
            ],
            $validator->getFields()
        );
    }
}
