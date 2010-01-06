<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class RLCMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('Add Learning Community', 'ShowAddRlc');
        $this->addCommandByName('Edit Learning Community', 'ShowEditRlc');
        $this->addCommandByName('Assign Applicants to RLCs', 'AssignRlcApplicants');
        $this->addCommandByName('View Denied Applications', 'ShowDeniedRlcApplicants');
        $this->addCommandByName('View RLC Members by RLC', 'ShowSearchByRlc');
        $this->addCommandByName('View RLC Assignments', 'ViewRlcAssignments');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/RLCMenu.tpl');
	}
}
?>
