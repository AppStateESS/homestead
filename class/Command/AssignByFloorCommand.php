<?php

namespace Homestead\Command;

use \Homestead\AssignByFloorView;

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class AssignByFloorCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'AssignByFloor');
    }

    public function execute(CommandContext $context)
    {
        $floorView = new AssignByFloorView();
        $context->setContent($floorView->show());
    }
}
