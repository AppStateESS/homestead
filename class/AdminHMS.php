<?php

class AdminHMS extends HMS
{
    public function process()
    {
        $this->context->setDefault('action', 'ShowAdminMaintenanceMenu');
        parent::process();

        $view = new hms\UserView();
        $view->setMain($this->context->getContent());

/*
        // Check permissions. Must be able to search for students in order to see the recent menu
        if(Current_User::allow('hms','search')){
            PHPWS_Core::initModClass('hms', 'RecentStudentSearchList.php');
            PHPWS_Core::initModClass('hms', 'RecentStudentSearchMenu.php');
            $recent = new RecentStudentSearchMenu(RecentStudentSearchList::getInstance());
            $view->addToSidebar($recent->show());
        }
*/

        PHPWS_Core::initModClass('hms', 'HMSNotificationView.php');
        $nv = new HMSNotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());

        $view->show();

        $this->saveState();
    }
}

?>
