<?php

namespace Homestead\command;

use \Homestead\Command;

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
        if(!Current_User::allow('hms', 'room_structure')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to add a room.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'AddRoomView.php');

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
        if(!Current_User::allow('hms','room_structure')) {
            HMS_Floor::show_edit_floor($floor_id,NULL,'You do not have permission to add rooms.');
        }

        $view = new AddRoomView($floor);
        $context->setContent($view->show());
    }
}
