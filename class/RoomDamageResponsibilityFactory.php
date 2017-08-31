<?php

namespace Homestead;

class RoomDamageResponsibilityFactory {

    public static function getResponsibilitiesByDmg(RoomDamage $damage)
    {
        $query = "select * from hms_room_damage_responsibility where damage_id = :damageId";

        $db = PdoFactory::getPdoInstance();
        $stmt = $db->prepare($query);

        $params = array('damageId' => $damage->getId());

        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_CLASS, '\Homestead\RoomDamageResponsibilityRestored');
    }

    public static function getResponsibilityById($id)
    {
        $query = "select * from hms_room_damage_responsibility where id = :id";

        $db = PdoFactory::getPdoInstance();
        $stmt = $db->prepare($query);

        $params = array('id' => $id);

        $stmt->execute($params);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\Homestead\RoomDamageResponsibilityRestored');

        return $stmt->fetch();
    }

    public static function save(RoomDamageResponsibility $resp)
    {
        $db = PdoFactory::getPdoInstance();

        $id = $resp->getId();

        if (isset($id)) {
            $query = "UPDATE hms_room_damage_responsibility SET (state, amount, assessed_on, assessed_by) = (:state, :amount, :assessedOn, :assessedBy) WHERE id = :id and damage_id = :damageId and banner_id = :bannerId";

            $params = array(
                        'id' => $resp->getId(),
                        'damageId'      => $resp->getDamageId(),
                        'bannerId'      => $resp->getBannerId(),
                        'state'         => $resp->getState(),
                        'amount'        => $resp->getAmount(),
                        'assessedBy'    => $resp->getAssessedBy(),
                        'assessedOn'    => $resp->getAssessedOn()
            );

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
