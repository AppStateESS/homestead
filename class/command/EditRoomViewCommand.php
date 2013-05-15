<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class EditRoomViewCommand extends Command {

    private $roomId;

    function setRoomId($id){
        $this->roomId = $id;
    }

    function getRequestVars()
    {
        $vars = array('action'=>'EditRoomView');
         
        if(isset($this->roomId)){
            $vars['room'] = $this->roomId;
        }
         
        return $vars;
    }

    function execute(CommandContext $context)
    {
        if( !Current_User::allow('hms', 'room_view') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view rooms.');
        }

        // Check for a  hall ID
        $roomId = $context->get('room');
         
        if(!isset($roomId)){
            throw new InvalidArgumentException('Missing room ID.');
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'RoomView.php');
        
        PHPWS_Core::initModClass('hms', 'DamageTypeFactory.php');
        //PHPWS_Core::intiModClass('hms', 'RoomDamageFactory.php');
        
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

?>