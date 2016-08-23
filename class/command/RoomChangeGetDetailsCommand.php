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

        $db = PdoFactory::getPdoInstance();

        $query = "select from_bed, to_bed from hms_room_change_participant where id = :participantId";
        $stmt = $db->prepare($query);

        $params = array('participantId' => $participantId);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = $results[0];

        $from = $results['from_bed'];
        $from = BedFactory::getBedByTermWithId($from, $term)->where_am_i();

        if($results['to_bed'] != NULL) {
            $to = $results['to_bed'];
            $to = BedFactory::getBedByTermWithId($to, $term)->where_am_i();
        } else {
            $to = "TBD";
        }

        $results['from'] = $from;
        $results['to'] = $to;

        echo json_encode($results);
        exit;
    }
}
