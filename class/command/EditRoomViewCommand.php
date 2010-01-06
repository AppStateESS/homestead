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
         
        $room = new HMS_Room($roomId);
        $floor = $room->get_parent();
        $hall = $floor->get_parent();
         
        $roomView = new RoomView($hall, $floor, $room);
         
        $context->setContent($roomView->show());
    }
}

?>