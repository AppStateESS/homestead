<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'HMS_Email.php');

/**
* Command to get the data that may change about a room change request
*
* @author Chris Detsch
* @package hms
*/
class RoomChangeGetDetailsCommand extends Command {

    private $participantId;

    public function setParticipantId($id)
    {
        $this->participantId = $id;
    }

    public function getRequestVars()
    {
        return array('action'           => 'RoomChangeGetDetails',
                     'participantId'    => $this->participantId);
    }

    public function execute(CommandContext $context)
    {
        $term = Term::getCurrentTerm();

        $participantId = $context->get('participantId');

        $participant = RoomChangeParticipantFactory::getParticipantById($participantId);

        $from = BedFactory::getBedById($participant->getFromBed())->where_am_i();

        $toBedId = $participant->getToBed();
        if($toBedId != NULL) {
            $to = BedFactory::getBedById($toBedId)->where_am_i();
        } else {
            $to = null;
        }

        $data = array();

        $data['participant'] = $participant;
        $data['fromBed'] = $from;
        $data['toBed'] = $to;

        echo json_encode($data);
        exit;
    }
}
