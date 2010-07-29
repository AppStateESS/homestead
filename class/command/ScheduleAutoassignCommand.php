<?php

class ScheduleAutoassignCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ScheduleAutoassign');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'AutoassignPulse.php');
        $pulse = new AutoassignPulse();
        $pulse->loadSingleton();

        if(isset($pulse->id)) {
            $began = strftime('on %d %h %Y at %I:%M %P', $pulse->began_execution);
            $user = $pulse->username;
            $message = "<h2>The autoassigner is already running.</h2><p>It started at $began and was scheduled by $user.</p>";
        } else {
            $pulse->name = 'HMS AutoAssigner';
            $pulse->username = UserStatus::getUsername();
            $pulse->scheduled_at = time();
            $pulse->execute_at = 0;
            $pulse->save();

            $message = '<h2>The autoassigner is now running.</h2><p>You will receive an email when it is finished.</p>';
        }

        $context->setContent($message . '<a href="index.php?module=hms&action=ShowAdminMaintenanceMenu">Return to Main Menu</a>');
    }
}

?>
