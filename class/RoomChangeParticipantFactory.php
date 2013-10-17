<?php

PHPWS_Core::initModClass('hms', 'RoomChangeParticipant.php');

class RoomChangeParticipantFactory {


    public static function getParticipantById($id)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_participant WHERE id = :participantId";

        $stmt = $db->prepare($query);

        $params = array(
            'participantId' => $id
        );

        $stmt->execute($params);

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'RoomChangeParticipantRestored');

        return $stmt->fetch();
    }

    public static function getParticipantsByRequest(RoomChangeRequest $request)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_participant WHERE request_id = :request_id";

        $stmt = $db->prepare($query);

        $params = array(
            'request_id' => $request->getId()
        );

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'RoomChangeParticipantRestored');
    }
}

?>