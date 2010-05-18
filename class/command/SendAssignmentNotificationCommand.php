<?php

class SendAssignmentNotificationCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'SendAssignmentNotification');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'assignment_notify')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to send assignment notifications.');
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_Letter.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
        PHPWS_Core::initModClass('hms', 'Term.php');
        
        // Check that Move-In Times are set before sending email.
        // If not set warn user and go back to main menu.
        $term = Term::getSelectedTerm();
        $moveinTimes = HMS_Movein_Time::get_movein_times_array($term);
        if(sizeof($moveinTimes) < 2 || is_null($moveinTimes) || !isset($moveinTimes)){
            $termString = Term::toString($term);
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, "No move-in times are set for $termString.");
            $context->goBack();
        }
        
        try{
            HMS_Letter::email();
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was a problem sending the assignment notices. Please contact ESS.');
            $context->goBack();
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Assignment notifications sent.');
        $context->goBack();
    }
}

?>