<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\HMS_Activity_Log;
use \Homestead\Floor;
use \Homestead\ResidenceHall;
use \Homestead\HMS_Permission;
use \Homestead\HMS_Email;
use \Homestead\NotificationView;

/**
 * SendNotificationEmailsCommand
 *
 *  Sends emails from RAs/RDs by halls/floor.
 *
 * @author Daniel West <lw77517 at appstate dot edu>
 * @package HMS
 */

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
        if(!\Current_User::allow('hms', 'email_hall') && !\Current_User::allow('hms', 'email_all')){
            throw new PermissionException('You do not have permission to send messages.');
        }
        */

        // Sanity checks
        if(is_null($context->get('hall')) && is_null($context->get('floor')) ){
            \NQ::simple('hms', NotificationView::ERROR, 'You must select a hall or floor to continue!');
            $cmd = CommandFactory::getCommand('ShowHallNotificationSelect');
            $cmd->redirect();
        }

        $subject   = $context->get('subject');
        $body      = $context->get('body');
        $anonymous = (!is_null($context->get('anonymous')) && $context->get('anonymous')) ? true : false;
        $from      = ($anonymous && \Current_User::allow('hms', 'anonymous_notifications')) ? FROM_ADDRESS : \Current_User::getUsername() . '@' . DOMAIN_NAME;
        $halls     = $context->get('hall');
        $floors    = $context->get('floor');

        if(empty($subject)){
            \NQ::simple('hms', NotificationView::ERROR, 'You must fill in the subject line of the email.');
            $cmd = CommandFactory::getCommand('ShowHallNotificationEdit');
            $cmd->loadContext($context);
            $cmd->redirect();
        } else if(empty($body)){
            \NQ::simple('hms', NotificationView::ERROR, 'You must fill in the message to be sent.');
            $cmd = CommandFactory::getCommand('ShowHallNotificationEdit');
            $cmd->loadContext($context);
            $cmd->redirect();
        }

        //Consider using a batch process instead of doing this this inline


        // Log that this is happening
        if($anonymous){
            HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_ANON_NOTIFICATION_SENT, \Current_User::getUsername());
        }else{
            HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_NOTIFICATION_SENT, \Current_User::getUsername());
        }

        //load the floors
        foreach($floors as $key=>$floor_id){
            $floors[$key] = new Floor($floor_id);
        }

        // TODO accurate logging
        //HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED_ANONYMOUSLY, \Current_User::getUsername(), $hall->hall_name);
        //HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED, \Current_User::getUsername(), $hall->hall_name);

        $floorObj = array();
        //load the halls and add floors that aren't already present, if they have js enabled should be zero
        foreach($halls as $hall){
            $hallObj = new ResidenceHall($hall);

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
            if(!$permission->verify(\Current_User::getUsername(), $floor, 'email')
               && !$permission->verify(\Current_User::getUsername(), $floor->get_parent(), 'email')
               && !\Current_User::allow('hms', 'email_all')
               ){
                continue;
            }

            $students = $floor->getUsernames();

            if($students == null || $students == false || !is_array($students) || sizeof($students) <= 0){
                // If no results, skip to the next floor.
                continue;
            }

            foreach($students as $student){
                HMS_Email::send_email($student . '@' . DOMAIN_NAME, $from, $subject, $body);
            }

            HMS_Activity_Log::log_activity(\Current_User::getUsername(), ($anonymous ? ACTIVITY_FLOOR_NOTIFIED_ANONYMOUSLY : ACTIVITY_FLOOR_NOTIFIED), \Current_User::getUsername(), $floor->where_am_i());
        }

        \NQ::simple('hms', NotificationView::SUCCESS, 'Emails sent successfully!');
        $cmd = CommandFactory::getCommand('DashboardHome');
        $cmd->redirect();
    }
}
