<?php

namespace Homestead\Command;

use \Homestead\PinDisabledView;

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
        $view = new PinDisabledView();
        $context->setContent($view->show());
    }
}
