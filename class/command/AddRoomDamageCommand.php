<?php

PHPWS_Core::initModClass('hms', 'RoomFactory.php');
PHPWS_Core::initModClass('hms', 'RoomDamage.php');

class AddRoomDamageCommand extends Command {
    
    private $room;
    
    public function setRoom(HMS_Room $room)
    {
        $this->room = $room;
    }
    
    public function getRequestVars()
    {
        return array('action'=> 'AddRoomDamage',
                     'roomId'=> $this->room->getPersistentId(),
                     'term'  => $this->room->getTerm());
    }
    
    public function execute(CommandContext $context)
    {
        $roomId = $context->get('roomId');
        $damageType = $context->get('damage_type');
        $term = $context->get('term');
        
        $note = ''; //TODO
        
        $room = RoomFactory::getRoomByPersistentId($roomId, $term);

        $damage = new RoomDamage($room, $damageType, $note);
        
        $db = new PHPWS_DB('hms_room_damage');
        $result = $db->saveObject($damage);
        
        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }
        
        echo 'success';
        exit;
    }
}

?>