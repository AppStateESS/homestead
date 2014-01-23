<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');

/**
 * Controller for approving a room change requests.
 *
 * Handles reassigning each participant, releasing room change reservations,
 * updating the request's state, and updating each participant's state
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeApproveCommand extends Command {

    private $students; // Array of student objects corresponding to participants, indexed by bannerid
    private $assigmentReasons; // Array of existing assignment reasons for each student, indexed by banner id

    public function getRequestVars()
    {
        return array('action'=>'RoomChangeApproveCommand');
    }

    public function execute(CommandContext $context)
    {
        // Get input
        $requestId = $context->get('requestId');

        // Get the current term
        $term = Term::getCurrentTerm();

        // Load the request
        $request = RoomChangeRequestFactory::getRequestById($requestId);

        // Load the participants
        $participants = $request->getParticipants();

        // Transition the request to 'Approved'
        $request->transitionTo(new RoomChangeStateApproved($request, time(), null, UserStatus::getUsername()));

        // Remove each participants existing assignment
        foreach ($participants as $participant) {
            $bannerId = $participant->getBannerId();

            // Lookup the student
            $student = StudentFactory::getStudentByBannerId($bannerId, $term);

            // Save student object for later
            $this->students[$bannerId] = $student;

            // Save student's current assignment reason for later re-use
            $assignment = HMS_Assignment::getAssignmentByBannerId($bannerId, $term);
            $this->assignmentReasons[$bannerId] = $assignment->getReason();

            // Remove existing assignment
            HMS_Assignment::unassignStudent($student, $term, 'Room Change Request Approved', UNASSIGN_CHANGE);
        }

        // Create new assignments for each participant
        foreach ($participants as $participant) {
            // Grab the student object which was previously saved
            $student = $this->students[$participant->getBannerId()];

            // Create each new assignment
            HMS_Assignment::assignStudent($student, $term, null, $participant->getToBed(), BANNER_MEAL_STD, 'Room Change Approved', FALSE, $this->assignmentReasons[$bannerId]);

            // Release bed reservation
            $bed = new HMS_Bed($participant->getToBed());
            $bed->clearRoomChangeReserved();
            $bed->save();
        }

        // Transition each participant to 'In Process'
        foreach ($participants as $participant) {
            $participant->transitionTo(new ParticipantStateInProcess($participant, time(), null, UserStatus::getUsername()));
            // TODO: Send notifications
        }

        // Notify everyone that they can do the move
        HMS_Email::sendRoomChangeInProcessNotice($r);

        // Return the user to the room change request page
        $cmd = CommandFactory::getCommand('ShowManageRoomChange');
        $cmd->setRequestId($requestId);
        $cmd->redirect();
    }
}

?>
