<?php

PHPWS_Core::initModClass('hms', 'View.php');

PHPWS_Core::initModClass('hms', 'StudentSearchMenu.php');
PHPWS_Core::initModClass('hms', 'AssignmentMenu.php');
PHPWS_Core::initModClass('hms', 'ResidenceHallMenu.php');
PHPWS_Core::initModClass('hms', 'TermMaintenanceMenu.php');
PHPWS_Core::initModClass('hms', 'ActivityLogMenu.php');
PHPWS_Core::initModClass('hms', 'RoommatesMenu.php');
PHPWS_Core::initModClass('hms', 'RLCMenu.php');
PHPWS_Core::initModClass('hms', 'ReapplicationMaintenanceMenu.php');
PHPWS_Core::initModClass('hms', 'MessagingMenu.php');
PHPWS_Core::initModClass('hms', 'ServiceDeskMenu.php');


class AdminMaintenanceMenuView extends hms\View{

    public function show()
    {
        Layout::addStyle('hms', 'css/menu-grid.css');
        $tpl = array();

        $searchMenu = new StudentSearchMenu();
        $tpl['STUDENT_SEARCH'] = $searchMenu->show();

        $hallMenu = new ResidenceHallMenu();
        $tpl['RESIDENCE_HALL'] = $hallMenu->show();

        $assignMenu = new AssignmentMenu();
        $tpl['ASSIGNMENT'] = $assignMenu->show();

        $termMenu = new TermMaintenanceMenu();
        $tpl['TERM'] = $termMenu->show();

        $activityLogs = new ActivityLogMenu();
        $tpl['LOGS'] = $activityLogs->show();

        $roommates = new RoommatesMenu();
        $tpl['ROOMMATES'] = $roommates->show();

        $rlcs = new RLCMenu();
        $tpl['RLCS'] = $rlcs->show();

        $reapp = new ReapplicationMaintenanceMenu();
        $tpl['REAPP'] = $reapp->show();

        $messaging = new MessagingMenu();
        $tpl['MESSAGING'] = $messaging->show();
        
        $serviceDesk = new ServiceDeskMenu();
        $tpl['SERVICE_DESK'] = $serviceDesk->show();

        Layout::addPageTitle("Main Menu");

        return PHPWS_Template::process($tpl, 'hms', 'AdminMaintenanceMenu.tpl');
    }
}

?>
