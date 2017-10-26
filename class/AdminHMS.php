<?php

namespace Homestead;

class AdminHMS extends HMS
{
    public function process()
    {
        $this->context->setDefault('action', 'ShowAdminMaintenanceMenu');
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
