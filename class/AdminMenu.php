<?php

/**
 * HMS Admin Menu Controller
 * Displays a side menu based on permissions.
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class AdminMenu extends CommandMenu
{
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('Main menu', 'ShowAdminMaintenanceMenu');
		$this->addCommandByName('Search students', 'ShowStudentSearch');
		$this->addCommandByName('Reports', 'ListReports');
        $this->addCommandByName('Stats','ShowStats');

        if(Current_User::isDeity()){
    		$this->addCommandByName('Control Panel', 'ShowControlPanel');
        }
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
                    
        return PHPWS_Template::process($tpl, 'hms', 'UserMenu.tpl');
	}
}
