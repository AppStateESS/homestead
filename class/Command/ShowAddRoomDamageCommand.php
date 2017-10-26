<?php

namespace Homestead\Command;

use \Homestead\HMS_Room;
use \Homestead\RoomFactory;
use \Homestead\AddRoomDamageView;

class ShowAddRoomDamageCommand extends Command{

    private $room;

    public function setRoom(HMS_Room $room)
    {
        $this->room = $room;
    }

    public function getRequestVars()
    {
        $persistentId = $this->room->getPersistentId();

        if(!isset($persistentId)){
            throw new \Exception('Missing room persistent Id.');
        }

        return array('action'=>'ShowAddRoomDamage', 'roomId'=>$this->room->getPersistentId(), 'term'=>$this->room->getTerm());
    }

    public function getLink($text, $target = null, $cssClass = null, $title = null)
    {
        $uri = $this->getURI();
        return "<a href=\"$uri\" id=\"addDamageLink\" onClick=\"return false;\">$text</a>";
    }

    public function execute(CommandContext $context)
    {
        $roomId = $context->get('roomId');

        if (!isset($roomId)) {
            throw new \InvalidArgumentException('Missing room id.');
        }

        $term = $context->get('term');

        if (!isset($term)) {
            throw new \InvalidArgumentException('Missing room term.');
        }

        $room = RoomFactory::getRoomByPersistentId($roomId, $term);


        $view = new AddRoomDamageView($room);

        echo $view->show();
        exit;
    }
}
