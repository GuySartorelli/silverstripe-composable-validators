<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Extensions\GridFieldItemRequestValidationExemptionExtension;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

class GridFieldItemRequestValidationExemptionExtensionTest extends SapphireTest
{
    protected static $required_extensions = [
        GridFieldDetailForm_ItemRequest::class => [GridFieldItemRequestValidationExemptionExtension::class],
    ];

    /**
     * These actions should be validation exempt if they're present.
     */
    public function testActionsAreValidationExempt()
    {
        $itemRequest = new GridFieldDetailForm_ItemRequest(
            new GridField('testField'),
            new GridFieldDetailForm(),
            new TestSiteTree(['ID' => 1]),
            new RequestHandler(),
            'testForm'
        );
        $actions = $itemRequest->ItemEditForm()->Actions();
        $ignoreValidationActions = [
            'action_delete',
            'action_doDelete',
            'action_archive',
            'action_doArchive',
            'action_restore',
            'action_doRestore',
        ];

        $count = 0;
        foreach ($actions->flattenFields() as $action) {
            if (in_array($action->getName(), $ignoreValidationActions)) {
                $count++;
                $this->assertTrue($action->getValidationExempt());
            }
        }
        $this->assertGreaterThan(0, $count);
    }
}
