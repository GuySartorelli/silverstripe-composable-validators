<?php

namespace Signify\ComposableValidators\Tests;

use Signify\ComposableValidators\Extensions\DataObjectValidationExemptionExtension;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DataObject;

class DataObjectValidationExemptionExtensionTest extends SapphireTest
{
    protected static $required_extensions = [
        DataObject::class => [DataObjectValidationExemptionExtension::class],
    ];

    /**
     * These actions should be validation exempt if they're present.
     */
    public function testActionsAreValidationExempt()
    {
        $page = new TestSiteTree();
        $actions = $page->getCMSActions();
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
