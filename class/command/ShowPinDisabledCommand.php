<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * ShowPinDisabledCommand
 *
 * Controller to show the view that informs the student his/her PIN is disabled.
 *
 * @author Jeremy Booker
 * @package HMS
 */

class ShowPinDisabledCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowPinDisabled');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'PinDisabledView.php');

        $view = new PinDisabledView();
        $context->setContent($view->show());
    }
}
