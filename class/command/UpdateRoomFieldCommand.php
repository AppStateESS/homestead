<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'HMS_Room.php');

  /*
   * UpdateRoomFieldCommand
   *
   *   Updates a field in a particular room.
   *
   * @author Daniel West <dwest at tux dot appstate dot edu>
   * @package hms
   */

class UpdateRoomFieldCommand extends Command {

    public function getRequestVars(){
        return array('action' => 'UpdateRoomFieldCommand');
    }

    public function execute(CommandContext $context){
		if(!Current_User::allow('hms', 'room_attributes')){
			echo json_encode(false);
            die();
		}

        $id = $context->get('id');
        $element = $context->get('field');
        $value = $context->get('value');

        if(is_null($id) || is_null($element) || is_null($value) ){
            echo json_encode(false);
            die();
        }

        $room = HMS_Room::update_row($id, $element, $value);
        echo json_encode(array('value' => $room->value));
        die();
    }
}
?>