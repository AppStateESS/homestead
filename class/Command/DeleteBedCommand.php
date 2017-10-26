<?php

namespace Homestead\Command;

use \Homestead\HMS_Bed;
use \Homestead\CommandFactory;
use \Homestead\UserStatus;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;

class DeleteBedCommand extends Command {

    private $bedId;
    private $roomId;

    public function setBedId($id){
        $this->bedId = $id;
    }

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function getRequestVars()
    {
        return array('action'=>'DeleteBed', 'bedId'=>$this->bedId, 'roomId'=>$this->roomId);
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'bed_structure')){
            throw new PermissionException('You do not have permission to remove a bed.');
        }

        $viewCmd = CommandFactory::getCommand('EditRoomView');
        $viewCmd->setRoomId($context->get('roomId'));

        $bedId = $context->get('bedId');
        $roomId = $context->get('roomId');

        if(!isset($roomId)){
            \NQ::simple('hms', NotificationView::ERROR, 'Missing room ID.');
            $viewCmd->redirect();
        }

        if(!isset($bedId)){
            \NQ::simple('hms', NotificationView::ERROR, 'Missing bed ID.');
            $viewCmd->redirect();
        }

        # Try to delete the bed
        try{
            HMS_Bed::deleteBed($bedId);
        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, 'There was an error deleting the bed: ' . $e->getMessage());
            $viewCmd->redirect();
        }

        \NQ::simple('hms', NotificationView::SUCCESS, 'Bed successfully deleted.');
        $viewCmd->redirect();
    }
}
