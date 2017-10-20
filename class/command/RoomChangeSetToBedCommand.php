<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');

/**
 * Command for current RD or Admin to set the to bed for a room change participant.
 *
 * @author Chris Detsch
 * @package hms
 */
class RoomChangeSetToBedCommand extends Command {

    private $participantId;
    private $bedId;
    private $oldBedId;


    public function setParticipantId($id)
    {
        $this->participantId = $id;
    }

    public function setBedId($id)
    {
        $this->bedId = $id;
    }

    public function setOldBed($id)
    {
        $this->oldBedId = $id;
    }

    public function getRequestVars()
    {
        return array('action'           => 'RoomChangeSetToBed',
                     'participantId'    => $this->participantId,
                     'bedId'            => $this->bedId,
                     'oldBedId'         => $this->oldBed);
    }

    public function execute(CommandContext $context)
    {
        // Must have room change approval permissions
        if(!Current_User::allow('hms', 'admin_approve_room_change')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve/modify room changes.');
        }

        $this->setParticipantId($context->get('participantId'));
        $this->setBedId($context->get('bedId'));
        $this->setOldBed($context->get('oldBed'));

        $bed = new HMS_Bed($this->bedId);
        if($this->oldBedId != -1) {
          $oldBed = new HMS_Bed($this->oldBedId);
        }

        // Load the participant
        $participant = RoomChangeParticipantFactory::getParticipantById($this->participantId);

        // Check that the bed isn't already reserved for a room change
        if($bed->isRoomChangeReserved()){
            NQ::simple('hms'  , hms\NotificationView::ERROR, 'The bed you selected is already reserved for a room change. Please choose a different bed.');
            $cmd->redirect();
        }


        // Reserve the bed for room change
        $bed->setRoomChangeReserved();
        $bed->save();


        if($this->oldBedId != -1) {
          $oldBed->clearRoomChangeReserved();
          $oldBed->save();
        }

        // Save the bed to this participant
        $participant->setToBed($bed);
        $participant->save();
        $context->setContent(json_encode(''));
    }
}
