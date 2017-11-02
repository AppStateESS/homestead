<?php

namespace Homestead;

class AdminHMS extends HMS
{
    public function process()
    {
        $this->context->setDefault('action', 'ShowAdminMaintenanceMenu');
        parent::process();

        $view = new AdminView();
        $view->setMain($this->context->getContent());

        $nv = new NotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());

        $view->render();

        $this->saveState();
    }
}
