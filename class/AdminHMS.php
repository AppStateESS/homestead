<?php

class AdminHMS extends HMS
{
    public function process()
    {
        $this->context->setDefault('action', 'ShowAdminMaintenanceMenu');
        parent::process();

        $view = new hms\UserView();
        $view->setMain($this->context->getContent());

        $nv = new hms\NotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());

        $view->show();

        $this->saveState();
    }
}
