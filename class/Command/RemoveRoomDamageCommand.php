<?php

namespace Homestead\Command;

use \Homestead\PdoFactory;

class RemoveRoomDamageCommand extends Command {

    private $room;



    public function getRequestVars()
    {
        return array('action'=> 'RemoveRoomDamage',
                     'roomDamageId'=> $this->room->getPersistentId());
    }

    public function execute(CommandContext $context)
    {
        $dmgId = $context->get('roomDamageId');

        $db = PdoFactory::getPdoInstance();

        $query = "delete from hms_room_damage where id = :damageId";

        $stmt = $db->prepare($query);

        $params = array(
          'damageId' => $dmgId
        );

        $stmt->execute($params);

        echo 'success';
        exit;
    }
}
