<?php

namespace Homestead\Command;

use Homestead\AssetResolver;
use Homestead\CommandFactory;

class ShowAssignmentsHomeCommand extends Command {

    public function getRequestVars(){
        return array('action' => 'ShowAssignmentsHome');
    }

    public function execute(CommandContext $context)
    {
        $tpl = array();

        if(\Current_User::allow('hms', 'assignment_maintenance')){
            $assignCmd = CommandFactory::getCommand('ShowAssignStudent');
            $tpl['ASSIGN_STUDENT_URI'] = $assignCmd->getUri();

            //$this->addCommandByName('Unassign student', 'ShowUnassignStudent');
            //$this->addCommandByName('Set move-in times', 'ShowMoveinTimesView');
        }

        if(\Current_User::allow('hms', 'run_hall_overview')){
            $hallOverviewCmd = CommandFactory::getCommand('SelectResidenceHall');
            $hallOverviewCmd->setTitle('Hall Overview');
            $hallOverviewCmd->setOnSelectCmd(CommandFactory::getCommand('HallOverview'));

            $tpl['HALL_OVERVIEW_URI'] = $hallOverviewCmd->getUri();
        }

        if(\Current_User::allow('hms', 'admin_approve_room_change')){
            $adminRoomChangeCmd = CommandFactory::getCommand('ShowAdminRoomChangeList');
            $tpl['ROOM_CHANGE_URI'] = $adminRoomChangeCmd->getUri();
        }

        // TODO: Fix this. It doesn't run.
        if(\Current_User::allow('hms', 'assign_by_floor')){
            $floorAssignCmd = CommandFactory::getCommand('AssignByFloor');
            $tpl['ASSIGN_BY_FLOOR_URI'] = $floorAssignCmd->getUri();
        }

        // TODO - Fix this. It doesn't run.
        if(\Current_User::allow('hms', 'autoassign')) {
            $autoAssignCmd = CommandFactory::getCommand('StartAutoassign');
            $tpl['AUTO_ASSIGN_URI'] = $autoAssignCmd->getUri();
        }

        if(\Current_User::allow('hms', 'withdrawn_search')){
           $withdrawnSearchCmd = CommandFactory::getCommand('WithdrawnSearch');
           $tpl['WITHDRAWN_SEARCH_URI'] = $withdrawnSearchCmd->getUri();
       }

        // JS Bundles for Room Assignment List
        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'assignmentsTable');

        $context->setContent(\PHPWS_Template::process($tpl, 'hms', 'admin/AssignmentsHome.tpl'));
    }
}
