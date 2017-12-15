<?php

namespace Homestead\Command;

use \Homestead\RoomChangeManageView;
use \Homestead\RoomChangeRequestFactory;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;

class ShowManageRoomChangeCommand extends Command {

    private $requestId;

    public function setRequestId($id)
    {
        $this->requestId = $id;
    }

    public function getRequestVars()
    {
        return array('action'       => 'ShowManageRoomChange',
                     'requestId'    => $this->requestId);
    }

    public function execute(CommandContext $context)
    {
        $requestId = $context->get('requestId');

        if (!isset($requestId) || is_null($context)) {
            throw new \InvalidArgumentException('Missing request id');
        }

        $request = RoomChangeRequestFactory::getRequestById($requestId);

        if (is_null($request) || $request === false) {
           \NQ::simple('hms', NotificationView::ERROR, 'Invalid room change request id.');
           $cmd = CommandFactory::getCommand('DashboardHome');
           $cmd->redirect();
        }

        $view = new RoomChangeManageView($request);
        $context->setContent($view->show());
    }
}
