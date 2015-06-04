<?php

PHPWS_Core::initModClass('hms', 'RoomChangeRequestState.php');

class RoomChangeRequestStateFactory {

    public static function getCurrentState(RoomChangeRequest $request)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_request WHERE id = :requestId";

        $stmt = $db->prepare($query);
        $stmt->execute(array('requestId' => $request->getId()));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $className = 'RoomChangeState' . $result['state_name'];

        return new $className($request, $result['effective_date'], $result['effective_until_date'], $result['committed_by']);
    }

    public static function getStateHistory(RoomChangeRequest $request)
    {
        //TODO
    }
}

