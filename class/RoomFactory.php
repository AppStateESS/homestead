<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;

class RoomFactory {

    public static function getRoomByPersistentId($roomId, $term)
    {
        if(is_null($roomId) || $roomId == ''){
            throw new \InvalidArgumentException('Missing roomId parameter.');
        }

        if(is_null($term) || $term == ''){
            throw new \InvalidArgumentException('Missing term parameter.');
        }

        $db = new \PHPWS_DB('hms_room');

        $db->addWhere('term', $term);
        $db->addWhere('persistent_id', $roomId);

        $room = new Room(0);
        $result = $db->loadObject($room);

        if(\PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return $room;
    }

    // Retrieves bed by regular Id
	public static function getRoomById($roomId, $term)
    {
    	$db = PdoFactory::getPdoInstance();

        $query = "select * from hms_room where id = :roomId AND term = :term";
        $stmt = $db->prepare($query);

        $params = array(
                    'roomId' 	   => $roomId,
                    'term'         => $term
		);
        $stmt->execute($params);

        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, '\Homestead\RoomRestored');

        return $results[0];
    }

}
