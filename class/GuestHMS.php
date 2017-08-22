<?php

namespace Homestead;

/**
 * HMS Guest Controller
 * Controls information that Guests have access to (the login screen, for now).
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class GuestHMS extends HMS
{
    public function process()
    {
        $this->context->setDefault('action', 'ShowFrontPage');
        parent::process();

        PHPWS_Core::initModClass('hms', 'GuestView.php');
        $view = new hms\GuestView();
        $view->setMain($this->context->getContent());

        $nv = new hms\NotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());

        $view->show();

        $this->saveState();
    }
}
