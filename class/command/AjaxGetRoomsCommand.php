<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetRoomsCommand extends Command {

    private $floorId;

    public function getRequestVars(){
        return array('action'=>'AjaxGetRooms');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        //TODO check for a hallId

        $floor = new HMS_Floor($context->get('floorId'));

        $roomsResult = $floor->get_rooms();

        $rooms = array();
        $i = 0;

        foreach ($roomsResult as $room)
        {
          $rooms[$i]['room_number'] = $room->getRoomNumber();
          $rooms[$i]['room_id'] = $room->getId();
          $i++;
        }

        $context->setContent(json_encode($rooms));
    }
}
