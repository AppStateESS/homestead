<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class ActivityLogMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
        // Check permissions
        if(UserStatus::isAdmin() && Current_User::allow('hms', 'view_activity_log')){		
            $this->addCommandByName('View activity logs', 'ShowActivityLog');
        }
	}
	
	public function show()
	{
        if(empty($this->commands)){
            return "";
        }
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/ActivityLogMenu.tpl');
	}
}