<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class ActivityLogMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('View activity logs', 'ShowActivityLog');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/ActivityLogMenu.tpl');
	}
}