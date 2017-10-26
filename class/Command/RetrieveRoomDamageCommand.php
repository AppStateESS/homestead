<?php

namespace Homestead\Command;

use \Homestead\PdoFactory;

class RetrieveRoomDamageCommand extends Command {

    private $roomPersistentId;

    public function setRoomPersistentId($id)
    {
        $this->roomPersistentId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'RetrieveRoomDamage',
        'roomPersistentId' => $this->roomPersistentId);
    }

    public function execute(CommandContext $context)
    {
        $this->setRoomPersistentId($context->get('roomPersistentId'));

        $db = PdoFactory::getPdoInstance();

        $query = "select hms_room_damage.id, term, side, damage_type, to_char(to_timestamp(reported_on), 'MM/DD/YY') as reported_on, note, hms_damage_type.category, hms_damage_type.description FROM hms_room_damage JOIN hms_damage_type ON hms_room_damage.damage_type = hms_damage_type.id WHERE room_persistent_id = :persistentId and repaired = 0";

        $stmt = $db->prepare($query);

        $params = array(
            'persistentId' => $this->roomPersistentId
        );

        $stmt->execute($params);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode($results);
        exit;
    }
}
