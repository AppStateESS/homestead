<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class RoommatesMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('Create roommate group', 'CreateRoommateGroupView');
		
		$this->addCommandByName('Edit roommate groups', 'EditRoommateGroupsView');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/RoommateMenu.tpl');
	}
}