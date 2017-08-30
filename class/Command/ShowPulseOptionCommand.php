<?php

namespace Homestead\Command;

use \Homestead\PulseEditView;
use \Homestead\Exception\PermissionException;

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
        if (!\Current_User::isDeity()) {
            throw new PermissionException('You do not have permission to edit terms.');
        }

        $pulse = new PulseEditView();
        $context->setContent($pulse->show());
    }

}
