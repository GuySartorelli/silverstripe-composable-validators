<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Extension;

/**
 * Ensure validation messages for a GridField are displayed.
 * See https://github.com/silverstripe/silverstripe-framework/issues/10014
 */
class GridFieldMessagesExtension extends Extension
{
    /**
     * Add error messages to GridField.
     * Unfortunately there aren't nicer extension points for this, such as in FieldHolder().
     */
    public function updateAttributes()
    {
        $gridField = $this->owner;
        $message = Convert::raw2xml($gridField->getMessage());
        if (is_array($message)) {
            $message = $message['message'];
        }
        if ($message) {
            $alertType = $gridField->hasMethod('getAlertType') ? $gridField->getAlertType() : $gridField->messageType;
            $html = $gridField->getDescription();
            $gridField->setDescription(
                $html . '<p class="alert ' . $alertType
                . '" role="alert" id="message-' . $gridField->ID
                . '">' . $message . '</p>'
            );
        }
    }
}
