<?php

/**
 * HMS User Controller
 *  Controls the interface for authenticated non-admin users.
 *
 *  @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

PHPWS_Core::initModClass('hms', 'HMS.php');

class UserHMS extends HMS
{
    public function process()
    {
        $this->context->setDefault('action', 'ShowStudentMenu');
        parent::process();

        PHPWS_Core::initModClass('hms', 'UserView.php');
        $view = new UserView();
        $view->setMain($this->context->getContent());

        PHPWS_Core::initModClass('hms', 'HMSNotificationView.php');
        $nv = new HMSNotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());

        $view->show();

        $this->saveState();
    }
}

?>