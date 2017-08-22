<?php

namespace Homestead\command;

use \Homestead\Command;

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
        PHPWS_Core::initModClass('hms', 'AssignByFloorView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $floorView = new AssignByFloorView();
        $context->setContent($floorView->show());
    }
}
