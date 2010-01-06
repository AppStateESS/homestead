<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class TermMaintenanceMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('Edit Term Settings', 'ShowEditTerm');
		$this->addCommandByName('Create New Term', 'ShowCreateTerm');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/TermMaintenanceMenu.tpl');
	}
}