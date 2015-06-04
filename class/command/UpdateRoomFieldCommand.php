<?php
PHPWS_Core::initModClass('hms', 'HMS_Room.php');

/**
 * UpdateRoomFieldCommand
 *
 *   Updates a field in a particular room.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package HMS
 */

class UpdateRoomFieldCommand extends Command {

    public function getRequestVars(){
        return array('action' => 'UpdateRoomFieldCommand');
    }

    public function execute(CommandContext $context){

        // Make sure the user has permission to change room attributes
        if(!Current_User::allow('hms', 'room_attributes')){
            echo json_encode(false);
            die();
        }

        // Get the values from the request
        $id = $context->get('id');
        $element = $context->get('field');
        $value = $context->get('value');

        // Make sure the required values were passed in on the request
        if(is_null($id) || is_null($element) || is_null($value) ){
            echo json_encode(false);
            die();
        }

        // Instantiate the room object
        try{
            $room = new HMS_Room($id);
        }catch(Exception $e){
            echo json_encode(false);
            die();
        }

        /**********
         * Gender *
        */

        // If the user is trying to change the gender, make sure no one is assigned
        if($element == 'gender_type'){
            if($room->get_number_of_assignees() > 0){
                echo json_encode(false);
                die();
            }
        }

        // Check if the user is trying to change a room's gender to co-ed.
        // If so, make sure the user has the permission to do so.
        if($element == 'gender_type' && $room->getGender() != $value && $value == COED){
            if(!Current_User::allow('hms', 'coed_rooms')){
                echo json_encode(false);
                die();
            }
        }

        // If the gender field was changed
        if($element == 'gender_type' && $room->getGender() != $value){
            // Make sure the requested gender is compatiable with the hall/floor
            if($room->can_change_gender($value)){
                $room->setGender($value);
            }else{
                echo json_encode(false);
                die();
            }
        }

        /******************
         * Default Gender *
        */

        // If default gender was changed
        if($element == 'default_gender' && $room->getDefaultGender() != $value){
            // Make sure the requested default gender is compatiable with the hall/floor
            if($room->can_change_gender($value)){
                $room->setDefaultGender($value);
            }else{
                echo json_encode(false);
                die();
            }
        }
        
        /* RLC Reservation */
        if($element == 'rlc_reserved') {
            if($value <= 0) {
            	$room->setReservedRlcId(null);
            } else {
        	   $room->setReservedRlcId($value);
            }
        }

        // A switch statement for all the check boxes
        switch($element){
            case 'ra':
                $room->setRa($value);
                break;
            case 'private':
                $room->setPrivate($value);
                break;
            case 'overflow':
                $room->setOverflow($value);
                break;
            case 'ada':
                $room->setADA($value);
                break;
            case 'reserved':
                $room->setReserved($value);
                break;
            case 'offline':
                $room->setOffline($value);
                break;
        }

        try{
            $room->save();
        }catch(Exception $e){
            echo json_encode(false);
            die();
        }

        echo json_encode($room);
        die();
    }
}
