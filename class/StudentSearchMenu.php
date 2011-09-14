<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class StudentSearchMenu extends CommandMenu {

    public function __construct()
    {
        parent::__construct();
        if(UserStatus::isAdmin() && Current_User::allow('hms', 'search')){
            $this->addCommandByName('Search students', 'ShowStudentSearch');
        }
    }

    public function show()
    {
        // Those without permission should not see the option
        if(empty($this->commands)){
            return "";
        }

        $tpl = array();
        $tpl['MENU'] = parent::show();
        return PHPWS_Template::process($tpl, 'hms', 'admin/menus/StudentSearchMenu.tpl');
    }
}
?>