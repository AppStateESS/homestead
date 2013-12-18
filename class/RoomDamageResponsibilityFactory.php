<?php

PHPWS_Core::initModClass('hms', 'RoomDamageResponsibility.php');

class RoomDamageResponsibilityFactory {

    public static function getResponsibilitiesByDmg(RoomDamage $damage)
    {
        $query = "select * from hms_room_damage_responsibility where damage_id = :damageId";

        $db = PdoFactory::getPdoInstance();
        $stmt = $db->prepare($query);

        $params = array('damageId' => $damage->getId());

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'RoomDamageResponsibilityRestored');
    }

    public static function save(RoomDamageResponsibility $resp)
    {
        $db = PdoFactory::getPdoInstance();

        $id = $resp->getId();

        if (isset($id)) {
            // Update
            // TODO
            throw new Exception('Not yet implemented.');

            $query = "";
            $params = array();

        }else{
            // Insert
            $query = "INSERT INTO hms_room_damage_responsibility (id, damage_id, banner_id, state, amount) VALUES (nextval('hms_room_damage_responsibility_seq'), :damageId, :bannerId, :state, :amount)";

            $params = array(
                    'damageId'  => $resp->getDamageId(),
                    'bannerId'  => $resp->getBannerId(),
                    'state'     => $resp->getState(),
                    'amount'    => $resp->getAmount()
            );
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        // Update ID for a new object
        if (!isset($id)) {
            $resp->setId($db->lastInsertId('hms_room_damage_responsibility_seq'));
        }
    }
}

?>