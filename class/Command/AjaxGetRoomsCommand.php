<?php

namespace Homestead\Command;

use \Homestead\Floor;

class AjaxGetRoomsCommand extends Command {

    private $floorId;

    public function getRequestVars(){
        return array('action'=>'AjaxGetFloors');
    }

    public function execute(CommandContext $context)
    {
        //TODO check for a hallId

        $floor = new Floor($context->get('floorId'));

        $rooms = $floor->get_rooms();

        $json_rooms = array();
        $json_rooms[0] = 'Select...';

        foreach ($rooms as $room){
            $json_rooms[$room->id] = $room->room_number;
        }

        $context->setContent(json_encode($json_rooms));
    }
}
