<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeParticipant.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

class SubmitRoomChangeRequestCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRoomChangeRequest');
    }

    public function execute(CommandContext $context){

        // Cmd to redirect to when we're done or upon error.
        $formCmd = CommandFactory::getCommand('ShowRoomChangeRequestForm');
        $menuCmd = CommandFactory::getCommand('ShowStudentMenu');

        // Get input
        $cellNum = $context->get('cell_num');
        $optOut  = $context->get('cell_opt_out');

        $firstHallPref  = $context->get('first_choice');
        $secondHallPref = $context->get('second_choice');

        $swap = $context->get('swap_with');


        $term = Term::getCurrentTerm();

        // Create the student object
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Make sure the student is currently assigned
        $assignment = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), $term);

        if (is_null($assignment)) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You are not currently assigned to a room, so you cannot request a room change.');
            $menuCmd->redirect();
        }

        // Get the HMS_Bed object corresponding to the student's current assignment
        $bed = $assignment->get_parent();
        $room = $bed->get_parent();

        // Check for an existing room change request
        $changeReq = RoomChangeRequestFactory::getPendingByStudent($student, $term);
        if(!is_null($changeReq)){ // has pending request
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You already have a pending room change request. You cannot submit another request until your pending request is processed.');
            $menuCmd->redirect();
        }

        // Check that a cell phone number was provided, or that the opt-out box was checked.
        if((!isset($cellNum) || empty($cellNum)) && !isset($optOut)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a cell phone number or check the box indicating you do not wish to provide it.');
            $formCmd->redirect();
        }

        // Check the format of the cell phone number
        if(isset($cellNum)){
            // Filter out non-numeric characters
            $cellNum = preg_replace("/[^0-9]/", '', $cellNum);
        }

        $reason = $context->get('reason');

        // Make sure a 'reason' was provided.
        if(!isset($reason) || empty($reason)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please provide a brief explaniation of why you are requesting a room change.');
            $formCmd->redirect();
        }

        $type = $context->get('type');

        // Extra sanity checks if we're doing a switch
        if ($type == 'swap') {

            $switchUsername = $context->get('swap_with');

            // Can't switch with yourself
            if($student->getUsername() == $switchUsername) {
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "You can't swtich rooms with yourself. Please choose someone other than yourself.");
                $formCmd->redirect();
            }

            // Load the other student
            $swapStudent = StudentFactory::getStudentByUsername($switchUsername, $term);

            // Make sure the student is assigned
            $swapAssignment = HMS_Assignment::getAssignmentByBannerId($swapStudent->getBannerId(), $term);
            if (is_null($swapAssignment)) {
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "{$swapStudent->getName()} is not currently assigned. Please choose another student to switch rooms with.");
                $menuCmd->redirect();
            }

            // Make sure the other student's room is the same gender as this room
            $swapBed = $swapAssignment->get_parent();
            $swapRoom = $swapBed->get_parent();

            if ($swapRoom->getGender() != $room->getGender()) {
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "{$swapStudent->getName()} is assigned to a room of a different gender than you. Please choose student of the same gender as yourself to switch rooms with.");
                $menuCmd->redirect();
            }
        }

        //create the request object
        $request = new RoomChangeRequest($term, $reason);
        $request->save();

        // Main participant
        $participant = new RoomChangeParticipant($request, $student, $bed);

        if (isset($cellNum)) {
            $participant->setCellPhone($cellNum);
        }

        // Switching to a different room, so set params on main participant
        if($type == 'switch') {

            // preferences
            if (!empty($firstHallPref)) {
                $hall = new HMS_Residence_Hall($firstHallPref);
                if (!is_null($hall->getId())) {
                    $participant->setHallPref1($hall);
                }
            }

            if (!empty($secondHallPref)) {
                $hall = new HMS_Residence_Hall($firstHallPref);
                if (!is_null($hall->getId())) {
                    $participant->setHallPref2($hall);
                }
            }

        } else if($type == 'swap') {
            // Swapping with another student, so handle the other particpant

            // Set main participant's toBed to other student's bed
            $participant->setToBed($swapBed);

            // Create the other participant
            $swapParticipant = new RoomChangeParticipant($request, $swapStudent, $swapBed);

            // Set other student's toBed to main participant's bed
            $swapParticipant->setToBed($bed);
            $swapParticipant->save();

            //TODO send "request needs your approval" to other students
        }

        // Save the main participant and its state
        $participant->save();

        // Immediately transition to the StudentApproved state.
        $participant->transitionTo(new ParticipantStateStudentApproved($participant, time(), null, UserStatus::getUsername()));

        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_ROOM_CHANGE_SUBMITTED, UserStatus::getUsername(FALSE), $reason);

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your room change request has been received and is pending approval. You will be contacted by your Residence Director (RD) in the next 24-48 hours regarding your request.');
        $menuCmd->redirect();
    }
}
?>
