<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class TermMaintenanceMenu extends CommandMenu {

    public function __construct()
    {
        parent::__construct();
        // Check permissions
        if(UserStatus::isAdmin() && Current_User::allow('hms', 'edit_terms')){
            $this->addCommandByName('Edit Term Settings', 'ShowEditTerm');
            $this->addCommandByName('Create New Term', 'ShowCreateTerm');
        }
    }

    public function show()
    {
        if(empty($this->commands)){
            return "";
        }

        $tpl = array();
        $tpl['MENU'] = parent::show();
        return PHPWS_Template::process($tpl, 'hms', 'admin/menus/TermMaintenanceMenu.tpl');
    }
}