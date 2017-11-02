<?php

namespace Homestead\UI;

use \Homestead\ResidenceHall;
use \Homestead\HMS_Term;
use \Homestead\HMS_Activity_Log;
use \Homestead\HMS_Email;

/**
  * Interface for sending hall emails.
  *
  * @author     Daniel West <dwest at tux dot appstate dot edu>
  * @package    mod
  * @subpackage hms
  */

class Notification {

    public function main($op=null)
    {
        switch($op){
            case 'show_select_hall':
                return Notification::show_select_hall();
            case 'edit':
                $subject = isset($_REQUEST['subject']) ? $_REQUEST['subject'] : null;
                $body    = isset($_REQUEST['body'])    ? $_REQUEST['body']    : null;
                return Notification::show_edit_email('', $subject, $body);
            case 'review':
                return Notification::show_review_email();
            case 'mail':
                return Notification::send_emails();
            case 'confirm':
                return Notification::show_confirmation();
            default:
                return '<h1>Unkown Op</h1>';
        }
    }

    public function show_select_hall()
    {
        /*
        if(!\Current_User::allow('hms', 'email_hall')){
             return \PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        */

        $tpl=array();
        if(\Current_User::allow('hms', 'email_all')){
            $halls = ResidenceHall::get_halls(HMS_Term::get_selected_term());
            $form = new \PHPWS_Form('select_halls_to_email');
            foreach($halls as $hall){
                if($hall->is_online != 1){
                    continue;
                } else {
                    $form->addCheck('hall['.$hall->id.']', $hall->id);
                    $form->setLabel('hall['.$hall->id.']', $hall->hall_name);
                }
            }
            $form->addHidden('type', 'notification');
            $form->addHidden('op',   'edit');
            $form->addSubmit('Continue');

            $i=0;
            $elements = $form->getTemplate();
            foreach($elements as $row){
                //put the first and last elements directly into the template, not the row repeat because they are form tags
                if($i == 0){
                    $tpl['START_FORM'] = $row;
                    $i++;
                    continue;
                } elseif($i == sizeof($elements)-1){
                    $tpl['END_FORM'] = $row;
                    break;
                }

                //even numbered rows are checkboxes, odd are labels
                if($i % 2 == 1)
                    $tpl['halls_list'][$i+1]['LABEL'] = $row; //group the label with the checkbox
                else
                    $tpl['halls_list'][$i]['SELECT'] = $row;

                $i++;
            }
        } else {
            $tpl['SELECT'] = ResidenceHall::show_select_residence_hall('Select recipient Hall', 'notification', 'edit');
        }

        return \PHPWS_Template::process($tpl, 'hms', 'admin/messages.tpl');
    }

    public function show_edit_email($error=null, $subject=null, $body=null)
    {
        $tpl = array();

        if(!is_null($error)){
            $tpl['ERROR'] = $error;
        }

        $tpl['HEADER'] = 'Email';
        $form = new \PHPWS_Form('email_content');

        if(\Current_User::allow('hms', 'anonymous_notifications')){
            $form->addCheck('anonymous');
            $form->setMatch('anonymous', isset($_REQUEST['anonymous']) ? true : false);
            $form->setLabel('anonymous', 'Send Anonymously: ');
        }

        $form->addText('subject', (!is_null($subject) ? $subject : ''));
        $form->setLabel('subject', 'Subject:');
        $form->setSize('subject', 35);

        $form->addTextarea('body', (!is_null($body) ? $body : ''));
        $form->setLabel('body', 'Message:');

        $form->addHidden('type', 'notification');
        $form->addHidden('op',   'review');
        if(isset($_REQUEST['hall'])){
            $form->addHidden('hall', $_REQUEST['hall']);
        }

        $form->addSubmit('Submit');

        $tpl['EMAIL'] = preg_replace('/<br \/>/', '', implode('<br />', $form->getTemplate()), 2);

        return \PHPWS_Template::process($tpl, 'hms', 'admin/hall_notification_email_page.tpl');
    }

    public function show_review_email()
    {
        $tpl = array();
        if(is_array($_REQUEST['hall'])){
            foreach($_REQUEST['hall'] as $hall){
                $_hall = new ResidenceHall($hall);
                $tpl['halls'][] = array('HALL'=>$_hall->hall_name);
            }
        } else {
            $hall = new ResidenceHall($_REQUEST['hall']);
            $tpl['halls'][] = array('HALL'=>$hall->hall_name);
        }

        if(empty($_REQUEST['subject'])){
            return Notification::show_edit_email('You must fill in the subject line of the email.', '', $_REQUEST['body']);
        } else if(empty($_REQUEST['body'])){
            return Notification::show_edit_email('You must fill in the message to be sent.', $_REQUEST['subject'], '');
        }
        $tpl['FROM']    = isset($_REQUEST['anonymous']) && \Current_User::allow('hms', 'anonymous_notification') ? 'housing@appstate.edu' : \Current_User::getEmail();
        $tpl['SUBJECT'] = $_REQUEST['subject'];
        $tpl['BODY']    = $_REQUEST['body'];

        $form = new \PHPWS_Form('edit_email');
        $form->addHidden('anonymous',   isset($_REQUEST['anonymous']) ? $_REQUEST['anonymous'] : '');
        $form->addHidden('subject',     $_REQUEST['subject']);
        $form->addHidden('body',        $_REQUEST['body']);
        $form->addHidden('type',        'notification');
        $form->addHidden('op',          'edit');
        $form->addHidden('hall',        $_REQUEST['hall']);
        $form->addSubmit('back',        'Edit Message');
        $tpl['BACK'] = implode('', $form->getTemplate());

        $form2 = new \PHPWS_Form('review_email');
        $form2->addHidden('anonymous',  isset($_REQUEST['anonymous']) ? $_REQUEST['anonymous'] : '');
        $form2->addHidden('subject',    $_REQUEST['subject']);
        $form2->addHidden('body',       $_REQUEST['body']);
        $form2->addHidden('type',       'notification');
        $form2->addHidden('op',         'mail');
        $form2->addHidden('hall',       $_REQUEST['hall']);
        $form2->addSubmit('Send Emails');
        $tpl['SUBMIT'] = implode('', $form2->getTemplate());

        return \PHPWS_Template::process($tpl, 'hms', 'admin/review_hall_email.tpl');
    }

    public function send_emails()
    {
        if(empty($_REQUEST['subject'])){
            return Notification::show_edit_email('You must fill in the subject line of the email.', '', $_REQUEST['body']);
        } else if(empty($_REQUEST['body'])){
            return Notification::show_edit_email('You must fill in the message to be sent.', $_REQUEST['subject'], '');
        }
        $from       = isset($_REQUEST['anonymous']) && \Current_User::allow('hms', 'anonymous_notification') ? FROM_ADDRESS : \Current_User::getEmail();
        $subject    = $_REQUEST['subject'];
        $body       = $_REQUEST['body'];

        // Log that this is happening
        if($from == FROM_ADDRESS){
            HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_ANON_NOTIFICATION_SENT, \Current_User::getUsername());
        }else{
            HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_NOTIFICATION_SENT, \Current_User::getUsername());
        }

        if(is_array($_REQUEST['hall'])){
            foreach($_REQUEST['hall'] as $hall_id){
                $hall = new ResidenceHall($hall_id);
                $floors = $hall->get_floors();
                foreach($floors as $floor){
                    $rooms = $floor->get_rooms();
                    foreach($rooms as $room){
                        $students = $room->get_assignees();
                        foreach($students as $student){
                            HMS_Email::send_email($student->asu_username . '@appstate.edu', $from, $subject, $body);
                        }
                    }
                }
                if($from == FROM_ADDRESS){
                    HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED_ANONYMOUSLY, \Current_User::getUsername(), $hall->hall_name);
                } else {
                    HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED, \Current_User::getUsername(), $hall->hall_name);
                }
            }
        } else {
            $hall = new ResidenceHall($_REQUEST['hall']);
            $floors = $hall->get_floors();
            foreach($floors as $floor){
                $rooms = $floor->get_rooms();
                foreach($rooms as $room){
                    $students = $room->get_assignees();
                    foreach($students as $student){
                        HMS_Email::send_email($student->asu_username . '@appstate.edu', $from, $subject, $body);
                    }
                }
            }
            if($from == FROM_ADDRESS){
                HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED_ANONYMOUSLY, \Current_User::getUsername(), $hall->hall_name);
            } else {
                HMS_Activity_Log::log_activity(\Current_User::getUsername(), ACTIVITY_HALL_NOTIFIED, \Current_User::getUsername(), $hall->hall_name);
            }
        }

        return Notification::show_confirmation();
    }

    public function show_confirmation()
    {
        return '<font color="green">Emails sent successfully</font><br /><br />Please click '. \PHPWS_Text::secureLink(_('Here'), 'hms', array('type'=>'maintenance','op'=>'show_maintenance_options','tab'=>'maintenance_main')) . ' to return to the main menu.';
    }
}
