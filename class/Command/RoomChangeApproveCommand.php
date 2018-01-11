<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\RoomChangeRequestFactory;
use \Homestead\CommandFactory;
use \Homestead\StudentFactory;
use \Homestead\Student;
use \Homestead\HMS_Assignment;
use \Homestead\Bed;
use \Homestead\HMS_Email;
use \Homestead\BannerRoomChangeStudent;
use \Homestead\RoomChangeStateApproved;
use \Homestead\UserStatus;
use \Homestead\ParticipantStateInProcess;

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

        // Make sure everyone is checked into their current assignments
        if (!$request->allParticipantsCheckedIn()) {
            // Return the user to the room change request page
            // NB, don't need an error message here because it should already be printed
            // by the RoomChangeParticipantView.
            $cmd = CommandFactory::getCommand('ShowManageRoomChange');
            $cmd->setRequestId($requestId);
            $cmd->redirect();
        }


        $bannerRoomChangeStudents = array();

        // Build BannerRoomChangeStudent object for each student
        foreach($participants as $participant){
            $bannerId = $participant->getBannerId();

            // Get the Student object
            $student = StudentFactory::getStudentByBannerId($bannerId, $term);

            // Get the student's current assignment
            $assignment = HMS_Assignment::getAssignmentByBannerId($bannerId, $term);

            // Load the bed so that we can lookup banner building and bed codes later
            $oldBed = $assignment->get_parent();

            // Load the new Bed object via its ID
            $newBed = new Bed($participant->getToBed());

            $bannerRoomChangeStudents[] = new BannerRoomChangeStudent($student, $oldBed, $newBed);
        }

        // Do all the assignment changes in HMS
        HMS_Assignment::moveAssignments($bannerRoomChangeStudents, $term);

        // Transition the request to 'Approved'
        $request->transitionTo(new RoomChangeStateApproved($request, time(), null, UserStatus::getUsername()));

        // Transition each participant to 'In Process'
        foreach ($participants as $participant) {
            $participant->transitionTo(new ParticipantStateInProcess($participant, time(), null, UserStatus::getUsername()));

            // Release bed reservation
            $bed = new Bed($participant->getToBed());
            $bed->clearRoomChangeReserved();
            $bed->save();
        }

        // Notify everyone that they can do the move
        HMS_Email::sendRoomChangeInProcessNotice($request);

        // Notify roommates that their circumstances are going to change
        foreach($participants as $p) {
            // Get the Student object
            $student = StudentFactory::getStudentByBannerId($p->getBannerId(), $term);

            // New Roommate
            $newbed = new Bed($p->getToBed());
            $newroom = $newbed->get_parent();

            foreach($newroom->get_assignees() as $a) {
                if($a instanceof Student && $a->getBannerID() != $p->getBannerID()) {
                    HMS_Email::sendRoomChangeApprovedNewRoommateNotice($a, $student);
                }
            }

            // Old Roommate
            $oldbed = new Bed($p->getFromBed());
            $oldroom = $oldbed->get_parent();
            foreach($oldroom->get_assignees() as $a) {
                if($a instanceof Student && $a->getBannerID() != $p->getBannerID()) {
                    HMS_Email::sendRoomChangeApprovedOldRoommateNotice($a, $student);
                }
            }
        }

        // Return the user to the room change request page
        $cmd = CommandFactory::getCommand('ShowManageRoomChange');
        $cmd->setRequestId($requestId);
        $cmd->redirect();
    }
}
