<?php

PHPWS_Core::initModClass('hms', 'RoomChangeParticipantState.php');

class RoomChangeParticipantStateFactory {

    public static function getCurrentStateForParticipant(RoomChangeParticipant $participant)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_participant WHERE participant_id = :participantId";

        $stmt = $db->prepare($query);
        $stmt->execute(array('participantId' => $participant->getId()));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $className = 'ParticipantState' . $result['state_name'];

        return new $className($participant, $result['effective_date'], $result['effective_until_date'], $result['committed_by']);
    }

    public static function getStateHistory(RoomChangeParticipant $participant)
    {
        //TODO
    }
}

?>