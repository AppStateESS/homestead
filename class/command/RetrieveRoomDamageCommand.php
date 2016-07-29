<?php

PHPWS_Core::initModClass('hms', 'Command.php');

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

      $query = "select id, term, side, damage_type, reported_on, note from hms_room_damage where room_persistent_id = :persistentId and repaired = 0";

      $stmt = $db->prepare($query);

      $params = array(
        'persistentId' => $this->roomPersistentId
      );

      $stmt->execute($params);

      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $damageTypeQuery = "select category, description from hms_damage_type where id = :damageType";

      $i = 0;
      $converted = array();

      foreach ($results as $row) {
        $dmgType = $row['damage_type'];

        $stmt = $db->prepare($damageTypeQuery);

        $damageTypeParams = array(
          'damageType' => $dmgType
        );

        $stmt->execute($damageTypeParams);

        $result = $stmt->fetch();

        $row['category'] = $result['category'];
        $row['description'] = $result['description'];


        $row['reported_on'] = date('m/d/Y', $row['reported_on']);
        array_push($converted, $row);
      }

      echo json_encode($converted);
      exit;
    }
}
