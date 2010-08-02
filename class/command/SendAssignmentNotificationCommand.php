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

        // Check if any move-in times are set for the selected term
        $moveinTimes = HMS_Movein_Time::get_movein_times_array(Term::getSelectedTerm());
        
        // If the array of move-in times ONLY has the zero-th element ['None'] then it's no good
        // Or, of course, if the array is null or emtpy it is no good
        if(count($moveinTimes) <= 1 || is_null($moveinTimes) || empty($moveinTimes)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There are no move-in times set for '.Term::getPrintableSelectedTerm());
            $context->goBack();
        }

        $missing = null;

        try{
            $missing = HMS_Letter::email();
        }catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was a problem sending the assignment notices. Please contact ESS.');
            $context->goBack();
        }

        if(empty($missing) || is_null($missing)){
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, "Assignment notifications sent.");
        }
        else {
            foreach($missing as $floor){
                $hall = $floor->get_parent();
                $text = $floor->getLink($hall->getHallName()." floor ") . " move-in times not set.";
                NQ::simple('hms', HMS_NOTIFICATION_WARNING, $text);
            }
        }
        $context->goBack();
    }
}

?>