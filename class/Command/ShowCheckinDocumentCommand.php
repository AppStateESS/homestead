<?php

namespace Homestead\Command;

use \Homestead\CheckinFactory;
use \Homestead\CheckinDocumentView;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;

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

        $view = new CheckinDocumentView($checkin);

        $context->setContent($view->show());
    }
}
