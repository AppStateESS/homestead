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