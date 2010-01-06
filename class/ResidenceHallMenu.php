<?php

// TODO: consider renaming this to HallMaintenanceMenu

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class ResidenceHallMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
		$residenceHallCmd = CommandFactory::getCommand('SelectResidenceHall');
		$residenceHallCmd->setTitle('Edit a Residence Hall');
		$residenceHallCmd->setOnSelectCmd(CommandFactory::getCommand('EditResidenceHallView'));
		$this->addCommand('Edit a residence hall', $residenceHallCmd);
		
		$floorCmd = CommandFactory::getCommand('SelectFloor');
		$floorCmd->setTitle('Edit a Floor');
		$floorCmd->setOnSelectCmd(CommandFactory::getCommand('EditFloorView'));
		$this->addCommand('Edit a floor', $floorCmd);
		
		$roomCmd = CommandFactory::getCommand('SelectRoom');
		$roomCmd->setTitle('Edit a Room');
		$roomCmd->setOnSelectCmd(CommandFactory::getCommand('EditRoomView'));
		$this->addCommand('Edit a room', $roomCmd);
		
		$bedCmd = CommandFactory::getCommand('SelectBed');
		$bedCmd->setTitle('Edit a Bed');
		$bedCmd->setOnSelectCmd(CommandFactory::getCommand('EditBedView'));
		$this->addCommand('Edit a bed', $bedCmd);
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/ResidenceHallMenu.tpl');
	}
}

?>