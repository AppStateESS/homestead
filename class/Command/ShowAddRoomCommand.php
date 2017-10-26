<?php

namespace Homestead\Command;

use \Homestead\HMS_Floor;
use \Homestead\AddRoomView;
use \Homestead\Exception\PermissionException;

//TODO finish this class, make a view

class ShowAddRoomCommand extends Command {

    private $floorId;

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowAddRoom');

        if(isset($this->floorId)){
            $vars['floor'] = $this->floorId;
        }

        return $vars;
    }

    public function setFloorId($id)
    {
        $this->floorId = $id;
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'room_structure')){
            throw new PermissionException('You do not have permission to add a room.');
        }

        $floor_id = $context->get('floor');

        $tpl = array();

        # Setup the title and color of the title bar
        $tpl['TITLE']       = 'Add Room';

        # Check to make sure we have a floor and hall.
        $floor = new HMS_Floor($floor_id);
        if(!$floor){
            $tpl['ERROR_MSG'] = 'There was an error getting the floor object. Please contact ESS.';
            return \PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
        }

        $hall = $floor->get_parent();
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return \PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
        }

        # Check Permissions
        if(!\Current_User::allow('hms','room_structure')) {
            HMS_Floor::show_edit_floor($floor_id,NULL,'You do not have permission to add rooms.');
        }

        $view = new AddRoomView($floor);
        $context->setContent($view->show());
    }
}
