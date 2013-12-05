<?php
PHPWS_Core::initModClass('hms', 'RoomDamage.php');


/**
 * RoomDamageFactory - Factory class with various
 * static utilitiy methods for loading RoomDamage
 * objects from the database.
 *
 * @author jbooker
 * @package hms
 */
class RoomDamageFactory {

    /**
     * Returns a set of RoomDamage objects representing all
     * the room damages for the given room.
     *
     * @param HMS_Room $room
     * @throws DatabaseException
     * @return Array<RoomDamage> null
     */
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

    /**
     * Returns the set of RoomDamage objects that were created before
     * the give timestmap.
     *
     * @param HMS_Room $room
     * @param unknown $timestamp
     * @throws DatabaseException
     * @throws InvalidArgumentException
     * @return Array<RoomDamage> null
     */
    public static function getDamagesBefore(HMS_Room $room, $timestamp)
    {
        if(!isset($timestamp)){
            throw new InvalidArgumentException('Missing timestamp.');
        }

        $db = new PHPWS_DB('hms_room_damage');

        $db->addWhere('room_persistent_id', $room->getPersistentId());
        $db->addWhere('repaired', 0);

        $db->addWhere('reported_on', $timestamp, '<=');

        $result = $db->getObjects('RoomDamageDb');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public function save(RoomDamage $dmg)
    {
        $db = PdoFactory::getPdoInstance();

        $id = $dmg->getId();
        if (isset($id)) {
            // Update
            // TODO
            throw new Exception('Not yet implemented.');

            $query = "";
            $params = array();

        }else{
            // Insert
            $query = "INSERT INTO hms_room_damage (id, room_persistent_id, term, damage_type, note, repaired, reported_by, reported_on, side) VALUES (nextval('hms_room_damage_seq'), :persistentId, :term, :damageType, :note, :repaired, :reportedBy, :reportedOn, :side)";

            $params = array(
                    'persistentId'  => $dmg->getRoomPersistentId(),
                    'term'          => $dmg->getTerm(),
                    'damageType'    => $dmg->getDamageType(),
                    'note'          => $dmg->getNote(),
                    'repaired'      => $dmg->isRepaired() ? 1 : 0,
                    'reportedBy'    => $dmg->getReportedBy(),
                    'reportedOn'    => $dmg->getReportedOn(),
                    'side'          => $dmg->getSide()
            );
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        // Update ID for a new object
        if (!isset($id)) {
            $dmg->setId($db->lastInsertId('hms_room_damage_seq'));
        }
    }
}

?>