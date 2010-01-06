<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetRoomsCommand extends Command {
	
	private $floorId;
	
	public function getRequestVars(){
		return array('action'=>'AjaxGetFloors');
	}
	
	public function execute(CommandContext $context)
	{
		PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
		PHPWS_Core::initModClass('hms', 'HMS_Util.php');

		//TODO check for a hallId
		
        $floor = new HMS_Floor($context->get('floorId'));

        $rooms = $floor->get_rooms();

        $json_rooms = array();
        $json_rooms[0] = 'Select...';
        
        foreach ($rooms as $room){
            $json_rooms[$room->id] = $room->room_number;
        }
        
        $context->setContent(json_encode($json_rooms));
	}
}

?>