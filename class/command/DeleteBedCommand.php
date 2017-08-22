<?php

namespace Homestead\command;

use \Homestead\Command;

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
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'bed_structure')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to remove a bed.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $viewCmd = CommandFactory::getCommand('EditRoomView');
        $viewCmd->setRoomId($context->get('roomId'));

        $bedId = $context->get('bedId');
        $roomId = $context->get('roomId');

        if(!isset($roomId)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Missing room ID.');
            $viewCmd->redirect();
        }

        if(!isset($bedId)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Missing bed ID.');
            $viewCmd->redirect();
        }

        # Try to delete the bed
        try{
            HMS_Bed::deleteBed($bedId);
        }catch(Exception $e){
            NQ::simple('hms', hms\NotificationView::ERROR, 'There was an error deleting the bed: ' . $e->getMessage());
            $viewCmd->redirect();
        }

        NQ::simple('hms', hms\NotificationView::SUCCESS, 'Bed successfully deleted.');
        $viewCmd->redirect();
    }
}
