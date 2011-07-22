<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetRoomsWithVacanciesCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'AjaxGetRoomsWithVacancies');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        //TODO check for a floorId

        $floor = new HMS_Floor($context->get('floorId'));

        $rooms = $floor->getRoomsWithVacancies();
         
        $json_rooms = array();
        $json_rooms[0] = 'Select ...';

        foreach ($rooms as $room){
            unset($text);

            $text = $room->room_number;

            if($floor->gender_type == COED){
                $text .= (' (' . HMS_Util::formatGender($room->gender_type) . ')');
            }

            if($room->ra_room == 1){
                $text .= (' (RA)');
            }

            $json_rooms[$room->id] = $text;
        }

        $context->setContent(json_encode($json_rooms));
    }
}

?>