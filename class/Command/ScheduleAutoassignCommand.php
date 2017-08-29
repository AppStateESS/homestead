<?php

namespace Homestead\Command;

 

class ScheduleAutoassignCommand extends Command
{

    public function getRequestVars()
    {
        return array('action' => 'ScheduleAutoassign');
    }

    public function execute(CommandContext $context)
    {
        $pulse = \pulse\PulseFactory::getByName('AutoAssign', 'hms');
        if (!empty($pulse)) {
            $began = strftime('on %d %h %Y at %I:%M %P', $pulse->getStartTime());
            $message = "<h2>The autoassigner is already running.</h2><p>It started at $began and was scheduled by $user.</p>";
        } else {
            PHPWS_Core::initModClass('hms', 'command/SavePulseOptionCommand.php');
            SavePulseOptionCommand::addAutoAssignSchedule();
            $message = '<h2>The autoassigner is now running.</h2><p>You will receive an email when it is finished.</p>';
        }

        $context->setContent($message . '<a href="index.php?module=hms&action=ShowAdminMaintenanceMenu">Return to Main Menu</a>');
    }

}
