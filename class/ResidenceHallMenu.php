<?php

// TODO: consider renaming this to HallMaintenanceMenu

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class ResidenceHallMenu extends CommandMenu {

    public function __construct()
    {
        parent::__construct();
        // Check permissions
        if(UserStatus::isAdmin()){
            if(Current_User::allow('hms', 'hall_view')){
                $residenceHallCmd = CommandFactory::getCommand('SelectResidenceHall');
                $residenceHallCmd->setTitle('Edit a Residence Hall');
                $residenceHallCmd->setOnSelectCmd(CommandFactory::getCommand('EditResidenceHallView'));
                $this->addCommand('Edit a residence hall', $residenceHallCmd);
            }
        }
    }

    public function show()
    {
        if(empty($this->commands))
        return "";

        $tpl = array();

        $tpl['MENU'] = parent::show();

        return PHPWS_Template::process($tpl, 'hms', 'admin/menus/ResidenceHallMenu.tpl');
    }
}
