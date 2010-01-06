<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class StudentSearchMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('Search students', 'ShowStudentSearch');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/StudentSearchMenu.tpl');
	}
}
?>