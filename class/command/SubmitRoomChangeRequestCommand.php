<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeParticipant.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');

class SubmitRoomChangeRequestCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRoomChangeRequest');
    }

    public function execute(CommandContext $context){


        // Cmd to redirect to when we're done or upon error.
        $returnCmd = CommandFactory::getCommand('ShowRoomChangeRequestForm');
        $menuCmd = CommandFactory::getCommand('ShowStudentMenu');

        // Get input
        $cellNum = $context->get('phoneNumber');
        $optOut  = $context->get('cellOptOut');

        // Get the hall preferences
        $firstHallPref  = $context->get('firstChoice');
        $secondHallPref = $context->get('secondChoice');

        // Retrieve the array containing the users and the beds they are looking to move to.
        $userToBeds = $context->get('userToBed');

        // Retrieve the type of this room change, should be 'swap', indicating a multi user swap,
        // or 'switch', indicating a single user looking to move where ever they can, with preferences
        $type = $context->get('type');

        // Initialize the variable for containing the information sent back on success or error,
        // this should contain a url, either redirecting back to the form or to the menu.
        $returnMsg = array();



        // Retrieve the current term.
        $term = Term::getCurrentTerm();

        // Create the student object
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);

        $initGender = $student->getGender();

        // Make sure the student is currently assigned
        $assignment = HMS_Assignment::getAssignmentByBannerId($student->getBannerId(), $term);

        if (is_null($assignment)) {
            // The student does not currently have an assignment and an error case has happened,
            // this will return to the menu and display the error via the NotificationView.
            NQ::simple('hms', hms\NotificationView::ERROR, 'You are not currently assigned to a room, so you cannot request a room change.');
            $returnMsg = array('status' => 'invalid', 'url' => $menuCmd->getURI(), 'desc' => 'No Assignment');
            echo json_encode($returnMsg);
            NQ::close();
            exit;
        }

        // Get the HMS_Bed object corresponding to the student's current assignment
        $thisUserBed = $assignment->get_parent();
        $room = $thisUserBed->get_parent();

        // Check for an existing room change request
        $changeReq = RoomChangeRequestFactory::getPendingByStudent($student, $term);

        if(!is_null($changeReq)){
            // The student already has pending request, that request needs to either be
            // denied or completeed before the student can request to move again.
            NQ::simple('hms', hms\NotificationView::ERROR,
              'You already have a pending room change request. You cannot submit another request until it is processed.');
            $returnMsg = array('status' => 'invalid', 'url' => $menuCmd->getURI(), 'desc' => 'Pending Room Change');
            echo json_encode($returnMsg);
            NQ::close();
            exit;
        }

        // Check the format of the cell phone number
        if(isset($cellNum)){
            // Filter out non-numeric characters
            $cellNum = preg_replace("/[^0-9]/", '', $cellNum);
        }

        // Check that a cell phone number was provided, or that the opt-out box was checked.
        if(($cellNum == "" || empty($cellNum)) && !isset($optOut)){
            // There was no cell phone and the opt out check box was not selected so an
            // error will appear on the form page, this will be handled on the front end.
            $errorInfo = array('error' => 'missing_value', 'value' => 'phone');
            $returnMsg = array('status' => 'error', 'error' => $errorInfo);
            echo json_encode($returnMsg);
            exit;
        }

        $reason = $context->get('reason');

        // Make sure a 'reason' was provided.
        if($reason == '' || empty($reason)){
            // There was no reason given so an error will appear on the form page, this
            // will be handled on the front end.
            $errorInfo = array('error' => 'missing_value', 'value' => 'reason');
            $returnMsg = array('status' => 'error', 'error' => $errorInfo);
            echo json_encode($returnMsg);
            exit;
        }

        // Extra sanity checks if we're doing a switch
        if ($type == 'swap') {
            // Initialize the users array
            $users = array();
            $beds = array();
            $i = 0;
            foreach ($userToBeds as $node)
            {
              foreach($users as $user)
              {
                if($user == $node['user'])
                {
                  // The username was found to already have been in the array indicating that the
                  // username was a duplicate and an error case has occurred. This most likely means that
                  // someone is attempting to submit to the server without using the webpage form as the
                  // original front end code should make it so the user cannot do this.

                  $errorInfo = array('error' => 'duplicate_users', 'message' => 'The participant ' . $node['user'] . ' was entered twice.');
                  $returnMsg = array('status' => 'error', 'error' => $errorInfo);
                  header("HTTP/1.1 500 Internal Server Error");
                  echo json_encode($returnMsg);
                  exit;
                }
              }

              foreach ($beds as $bed)
              {
                if($bed == $node['bedId'])
                {
                  // The bedId was found to already have been in the array indicating that the
                  // bedId was a duplicate and an error case has occurred. This most likely means that
                  // someone is attempting to submit to the server without using the form as the original
                  // front end should make it so the user cannot do this.
                  $errorInfo = array('error' => 'duplicate_bed', 'message' => 'A bed was chosen for more than one user at a time.');
                  $returnMsg = array('status' => 'error', 'error' => $errorInfo);
                  header("HTTP/1.1 500 Internal Server Error");
                  echo json_encode($returnMsg);
                  exit;
                }
              }

              // Having checked to make sure they are not duplicates the node values are added to their
              // respective arrays.
              $users[$i] = $node['user'];
              $beds[$i] = $node['bedId'];
              $i++;
            }

            // Now that we have checked for duplicates we need to check to see that the users are valid
            // and that the beds are valid.
            $actualBeds = array();
            foreach ($userToBeds as $node)
            {
                $switchUsername = $node['user'];

                // Load the other student
                try {
                  $swapStudent = StudentFactory::getStudentByUsername($switchUsername, $term);
                } catch(StudentNotFoundException $e){
                  // The student did not exist so an error will be thrown back to the front end
                  // with the username of the student, most likely this has happened because the
                  // values were passed without using the web page, as the front end keeps an invalid user
                  // from being picked.
                  $errorInfo = array('error' => 'invalid_user', 'message' => 'A student with the username '. $switchUsername . ' does not exist in our records.');
                  $returnMsg = array('status' => 'error', 'error' => $errorInfo);
                  header("HTTP/1.1 500 Internal Server Error");
                  echo json_encode($returnMsg);
                  exit;
                }

                if($swapStudent->getGender() != $initGender)
                {
                  // This student is not the same gender as the student who initialized this room change.
                  // At this point coed rooms are not allowed.
                  $errorInfo = array('error' => 'invalid_gender', 'message' => 'Error occurred. Please ensure that all participants are of the same sex.');
                  $returnMsg = array('status' => 'error', 'error' => $errorInfo);
                  header("HTTP/1.1 500 Internal Server Error");
                  echo json_encode($returnMsg);
                  exit;
                }

                // Make sure the student is assigned
                $swapAssignment = HMS_Assignment::getAssignmentByBannerId($swapStudent->getBannerId(), $term);
                if (is_null($swapAssignment)) {
                  // This student is not currently assigned and an error will be thrown back to the front end
                  // with the username of the student who is not assigned.
                  $errorInfo = array('error' => 'unassigned_user', 'message' => 'The student with username ' . $switchUsername.' does not appear to be assigned at present, so cannot participate in a room change.');
                  $returnMsg = array('status' => 'error', 'error' => $errorInfo);
                  header("HTTP/1.1 500 Internal Server Error");
                  echo json_encode($returnMsg);
                  exit;
                }

                $actualBeds[$i] = $swapAssignment->getBedId();

                // Check to see if the other student is already involved in a room change request
                $swapStudentReq = RoomChangeRequestFactory::getPendingByStudent($swapStudent, $term);
                if(!is_null($swapStudentReq)){
                  // This student already has pending request for a room change and will not be able to
                  // attempt another until the current one is completed/rejected, an error will be thrown to
                  // the front end, with the username, in order to inform the user of this fact.
                  $errorInfo = array('error' => 'already_room_change', 'message' => 'The student with username '. $switchUsername . ' is already involved in a room change and cannot be involved in another at this time.');
                  $returnMsg = array('status' => 'error', 'error' => $errorInfo);
                  header("HTTP/1.1 500 Internal Server Error");
                  echo json_encode($returnMsg);
                  exit;
                }
                $i++;
            }

            // Loops through the beds checking to make sure that the beds that were
            // given as input are all the beds of the users involved.
            foreach($beds as $inputBed)
            {
              $check = false;
              foreach($actualBeds as $actualBed)
              {
                if($actualBed == $inputBed)
                {
                    $check = true;
                }
              }
              if(!$check)
              {
                // There was a bed that did not match the beds of any of the users involved,
                // this is an error case and will be sent back to the front end, this really shouldnt happen
                // unless someone is sending values directly to the server without using the front end though.
                $errorInfo = array('error' => 'invalid_bedId', 'message' => 'Input contained a bed that none of the participants are currently assigned to.');
                $returnMsg = array('status' => 'error', 'error' => $errorInfo);
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode($returnMsg);
                exit;
              }
            }

        }

        //create the request object
        $request = new RoomChangeRequest($term, $reason);
        $request->save();

        // Creates a participant out of the student submitting this room change.
        $participant = new RoomChangeParticipant($request, $student, $thisUserBed);

        // Switching to a different room, so set params on main participant
        if($type == 'switch') {
            // Sets the cell phone number of the participant if a cell was given.
            if (isset($cellNum)) {
              $participant->setCellPhone($cellNum);
            }

            // Sets the hall preferences for the participant if they were given.
            if (!empty($firstHallPref)) {
                $hall = new HMS_Residence_Hall($firstHallPref);
                if (!is_null($hall->getId())) {
                    $participant->setHallPref1($hall);
                }
            }

            if (!empty($secondHallPref)) {
                $hall = new HMS_Residence_Hall($secondHallPref);
                if (!is_null($hall->getId())) {
                    $participant->setHallPref2($hall);
                }
            }

            // Save the main participant and its state
            $participant->save();

            // No further approval is required so we skip a step
            HMS_Email::sendRoomChangeCurrRDNotice($request);

        }
        else if($type == 'swap')
        {
          foreach ($userToBeds as $node)
          {
              // Get the username of the next user involved in the switch
              $switchUsername = $node['user'];

              // Use the student factory to get the student object using the username and term.
              $swapStudent = StudentFactory::getStudentByUsername($switchUsername, $term);

              // Make sure the student is currently assigned
              $assignment = HMS_Assignment::getAssignmentByBannerId($swapStudent->getBannerId(), $term);

              // Get the HMS_Bed object corresponding to the student's current assignment
              $bed = $assignment->get_parent();

              // Create a RoomChangeParticipant object using the request, student, and bed.
              $swapParticipant = new RoomChangeParticipant($request, $swapStudent, $bed);

              // Get the bedId of the bed that this student will be moving to.
              $toBedId = $node['bedId'];

              // Use this BedFactory to create the bed object from the bedId.
              $toBed = BedFactory::getBedByBedId($toBedId, $term);

              // Set participant's toBed to other student's bed
              $swapParticipant->setToBed($toBed);

              // Check to see if this is the original user, if it is set the cell phone number.
              if($student->getUsername() == $swapStudent->getUsername())
              {
                if (isset($cellNum)) {
                  $swapParticipant->setCellPhone($cellNum);
                }
              }
              // Save the participant and its state
              $swapParticipant->save();
              if($student->getUsername() == $swapStudent->getUsername())
              {
                $swapParticipant->transitionTo(new ParticipantStateStudentApproved($swapParticipant, time(), null, UserStatus::getUsername()));
              }
              // Send "request needs your approval" to other students
              HMS_Email::sendRoomChangeParticipantNotice($participant, $swapParticipant);
          }
        }

        HMS_Activity_Log::log_activity(UserStatus::getUsername(), ACTIVITY_ROOM_CHANGE_SUBMITTED, UserStatus::getUsername(FALSE), $reason);

        // Email sender with acknowledgment
        HMS_Email::sendRoomChangeRequestReceivedConfirmation($student);
        // Success message, gives the menu url to redirect back to.
        NQ::simple('hms', hms\NotificationView::SUCCESS, 'Room Change Request Successfully Submitted.');
        $returnMsg = array('status' => 'success', 'url' => $menuCmd->getURI());
        NQ::close();
        echo json_encode($returnMsg);
        exit;
    }
}
