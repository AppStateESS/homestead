<?php

//TODO finish this class, make a view

class AddRoomViewCommand extends Command {
    public $floor;
    public $residence_hall;

    public function getRequestVars()
    {
        $vars = array('action'=>'AddRoomView');

        if(isset($this->floor)){
            $vars['floor'] = $this->floor;
        }

        if(isset($this->residence_hall)){
            $vars['residence_hall'] = $this->residence_hall;
        }

        return $vars;
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
        PHPWS_Core::initModClass('hms', 'HMS_Pricing_Tier.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'AddRoomView.php');

        $floor_id = $context->get('floor');
        $hall_id  = $context->get('residence_hall');

        # Setup the title and color of the title bar
        $tpl['TITLE']       = 'Add Room';
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();

        # Check to make sure we have a floor and hall.
        $floor = new HMS_Floor($floor_id);
        if(!$floor){
            $tpl['ERROR_MSG'] = 'There was an error getting the floor object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
        }

        $hall = new HMS_Residence_Hall($hall_id);
        if(!$hall){
            $tpl['ERROR_MSG'] = 'There was an error getting the hall object. Please contact ESS.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/add_room.tpl');
        }

        # Check Permissions
        if(!Current_User::allow('hms','room_structure')) {
            HMS_Floor::show_edit_floor($floor_id,NULL,'You do not have permission to add rooms.');
        }

        $view = new AddRoomView;
        $view->hall = $hall;
        $view->floor = $floor;
        $context->setContent($view->show());
    }
}