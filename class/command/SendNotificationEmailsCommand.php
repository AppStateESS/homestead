<?php
/**
 * SendNotificationEmailsCommand
 *
 *  Sends emails from RAs/RDs by halls/floor.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package HMS
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

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        PHPWS_Core::initModClass('hms', 'HMS_Permission.php');

        // Sanity checks
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


        // Log that this is happening
        if($anonymous){
            HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_ANON_NOTIFICATION_SENT, Current_User::getUsername());
        }else{
            HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_NOTIFICATION_SENT, Current_User::getUsername());
        }

        //load the floors
        foreach($floors as $key=>$floor_id){
            $floors[$key] = new HMS_Floor($floor_id);
        }

        // TODO accurate logging
        //HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED_ANONYMOUSLY, Current_User::getUsername(), $hall->hall_name);
        //HMS_Activity_Log::log_activity(Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED, Current_User::getUsername(), $hall->hall_name);

        //load the halls and add floors that aren't already present, if they have js enabled should be zero
        foreach($halls as $hall){
            $hallObj = new HMS_Residence_Hall($hall);

            $hallFloors = $hallObj->get_floors();

            //if the hall has zero floors, skip it
            if(!is_array($hallFloors))
                continue;

            foreach($hallFloors as $hallFloor){
                if(!empty($floors)){
                    foreach($floors as $floor){
                        if($hallFloor->id == $floor->id){
                            break;
                        }
                    }
                }
                if(!in_array($hallFloor, $floors)){
                    $floorObj[] = $hallFloor;
                }
            }
        }

        if(!is_array($floorObj)){
            $floorObj = array();
        }

        if(!is_array($floors)){
            $floors = array();
        }

        $floorObj = array_merge($floorObj, $floors);

        $permission = new HMS_Permission();
        foreach($floorObj as $floor){
            if(!$permission->verify(Current_User::getUsername(), $floor, 'email')
               && !$permission->verify(Current_User::getUsername(), $floor->get_parent(), 'email')
               && !Current_User::allow('hms', 'email_all')
               ){
                continue;
            }

            /**
            $rooms = $floor->get_rooms();
            foreach($rooms as $room){
                $students = $room->get_assignees();
                foreach($students as $student){
                    $people[] = $student->getUsername();
                    HMS_Email::send_email($student->getUsername() . '@appstate.edu', $from, $subject, $body);
                }
            }
            */

            $students = $floor->getUsernames();
            foreach($students as $student){
                HMS_Email::send_email($student . '@appstate.edu', $from, $subject, $body);
            }

            HMS_Activity_Log::log_activity(Current_User::getUsername(), ($anonymous ? ACTIVITY_FLOOR_NOTIFIED_ANONYMOUSLY : ACTIVITY_FLOOR_NOTIFIED), Current_User::getUsername(), $floor->where_am_i());
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Emails sent successfully!');
        $cmd = CommandFactory::getCommand('ShowAdminMaintenanceMenu');
        $cmd->redirect();
    }
}
?>
