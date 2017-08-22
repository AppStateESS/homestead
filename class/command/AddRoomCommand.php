<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * AddRoom
 *
 *   Adds a room to the specified floor with the given properties.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package hms
 */

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
        $room->gender_type = $context->get('gender_type');
        $room->default_gender = $context->get('default_gender');

        $room->ra       = !is_null($context->get('ra')) ? 1 : 0;
        $room->private  = !is_null($context->get('private')) ? 1 : 0;
        $room->overflow = !is_null($context->get('overflow')) ? 1 : 0;
        $room->reserved = !is_null($context->get('reserved')) ? 1 : 0;
        $room->offline  = !is_null($context->get('offline')) ? 1 : 0;
        $room->parlor   = !is_null($context->get('parlor')) ? 1 : 0;

        $room->ada  = !is_null($context->get('ada')) ? 1 : 0;
        $room->hearing_impaired  = !is_null($context->get('hearing_impaired')) ? 1 : 0;
        $room->bath_en_suite  = !is_null($context->get('bath_en_suite')) ? 1 : 0;

        $rlcId = $context->get('rlc_reserved');
        $room->reserved_rlc_id = !is_null($rlcId) && $rlcId != '0' ? $rlcId : null;

        $room->term = Term::getSelectedTerm();

        // Get the building code
        $floor = new HMS_Floor($floor_id);
        $hall = $floor->get_parent();

        // and set the rooms building code to the same as the hall it is in
        $room->banner_building_code = $hall->banner_building_code;

        // creates a persistent_id for the new room
        $room->persistent_id = uniqid();

        $room->save();

        $cmd = CommandFactory::getCommand('EditRoomView');
        $cmd->setRoomId($room->getId());
        $cmd->redirect();
    }
}
