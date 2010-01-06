<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class ReapplicationMaintenanceMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('Settings', 'ShowLotterySettings');
		$this->addCommandByName('Create entry', 'ShowLotteryAdminEntry');
		$this->addCommandByName('Set automatic winners', 'ShowLotteryAutoWinners');
		$this->addCommandByName('Eligibility waivers', 'ShowLotteryEligibilityWaiver');
		$this->addCommandByName('Interest group approval', 'ShowSpecialInterestGroupApproval');
		$this->addCommandByName('Waiting list', 'ShowLotteryWaitingList');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/ReapplicationMaintenanceMenu.tpl');
	}
}