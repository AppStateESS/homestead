<?php

namespace Homestead\command;

use \Homestead\Command;

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
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeManageView.php');

        $requestId = $context->get('requestId');

        if (!isset($requestId) || is_null($context)) {
            throw new \InvalidArgumentException('Missing request id');
        }

        $request = RoomChangeRequestFactory::getRequestById($requestId);

        if (is_null($request) || $request === false) {
           \NQ::simple('hms', NotificationView::ERROR, 'Invalid room change request id.');
           $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
           $cmd->redirect();
        }

        $view = new RoomChangeManageView($request);
        $context->setContent($view->show());
    }
}
