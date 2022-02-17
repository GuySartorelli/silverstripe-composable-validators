<?php

namespace Signify\ComposableValidators\Tests;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\TestOnly;

/**
 * A very basic page class for test purposes.
 */
class TestSiteTree extends SiteTree implements TestOnly
{
    public function canEdit($member = null)
    {
        return true;
    }

    public function canCreate($member = null, $context = [])
    {
        return true;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canDelete($member = null)
    {
        return true;
    }
}
