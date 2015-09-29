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
          $text = $room->getRoomNumber();

          if($floor->gender_type == COED){
              $text .= (' (' . HMS_Util::formatGender($room->gender_type) . ')');
          }

          if($room->ra == 1){
              $text .= (' (RA)');
          }

          if($room->reserved == 1)
          {
              $text .= (' (Reserved)');
          }

          if($room->offline == 1)
          {
              $text .= (' (Offline)');
          }

          if($room->private == 1)
          {
              $text .= (' (Private)');
          }

          if($room->overflow == 1)
          {
              $text .= (' (Overflow)');
          }

          if($room->parlor == 1)
          {
              $text .= (' (Parlor)');
          }

          if($room->ada == 1)
          {
              $text .= (' (ADA)');
          }

          if($room->hearing_impaired == 1)
          {
              $text .= (' (Hearing Impaired)');
          }

          if($room->bath_en_suite)
          {
              $text .= (' (Bath En Suite)');
          }

          $rooms[$i]['room_number'] = $text;
          $rooms[$i]['room_id'] = $room->getId();
          $i++;
        }

        $context->setContent(json_encode($rooms));
    }
}
