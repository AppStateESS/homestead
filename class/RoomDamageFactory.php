<?php

PHPWS_Core::initModClass('hms', 'RoomDamage.php');

class RoomDamageFactory {
    
    public static function getDamagesByRoom(HMS_Room $room)
    {
        $db = new PHPWS_DB('hms_room_damage');
        
        $db->addWhere('room_persistent_id', $room->getPersistentId());
        $db->addWhere('repaired', 0);
        $result = $db->getObjects('RoomDamageDb');
        
        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        
        return $result;
    }
}

?>