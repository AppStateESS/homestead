<?php

namespace Homestead;

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

        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'RoomChangeParticipantRestored');

        return $stmt->fetch();
    }

    public static function getParticipantsByRequest(RoomChangeRequest $request)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_participant WHERE request_id = :request_id ORDER BY banner_id ASC";

        $stmt = $db->prepare($query);

        $params = array(
            'request_id' => $request->getId()
        );

        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_CLASS, 'RoomChangeParticipantRestored');
    }

    public static function getParticipantByRequestStudent(RoomChangeRequest $request, Student $student)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_participant WHERE request_id = :request_id and banner_id = :bannerId";

        $stmt = $db->prepare($query);

        $params = array(
            'request_id' => $request->getId(),
            'bannerId'   => $student->getBannerId()
        );

        $stmt->execute($params);

        $stmt->setFetchMode(\PDO::FETCH_CLASS, 'RoomChangeParticipantRestored');
        return $stmt->fetch();
    }
}
