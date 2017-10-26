<?php

namespace Homestead\Command;

use \Homestead\CheckinFactory;
use \Homestead\CheckoutDocumentView;
use \Homestead\CommandFactory;

/**
 * Controller for showing the "Check-out successful page"
 *
 * @author jbooker
 * @package hms
 */
class ShowCheckoutDocumentCommand extends Command {
    private $checkinId;

    public function setCheckinId($id)
    {
        $this->checkinId = $id;
    }

    public function getRequestVars()
    {
        return array(
                'action' => 'ShowCheckoutDocument',
                'checkinId' => $this->checkinId
        );
    }

    public function execute(CommandContext $context)
    {
        // Load the checkin object
        $checkinId = $context->Get('checkinId');

        $checkin = CheckinFactory::getCheckinById($checkinId);

        if(!isset($checkin) || is_null($checkin)){
            \NQ::simple('hms', NotificationView::ERROR, 'There was an error while looking up this checkin. Please contact ESS.');
            $errCmd = CommandFactory::getCommand('ShowAdminMainMenu');
            $errCmd->redirect();
        }

        $view = new CheckoutDocumentView($checkin);

        $context->setContent($view->show());
    }
}
