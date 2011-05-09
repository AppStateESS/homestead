<?php

/*
 * AddRoom
 *
 *   Adds a room to the specified floor with the given properties.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 */

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'HMS_Room.php');
PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

class AddRoomCommand extends Command {
    public $floor;

    public function getRequestVars()
    {
        $vars = array('action' => 'AddRoom');

        if(isset($this->floor)){
            $vars['floor'] = $this->floor;
        }
        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'room_structure')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to add a room.');
        }

        $floor_id = $context->get('floor');

        $room = new HMS_Room;
        $room->floor_id = $floor_id;
        $room->room_number = $context->get('room_number');
        $room->default_gender = $context->get('default_gender');
        $room->ra_room = !is_null($context->get('ra_room')) ? $context->get('ra_room') : 0;
        $room->private_room = !is_null($context->get('private_room')) ? $context->get('private_room') : 0;
        $room->is_overflow = !is_null($context->get('is_overflow')) ? $context->get('is_overflow') : 0;
        $room->is_medical = !is_null($context->get('is_medical')) ? $context->get('is_medical') : 0;
        $room->is_reserved = !is_null($context->get('is_reserved')) ? $context->get('is_reserved') : 0;
        $room->is_online = !is_null($context->get('is_online')) ? $context->get('is_online') : 0;
        $room->term = Term::getSelectedTerm();

        //get the building code
        $floor = new HMS_Floor($floor_id);
        $hall = $floor->get_parent();

        //and set the rooms building code to the same as the hall it is in
        $room->banner_building_code = $hall->banner_building_code;

        $room->save();

        $cmd = CommandFactory::getCommand('EditFloorView');
        $cmd->setFloorId($floor_id);
        $cmd->redirect();
    }
}