<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class AssignmentMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
        if(UserStatus::isAdmin()){
            if(Current_User::allow('hms', 'assignment_maintenance')){
                $this->addCommandByName('Assign student', 'ShowAssignStudent');
                $this->addCommandByName('Unassign student', 'ShowUnassignStudent');
                $this->addCommandByName('Set move-in times', 'ShowMoveinTimesView');
            }
            if(Current_User::allow('hms', 'run_hall_overview')){
                $hallOverviewCmd = CommandFactory::getCommand('SelectResidenceHall');
                $hallOverviewCmd->setTitle('Hall Overview');
                $hallOverviewCmd->setOnSelectCmd(CommandFactory::getCommand('HallOverview'));
                $this->addCommand('Hall Overview', $hallOverviewCmd);
            }
        }
	}
	
	public function show()
	{
        if(empty($this->commands))
            return "";

		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/AssignmentMenu.tpl');
	}
}

?>