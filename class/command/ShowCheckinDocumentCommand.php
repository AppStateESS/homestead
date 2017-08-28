<?php

namespace Homestead\command;

use \Homestead\Command;
PHPWS_Core::initModClass('hms', 'CheckinFactory.php');

/**
 * Controller for showing the Check-in successful page
 *
 * @author jbooker
 * @package hms
 */
class ShowCheckinDocumentCommand extends Command {

    private $bannerId;
    private $checkinId;

    public function setBannerId($bannerId){
        $this->bannerId = $bannerId;
    }

    public function setCheckinId($checkinId){
        $this->checkinId = $checkinId;
    }

    public function getRequestVars(){
        return array('action'		=> 'ShowCheckinDocument',
                'bannerId' 	=> $this->bannerId,
                'checkinId'	=> $this->checkinId);
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

        PHPWS_Core::initModClass('hms', 'CheckinDocumentView.php');
        $view = new CheckinDocumentView($checkin);

        $context->setContent($view->show());
    }
}
