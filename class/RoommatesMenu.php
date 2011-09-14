<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class RoommatesMenu extends CommandMenu {

    public function __construct()
    {
        parent::__construct();
        // check permissions
        if(UserStatus::isAdmin() && Current_User::allow('hms', 'roommate_maintenance')){
            $this->addCommandByName('Create roommate group', 'CreateRoommateGroupView');
            $this->addCommandByName('Edit roommate groups', 'EditRoommateGroupsView');
        }
    }

    public function show()
    {
        if(empty($this->commands)){
            return "";
        }

        $tpl = array();

        $tpl['MENU'] = parent::show();

        return PHPWS_Template::process($tpl, 'hms', 'admin/menus/RoommateMenu.tpl');
    }
}