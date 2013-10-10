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

        //create the request object
        $request = new RoomChangeRequest($term, $reason);
        $request->save();

        $type = $context->get('type');

        // Switching to a different room
        if($type == 'switch') {

            $participant = new RoomChangeParticipant($request, $student, $bed);

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

            if (isset($cellNum)) {
                $participant->setCellPhone($cellNum);
            }

            // Save this participant and its state
            $participant->save();

            // Immediately transition to the StudentApproved state.
            $participant->transitionTo(new ParticipantStateStudentApproved($participant, time(), null, UserStatus::getUsername()));

            //TODO Send "request submitted" confirmation email

        // Swapping with another student
        } else if ($type == 'swap') {
            // TODO
            /*
            // swap - make sure the other person has an assignment
            if(!empty($swap) && !is_null(HMS_Assignment::getAssignment($swap, Term::getSelectedTerm()))){
            }else{
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'The user name you supplied was invalid or the student is not currently assigned to a room. (Hint: Don\'t include the "@appstate.edu" portion of the email address.)');
                $cmd->redirect();
            }

             //sanity check
            if($request->is_swap && $request->switch_with == $request->username){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Please select someone other than yourself to switch rooms with.");
                $cmd->redirect();
            }
            */

            //TODO send "request needs your approval" to other students
        }

        //$request->change(new PendingRoomChangeRequest); // This triggers emails to be sent, so don't do it until as late as possible


        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_ROOM_CHANGE_SUBMITTED, UserStatus::getUsername(FALSE), $reason);

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your room change request has been received and is pending approval. You will be contacted by your Residence Director (RD) in the next 24-48 hours regarding your request.');
        $menuCmd->redirect();
    }
}
?>
