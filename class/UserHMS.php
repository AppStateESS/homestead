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
        $view = new hms\UserView();
        $view->setMain($this->context->getContent());

        $nv = new hms\NotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());

        $view->show();

        $this->saveState();
    }
}

