<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class AssignmentMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('Assign student', 'ShowAssignStudent');
		
		$this->addCommandByName('Unassign student', 'ShowUnassignStudent');
		
		$hallOverviewCmd = CommandFactory::getCommand('SelectResidenceHall');
		$hallOverviewCmd->setTitle('Hall Overview');
		$hallOverviewCmd->setOnSelectCmd(CommandFactory::getCommand('HallOverview'));
		$this->addCommand('Hall Overview', $hallOverviewCmd);
		
		$this->addCommandByName('Set move-in times', 'ShowMoveinTimesView');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/AssignmentMenu.tpl');
	}
}

?>