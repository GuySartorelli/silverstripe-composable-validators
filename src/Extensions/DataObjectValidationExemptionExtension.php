<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class DataObjectValidationExemptionExtension extends Extension
{
    /**
     * Don't require validation for delete/archive/restore actions.
     * @see SiteTree::getCMSActions()
     * @see GridFieldItemRequestValidationExemptionExtension::updateFormActions()
     */
    public function updateCMSActions(FieldList $actions)
    {
        // Can't just use dataFieldByName because sometimes action_save is there twice which throws an exception.
        $ignoreValidationActions = [
            'action_delete',
            'action_doDelete',
            'action_archive',
            'action_doArchive',
            'action_restore',
            'action_doRestore',
        ];
        foreach ($actions->flattenFields() as $action) {
            if (in_array($action->getName(), $ignoreValidationActions)) {
                $action->setValidationExempt(true);
            }
        }
    }
}
