<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\CommandFactory;
use \Homestead\StudentFactory;
use \Homestead\HMS_Assignment;
use \Homestead\ResidenceHall;
use \Homestead\HMS_Email;
use \Homestead\HMS_Activity_Log;
use \Homestead\RoomChangeParticipant;
use \Homestead\RoomChangeRequest;
use \Homestead\RoomChangeRequestFactory;
use \Homestead\ParticipantStateStudentApproved;
use \Homestead\UserStatus;
use \Homestead\NotificationView;
use \Homestead\Exception\StudentNotFoundException;


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


        $term = Term::getCurrentTerm();

        // Create the student object
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        // Make sure the student is currently assigned
        $assignment = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), $term);

        if (is_null($assignment)) {
            \NQ::simple('hms', NotificationView::ERROR, 'You are not currently assigned to a room, so you cannot request a room change.');
            $menuCmd->redirect();
        }

        // Get the Bed object corresponding to the student's current assignment
        $bed = $assignment->get_parent();
        $room = $bed->get_parent();

        // Check for an existing room change request
        $changeReq = RoomChangeRequestFactory::getPendingByStudent($student, $term);
        if(!is_null($changeReq)){ // has pending request
            \NQ::simple('hms', NotificationView::ERROR, 'You already have a pending room change request. You cannot submit another request until your pending request is processed.');
            $menuCmd->redirect();
        }

        // Check that a cell phone number was provided, or that the opt-out box was checked.
        if((!isset($cellNum) || empty($cellNum)) && !isset($optOut)){
            \NQ::simple('hms', NotificationView::ERROR, 'Please provide a cell phone number or check the box indicating you do not wish to provide it.');
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
            \NQ::simple('hms', NotificationView::ERROR, 'Please provide a brief explaniation of why you are requesting a room change.');
            $formCmd->redirect();
        }

        $type = $context->get('type');

        // Extra sanity checks if we're doing a switch
        if ($type == 'swap') {

            $switchUsername = $context->get('swap_with');

            // Can't switch with yourself
            if($student->getUsername() == $switchUsername) {
                \NQ::simple('hms', NotificationView::ERROR, "You can't swtich rooms with yourself. Please choose someone other than yourself.");
                $formCmd->redirect();
            }

            // Load the other student
            try {
                $swapStudent = StudentFactory::getStudentByUsername($switchUsername, $term);
            } catch(StudentNotFoundException $e) {
                \NQ::simple('hms', NotificationView::ERROR, "Sorry, we could not find a student with the user name '$switchUsername'. Please double-check the user name of the student you would like to switch places with.");
                $formCmd->redirect();
            }

            // Make sure the student is assigned
            $swapAssignment = HMS_Assignment::getAssignmentByBannerId($swapStudent->getBannerId(), $term);
            if (is_null($swapAssignment)) {
                \NQ::simple('hms', NotificationView::ERROR, "{$swapStudent->getName()} is not currently assigned. Please choose another student to switch rooms with.");
                $formCmd->redirect();
            }

            // Make sure the other student's room is the same gender as this room
            $swapBed = $swapAssignment->get_parent();
            $swapRoom = $swapBed->get_parent();

            if ($swapRoom->getGender() != $room->getGender()) {
                \NQ::simple('hms', NotificationView::ERROR, "{$swapStudent->getName()} is assigned to a room of a different gender than you. Please choose student of the same gender as yourself to switch rooms with.");
                $formCmd->redirect();
            }

            // Check to see if the other student is already involved in a room change request
            $swapStudentReq = RoomChangeRequestFactory::getPendingByStudent($swapStudent, $term);
            if(!is_null($swapStudentReq)){ // has pending request
                \NQ::simple('hms', NotificationView::ERROR, 'The student you are requesting to swap with already has a pending room change request. You cannot request to swap with him/her until the pending request is processed.');
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
                $hall = new ResidenceHall($firstHallPref);
                if (!is_null($hall->getId())) {
                    $participant->setHallPref1($hall);
                }
            }

            if (!empty($secondHallPref)) {
                $hall = new ResidenceHall($secondHallPref);
                if (!is_null($hall->getId())) {
                    $participant->setHallPref2($hall);
                }
            }

            // Save the main participant and its state
            $participant->save();

            // No further approval is required so we skip a step
            HMS_Email::sendRoomChangeCurrRDNotice($request);

        } else if($type == 'swap') {
            // Swapping with another student, so handle the other particpant

            // Set main participant's toBed to other student's bed
            $participant->setToBed($swapBed);

            // Create the other participant
            $swapParticipant = new RoomChangeParticipant($request, $swapStudent, $swapBed);

            // Set other student's toBed to main participant's bed
            $swapParticipant->setToBed($bed);
            $swapParticipant->save();


            // Save the main participant and its state
            $participant->save();

            // Send "request needs your approval" to other students
            // TODO: When you add the ability to have many people on this request, you will
            //       need to put a foreach loop here or something.
            HMS_Email::sendRoomChangeParticipantNotice($participant, $swapParticipant);
        }

        // Immediately transition to the StudentApproved state.
        $participant->transitionTo(new ParticipantStateStudentApproved($participant, time(), null, UserStatus::getUsername()));

        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_ROOM_CHANGE_SUBMITTED, UserStatus::getUsername(FALSE), $reason);

        // Email sender with acknowledgment
        HMS_Email::sendRoomChangeRequestReceivedConfirmation($student);

        if ($type == 'switch') {
            \NQ::simple('hms', NotificationView::SUCCESS, 'Your room change request has been received and is pending approval. You will be contacted by your Residence Director (RD) in the next 24-48 hours regarding your request.');
        } else {
            \NQ::simple('hms', NotificationView::SUCCESS, 'Your room change request has been received. The student(s) you selected to swap with must sign-in and agree to the request. It will then be forwarded to your Residence Director and the Housing Assignments Office for approval.');
        }
        $menuCmd->redirect();
    }
}
