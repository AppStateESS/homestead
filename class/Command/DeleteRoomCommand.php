<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\HMS_Room;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;

class DeleteRoomCommand extends Command {

    private $roomId;
    private $floorId;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function setFloorId($id){
        $this->floorId = $id;
    }

    public function getRequestVars()
    {
        return array('action'=>'DeleteRoom', 'floorId'=>$this->floorId, 'roomId'=>$this->roomId);
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'room_structure')){
            throw new PermissionException('You do not have permission to remove a room.');
        }

        $viewCmd = CommandFactory::getCommand('EditFloorView');
        $viewCmd->setFloorId($context->get('floorId'));

        $floorId = $context->get('floorId');
        $roomId = $context->get('roomId');

        if(!isset($roomId)){
            \NQ::simple('hms', NotificationView::ERROR, 'Missing room ID.');
            $viewCmd->redirect();
        }

        if(!isset($floorId)){
            \NQ::simple('hms', NotificationView::ERROR, 'Missing floor ID.');
            $viewCmd->redirect();
        }

        # Try to delete the room
        try{
            HMS_Room::deleteRoom($roomId);
        }catch(\Exception $e){
            \NQ::simple('hms', NotificationView::ERROR, 'There was an error deleting the room. Some (but not all) beds may have been deleted. : ' . $e->getMessage());
            $viewCmd->redirect();
        }

        \NQ::simple('hms', NotificationView::SUCCESS, 'Room successfully deleted.');
        $viewCmd->redirect();
    }
}
