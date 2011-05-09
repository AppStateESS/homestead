<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class EditRoomCommand extends Command {

    private $roomId;

    public function setRoomId($id){
        $this->roomId = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'EditRoom');

        if(isset($this->roomId)){
            $vars['roomId'] = $this->roomId;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');

        if( !Current_User::allow('hms', 'room_attributes') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit rooms.');
        }

        $roomId = $context->get('roomId');

        $viewCmd = CommandFactory::getCommand('EditRoomView');
        $viewCmd->setRoomId($roomId);

        # Create the room object given the room_id
        $room = new HMS_Room($roomId);
        if(!$room){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Invalid room.');
            $viewCmd->redirect();
        }

        # Compare the room's gender and the gender the user selected
        # If they're not equal, call 'can_change_gender' public function
        if($room->gender_type != $context->get('gender_type')){
            if(!$room->can_change_gender($context->get('gender_type'))){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: Incompatible genders detected. No changes were made.');
                $viewCmd->redirect();
            }
        }

        # Check the default gender in the same way
        if($room->default_gender != $context->get('default_gender')){
            if(!$room->can_change_gender($context->get('default_gender'))){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: Default gender incompatable. No changes were made.');
                $viewCmd->redirect();
            }
        }

        if($room->get_number_of_assignees() > 0 && $context->get('is_online') != 1){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Error: Cannot take room offline while students are assigned to the room.  No changes were made.');
            $viewCmd->redirect();
        }

        # Grab all the input from the form and save the room
        //Changed from radio buttons to checkboxes, ternary
        //prevents null since only 1 is defined as a return value
        //test($_REQUEST['room_number']);
        $room->room_number    = $context->get('room_number');
        $room->gender_type    = $context->get('gender_type');
        $room->default_gender = $context->get('default_gender');
        $room->is_online      = $context->get('is_online')    == 1 ? 1 : 0;
        $room->is_reserved    = $context->get('is_reserved')  == 1 ? 1 : 0;
        $room->ra_room        = $context->get('ra_room')      == 1 ? 1 : 0;
        $room->private_room   = $context->get('private_room') == 1 ? 1 : 0;
        $room->is_medical     = $context->get('is_medical')   == 1 ? 1 : 0;
        $room->is_overflow    = $context->get('is_overflow')  == 1 ? 1 : 0;

        $result = $room->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was a problem saving the room data. No changes were made.');
            $viewCmd->redirect();
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'The room was updated successfully.');
        $viewCmd->redirect();
    }
}

?>