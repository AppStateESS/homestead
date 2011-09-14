<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetBedsCommand extends Command {

    private $roomId;

    public function getRequestVars(){
        return array('action'=>'AjaxGetFloors');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        //TODO check for a hallId

        $room = new HMS_Room($context->get('roomId'));

        $beds = $room->get_beds();

        $json_beds = array();
        $json_beds[0] = 'Select...';

        foreach ($beds as $bed){
            if($bed->room_change_reserved != 0){
                //Cannot assign to reserved rooms
                continue;
            }
            $json_beds[$bed->id] = $bed->bed_letter;
        }

        $context->setContent(json_encode($json_beds));
    }
}

?>