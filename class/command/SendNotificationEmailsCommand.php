<?
/**
 * SendNotificationEmailsCommand
 *
 *  Sends the hall notification emails.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package mod
 * @subpackage hms
 */
//PHPWS_Core::initModClass('hms', 'SendNotificationEmailsView.php');

class SendNotificationEmailsCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'  => 'SendNotificationEmails');

        foreach(array('anonymous', 'subject', 'body', 'hall', 'floor') as $key){
            if( !is_null($this->context) && !is_null($this->context->get($key)) ){
                $vars[$key] = $this->context->get($key);
            }
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        /*
        if(!Current_User::allow('hms', 'email_hall') && !Current_User::allow('hms', 'email_all')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to send messages.');
        }
        */

        if(is_null($context->get('hall')) && is_null($context->get('floor')) ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must select a hall or floor to continue!');
            $cmd = CommandFactory::getCommand('ShowHallNotificationSelect');
            $cmd->redirect();
        }

        $subject   = $context->get('subject');
        $body      = $context->get('body');
        $anonymous = (!is_null($context->get('anonymous')) && $context->get('anonymous')) ? true : false;
        $from      = ($anonymous && Current_User::allow('hms', 'anonymous_notifications')) ? FROM_ADDRESS : Current_User::getEmail();
        $halls     = $context->get('hall');
        $floors    = $context->get('floor');

        if(empty($subject)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must fill in the subject line of the email.');
            $cmd = CommandFactory::getCommand('ShowHallNotificationEdit');
            $cmd->loadContext($context);
            $cmd->redirect();
        } else if(empty($body)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must fill in the message to be sent.');
            $cmd = CommandFactory::getCommand('ShowHallNotificationEdit');
            $cmd->loadContext($context);
            $cmd->redirect();
        }

        //Consider using a batch process instead of doing this this inline
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

        // Log that this is happening
        if($anonymous){
            HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_ANON_NOTIFICATION_SENT, Current_User::getUsername());
        }else{
            HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_NOTIFICATION_SENT, Current_User::getUsername());
        }

        $permission = new HMS_Permission();
        //load the floors
        foreach($floors as $key=>$floor_id){
            $floors[$key] = new HMS_Floor($floor_id);
            if(!$permission->verify(Current_User::getUsername(), $floors[$key], 'email')){
                unset($floors[$key]);
            }
        }

        foreach($halls as $hall_id){
            $hall = new HMS_Residence_Hall($hall_id);
            if($permission->verify(Current_User::getUsername(), $hall, 'email')){
                foreach($hall->get_floors() as $floor){
                    $floors[] = new HMS_Floor($floor->id);
                }
                if($anonymous){
                    HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED_ANONYMOUSLY, Current_User::getUsername(), $hall->hall_name);
                } else {
                    HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED, Current_User::getUsername(), $hall->hall_name);
                }
            }
        }

        foreach($floors as $floor){
            $rooms = $floor->get_rooms();
            foreach($rooms as $room){
                $students = $room->get_assignees();
                foreach($students as $student){
                    HMS_Email::send_email($student->getUsername() . '@appstate.edu', $from, $subject, $body);
                }
            }
            //TODO: add activity for anonymous floor notification
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Emails sent successfully!');
        $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
        $cmd->redirect();
    }
}
?>
