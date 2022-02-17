<?php

namespace Signify\ComposableValidators\Extensions;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Extension;
use SilverStripe\View\HTML;

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
            $html = $gridField->getDescription();
            $gridField->setDescription(
                $html . HTML::createTag(
                    'p',
                    ['class' => 'message ' . $gridField->getMessageType()],
                    $message
                )
            );
        }
    }
}
