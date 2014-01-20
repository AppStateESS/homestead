<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');
PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

class AssignmentMenu extends CommandMenu {

    public function __construct()
    {
        parent::__construct();
        if(UserStatus::isAdmin()){
            if(Current_User::allow('hms', 'assignment_maintenance')){
                $this->addCommandByName('Assign student', 'ShowAssignStudent');
                $this->addCommandByName('Unassign student', 'ShowUnassignStudent');
                $this->addCommandByName('Set move-in times', 'ShowMoveinTimesView');
            }

            if(Current_User::allow('hms', 'run_hall_overview')){
                $hallOverviewCmd = CommandFactory::getCommand('SelectResidenceHall');
                $hallOverviewCmd->setTitle('Hall Overview');
                $hallOverviewCmd->setOnSelectCmd(CommandFactory::getCommand('HallOverview'));
                $this->addCommand('Hall Overview', $hallOverviewCmd);
            }

            if(Current_User::allow('hms', 'assign_by_floor')){
                $floorAssignCmd = CommandFactory::getCommand('SelectFloor');
                $floorAssignCmd->setOnSelectCmd(CommandFactory::getCommand('ShowFloorAssignmentView'));
                $floorAssignCmd->setTitle('Assign Students to Floor');
                $this->addCommand('Assign students by floor', $floorAssignCmd);
            }

            if(Current_User::allow('hms', 'autoassign')) {
                $autoAssignCmd = CommandFactory::getCommand('JSConfirm');
                $autoAssignCmd->setLink('Auto-assign');
                $autoAssignCmd->setTitle('Auto-assign');
                $autoAssignCmd->setQuestion('Start auto-assign process for the selected term?');

                $autoAssignCmd->setOnConfirmCommand(CommandFactory::getCommand('ScheduleAutoassign'));
                $this->addCommand('Start Autoassigner', $autoAssignCmd);
            }

            if(Current_User::allow('hms', 'withdrawn_search')){
                $withdrawnSearchCmd = CommandFactory::getCommand('JSConfirm');
                $withdrawnSearchCmd->setLink('Withdrawn search');
                $withdrawnSearchCmd->setTitle('Withdrawn search');
                $withdrawnSearchCmd->setQuestion('Start search for withdrawn students for the selected term?');

                $withdrawnSearchCmd->setOnConfirmCommand(CommandFactory::getCommand('WithdrawnSearch'));
                $this->addCommand('Withdrawn search', $withdrawnSearchCmd);
            }

            $memberships = HMS_Permission::getMembership('room_change_approve', NULL, UserStatus::getUsername());
            if(!empty($memberships)){
                $RDRoomChangeCmd = CommandFactory::getCommand('ShowRDRoomChangeList');
                $this->addCommand('Room Change Approval (RD)', $RDRoomChangeCmd);
            }

            if(Current_User::allow('hms', 'admin_approve_room_change')){
                $adminRoomChangeCmd = CommandFactory::getCommand('ShowAdminRoomChangeList');
                $this->addCommand('Room Change Approval (Admin)', $adminRoomChangeCmd);
            }
        }
    }

    public function show()
    {
        if(empty($this->commands))
        return "";

        $tpl = array();

        $tpl['MENU'] = parent::show();

        return PHPWS_Template::process($tpl, 'hms', 'admin/menus/AssignmentMenu.tpl');
    }
}

?>
