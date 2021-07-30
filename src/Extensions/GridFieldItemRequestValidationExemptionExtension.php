<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class GridFieldItemRequestValidationExemptionExtension extends Extension
{
    /**
     * Ensure objects are not tested for validity when deleting/archiving/restoring.
     *
     * @param FieldList $actions
     * @see \SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest::getFormActions()
     * @see \SilverStripe\Versioned\VersionedGridFieldItemRequest::addVersionedButtons()
     * @see DataObjectValidationExemptionExtension::updateCMSActions()
     */
    public function updateFormActions(FieldList &$actions)
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
