<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * EditRoomCommand
 *
 * Controller responsible for saving changes to room attributes.
 *
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 * @package HMS
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

        // Create the room object given the room_id
        $room = new HMS_Room($roomId);
        if(!$room){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Invalid room.');
            $viewCmd->redirect();
        }

        // Check if the user is trying to change a room's gender to co-ed.
        // If so, make sure the user has the permission to do so.
        if($room->getGender() != $context->get('gender_type') && $context->get('gender_type') == COED){
            if(!Current_User::allow('hms', 'coed_rooms')){
                NQ::simple('hms', hms\NotificationView::ERROR, 'Error: You do not have permission to change the room gender to co-ed. No changes were made.');
                $viewCmd->redirect();
            }
        }

        // Compare the room's gender and the gender the user selected
        // If they're not equal, call 'can_change_gender' public function
        if($room->gender_type != $context->get('gender_type')){
            if(!$room->can_change_gender($context->get('gender_type'))){
                NQ::simple('hms', hms\NotificationView::ERROR, 'Error: Incompatible genders detected. No changes were made.');
                $viewCmd->redirect();
            }
        }

        // Check the default gender in the same way
        if($room->default_gender != $context->get('default_gender')){
            if(!$room->can_change_gender($context->get('default_gender'))){
                NQ::simple('hms', hms\NotificationView::ERROR, 'Error: Default gender incompatable. No changes were made.');
                $viewCmd->redirect();
            }
        }

        if($room->get_number_of_assignees() > 0 && $context->get('offline') == 1){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Error: Cannot take room offline while students are assigned to the room.  No changes were made.');
            $viewCmd->redirect();
        }

        // Grab all the input from the form and save the room
        //Changed from radio buttons to checkboxes, ternary
        //prevents null since only 1 is defined as a return value
        //test($_REQUEST['room_number']);
        $room->room_number    = $context->get('room_number');
        $room->gender_type    = $context->get('gender_type');
        $room->default_gender = $context->get('default_gender');

        $rlcReserved = $context->get('rlc_reserved');
        if($rlcReserved != 0) {
        	$room->setReservedRlcId($rlcReserved);
        }
        else {
            $room->setReservedRlcId(null);
        }

        $room->offline        = $context->get('offline')   == 1 ? 1 : 0;
        $room->reserved       = $context->get('reserved')  == 1 ? 1 : 0;
        $room->ra             = $context->get('ra')        == 1 ? 1 : 0;
        $room->private        = $context->get('private')   == 1 ? 1 : 0;
        $room->overflow       = $context->get('overflow')  == 1 ? 1 : 0;
        $room->parlor         = $context->get('parlor')    == 1 ? 1 : 0;

        $room->ada              = $context->get('ada')              == 1 ? 1 : 0;
        $room->hearing_impaired = $context->get('hearing_impaired') == 1 ? 1 : 0;
        $room->bath_en_suite    = $context->get('bath_en_suite')    == 1 ? 1 : 0;

        $reservedReason = $context->get('reserved_reason');
        if($reservedReason == 'none') {
            $room->setReserved(0);
        } else {
            $room->setReserved(1);
        }
        $room->setReservedReason($reservedReason);
        $room->setReservedNotes($context->get('reserved_notes'));

        $result = $room->save();

        if(!$result || \PHPWS_Error::logIfError($result)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'There was a problem saving the room data. No changes were made.');
            $viewCmd->redirect();
        }

        NQ::simple('hms', hms\NotificationView::SUCCESS, 'The room was updated successfully.');
        $viewCmd->redirect();
    }
}
