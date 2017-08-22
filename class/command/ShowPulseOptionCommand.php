<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class ShowPulseOptionCommand extends Command
{

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowPulseOption');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if (!Current_User::isDeity()) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit terms.');
        }

        PHPWS_Core::initModClass('hms', 'PulseEditView.php');

        $pulse = new PulseEditView();
        $context->setContent($pulse->show());
    }

}
