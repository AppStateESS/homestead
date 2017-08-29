<?php

namespace Homestead\Command;

use \Homestead\HMS_Room;
use \Homestead\Term;
use \Homestead\CommandFactory;
use \Homestead\DamageTypeFactory;
use \Homestead\RoomView;
use \Homestead\Exception\PermissionException;

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 * @package hms
 */

class EditRoomViewCommand extends Command {

    private $roomId;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'EditRoomView');

        if(isset($this->roomId)){
            $vars['room'] = $this->roomId;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if( !\Current_User::allow('hms', 'room_view') ){
            throw new PermissionException('You do not have permission to view rooms.');
        }

        // Check for a  hall ID
        $roomId = $context->get('room');

        if(!isset($roomId)){
            throw new \InvalidArgumentException('Missing room ID.');
        }

        // Load the room
        $room = new HMS_Room($roomId);

        if($room->term != Term::getSelectedTerm()){
            $roomCmd = CommandFactory::getCommand('SelectRoom');
            $roomCmd->setTitle('Edit a Room');
            $roomCmd->setOnSelectCmd(CommandFactory::getCommand('EditRoomView'));
            $roomCmd->redirect();
        }

        // Load the floor/hall
        $floor = $room->get_parent();
        $hall = $floor->get_parent();

        // Load the room damages and damage types
        $damageTypes = DamageTypeFactory::getDamageTypeAssoc();

        $roomView = new RoomView($hall, $floor, $room, $damageTypes);

        $context->setContent($roomView->show());
    }
}
