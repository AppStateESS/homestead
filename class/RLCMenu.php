<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class RLCMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();

        // Check permissions
        if(UserStatus::isAdmin()){

            if(Current_User::allow('hms', 'learning_community_maintenance')){
                $this->addCommandByName('Add Learning Community', 'ShowAddRlc');
                $this->addCommandByName('Edit Learning Community', 'ShowEditRlc');
            }

            if(Current_User::allow('hms', 'view_rlc_applications')){
                $this->addCommandByName('Assign Applicants to RLCs', 'ShowAssignRlcApplicants');
                $this->addCommandByName('View Denied Applications', 'ShowDeniedRlcApplicants');
            }

            if(Current_User::allow('hms' ,'view_rlc_members')){
                $this->addCommandByName('View RLC Members by RLC', 'ShowSearchByRlc');
                $this->addCommandByName('View RLC Assignments', 'ViewRlcAssignments');
            }
        }
	}
	
	public function show()
	{
        if(empty($this->commands)){
            return "";
        }

        $tpl = array();
		
        $tpl['MENU'] = parent::show();

        return PHPWS_Template::process($tpl, 'hms', 'admin/menus/RLCMenu.tpl');
	}
}
?>
