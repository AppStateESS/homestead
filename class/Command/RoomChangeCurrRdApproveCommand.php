<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\RoomChangeRequestFactory;
use \Homestead\RoomChangeParticipantFactory;
use \Homestead\UserStatus;
use \Homestead\NotificationView;
use \Homestead\Student;
use \Homestead\Bed;
use \Homestead\HMS_Email;
use \Homestead\ParticipantStateCurrRdApproved;
use \Homestead\Exception\PermissionException;

/**
 * Command for currnet RD Approval of a room change participant.
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeCurrRdApproveCommand extends Command {

    private $requestId;
    private $participantId;

    public function setRequestId($id)
    {
        $this->requestId = $id;
    }

    public function setParticipantId($id)
    {
        $this->participantId = $id;
    }

    public function getRequestVars()
    {
        return array('action'           => 'RoomChangeCurrRdApprove',
                     'requestId'        => $this->requestId,
                     'participantId'    => $this->participantId);
    }

    public function execute(CommandContext $context)
    {
        // Get input
        $requestId = $context->get('requestId');
        $participantId = $context->get('participantId');

        // destinationBedId - This can be null for "swap" requests, because it's already known
        $toBedSelected = $context->get('bed_select');


        // Command for showing the request, redirected to on success/error
        $cmd = CommandFactory::getCommand('ShowManageRoomChange');
        $cmd->setRequestId($requestId);

        // Load the request
        $request = RoomChangeRequestFactory::getRequestById($requestId);

        // Load the participant
        $participant = RoomChangeParticipantFactory::getParticipantById($participantId);

        // Check permissions. Must be an RD for current bed, or an admin
        $rds = $participant->getCurrentRdList();
        if (!in_array(UserStatus::getUsername(), $rds) && !\Current_User::allow('hms', 'admin_approve_room_change')) {
            throw new PermissionException('You do not have permission to approve this room change.');
        }

        // Check that a destination bed has already been set, or that the RD
        // has just selected a bed
        $toBedId = $participant->getToBed();

        if (is_null($toBedId) && $toBedSelected == '-1') {
            \NQ::simple('hms', NotificationView::ERROR, 'Please select a destination bed.');
            $cmd->redirect();
        }

        // Set the selected bed, if needed
        if (is_null($toBedId) && $toBedSelected != '-1') {
            $bed = new Bed($toBedSelected);

            // Check that the bed isn't already reserved for a room change
            if($bed->isRoomChangeReserved()){
                \NQ::simple('hms', NotificationView::ERROR, 'The bed you selected is already reserved for a room change. Please choose a different bed.');
                $cmd->redirect();
            }

            // Reserve the bed for room change
            $bed->setRoomChangeReserved();

            $bed->save();

            // Save the bed to this participant
            $participant->setToBed($bed);
            $participant->save();
        }

        // Transition to CurrRdApproved
        $participant->transitionTo(new ParticipantStateCurrRdApproved($participant, time(), null, UserStatus::getUsername()));

        // If the future RD is the same as the current user Logged in, then go ahead and transition to FutureRdApproved too.
        //TODO

        if($request->isApprovedByAllCurrentRDs()) {
            // If all Current RDs have approved, notify Future RDs
            HMS_Email::sendRoomChangeFutureRDNotice($request);

            // If all Current RDs have approved, notify future roommates
            foreach($request->getParticipants() as $p) {
                $bed = new Bed($p->getToBed());
                $room = $bed->get_parent();

                foreach($room->get_assignees() as $a) {
                    if($a instanceof Student && $a->getBannerID() != $p->getBannerID()) {
                        HMS_Email::sendRoomChangePreliminaryRoommateNotice($a);
                    }
                }
            }
        }

        // Redirect to the manage request page
        $cmd->redirect();
    }
}
