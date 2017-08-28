<?php

namespace Homestead;

/**
 * HMS User Controller
 *  Controls the interface for authenticated non-admin users.
 *
 *  @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class UserHMS extends HMS
{
    public function process()
    {
        $this->context->setDefault('action', 'ShowStudentMenu');
        parent::process();

        $view = new UserView();
        $view->setMain($this->context->getContent());

        $nv = new NotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());

        $view->show();

        $this->saveState();
    }
}
