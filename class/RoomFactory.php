<?php

PHPWS_Core::initModClass('hms', 'HMS_Room.php');

class RoomFactory {

    public static function getRoomByPersistentId($roomId, $term)
    {
        $db = new PHPWS_DB('hms_room');

        $db->addWhere('term', $term);
        $db->addWhere('persistent_id', $roomId);

        $room = new HMS_Room(0);
        $result = $db->loadObject($room);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return $room;
    }
}
