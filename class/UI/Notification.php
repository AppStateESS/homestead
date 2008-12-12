<?php
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
                break;
            case 'edit':
                return Notification::show_edit_email();
                break;
            case 'review':
                return Notification::show_review_email();
                break;
            case 'mail':
                return Notification::send_emails();
                break;
            case 'confirm':
                return Notification::show_confirmation();
                break;
            default:
                return '<h1>Unkown Op</h1>';
                break;
        }
    }

    public function show_select_hall()
    {
        if(!Current_User::allow('hms', 'message_hall')){
             return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $tpl=array();
        if(Current_User::allow('hms', 'message_all')){
            $halls = HMS_Residence_Hall::get_halls();
            $form = new PHPWS_Form('select_halls_to_email');
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
                //put the first and last elements directly into the template, not the row repeat
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
            $tpl['SELECT'] = HMS_Residence_Hall::show_select_residence_hall('Select recipient Hall', 'notification', 'edit');
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/messages.tpl');
    }

    public function show_edit_email($error=null, $subject=null, $body=null)
    {
        $tpl = array();

        if(!is_null($error)){
            $tpl['ERROR'] = $error;
        }

        $tpl['HEADER'] = 'Email';
        $form = new PHPWS_Form('email_content');

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

        $tpl['EMAIL'] = implode('<br />', $form->getTemplate());

        return PHPWS_Template::process($tpl, 'hms', 'admin/hall_notification_email_page.tpl');
    }

    public function show_review_email()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        $tpl = array();
        if(is_array($_REQUEST['hall'])){
            foreach($_REQUEST['hall'] as $hall){
                $_hall = new HMS_Residence_Hall($hall);
                $tpl['halls'][] = array('HALL'=>$_hall->hall_name);
            }
        } else {
            $hall = new HMS_Residence_Hall($_REQUEST['hall']);
            $tpl['halls'][] = array('HALL'=>$hall->hall_name);
        }
        
        if(empty($_REQUEST['subject'])){
            return Notification::show_edit_email('You must fill in the subject line of the email.', '', $_REQUEST['body']);
        } else if(empty($_REQUEST['body'])){
            return Notification::show_edit_email('You must fill in the message to be sent.', $_REQUEST['subject'], '');
        }
        $tpl['SUBJECT'] = $_REQUEST['subject'];
        $tpl['BODY']    = $_REQUEST['body'];

        $form = new PHPWS_Form('edit_email');
        $form->addHidden('subject', $_REQUEST['subject']);
        $form->addHidden('body', $_REQUEST['body']);
        $form->addHidden('type', 'notification');
        $form->addHidden('op',   'edit');
        $form->addHidden('hall', $_REQUEST['hall']);
        $form->addSubmit('back', 'Edit Message');
        $tpl['BACK'] = implode('', $form->getTemplate());

        $form2 = new PHPWS_Form('review_email');
        $form2->addHidden('subject', $_REQUEST['subject']);
        $form2->addHidden('body', $_REQUEST['body']);
        $form2->addHidden('type', 'notification');
        $form2->addHidden('op',   'mail');
        $form2->addHidden('hall', $_REQUEST['hall']);
        $form2->addSubmit('Send Emails');
        $tpl['SUBMIT'] = implode('', $form2->getTemplate());

        return PHPWS_Template::process($tpl, 'hms', 'admin/review_hall_email.tpl');
    }

    public function send_emails()
    {
        if(empty($_REQUEST['subject'])){
            return Notification::show_edit_email('You must fill in the subject line of the email.', '', $_REQUEST['body']);
        } else if(empty($_REQUEST['body'])){
            return Notification::show_edit_email('You must fill in the message to be sent.', $_REQUEST['subject'], '');
        }
        $subject = $_REQUEST['subject'];
        $body    = $_REQUEST['body'];

        //Consider using a batch process instead of doing this this inline
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        if(is_array($_REQUEST['hall'])){
            foreach($_REQUEST['hall'] as $hall_id){
                $hall = new HMS_Residence_Hall($hall_id);
                $floors = $hall->get_floors();
                foreach($floors as $floor){
                    $rooms = $floor->get_rooms();
                    foreach($rooms as $room){
                        $students = $room->get_assignees();
                        foreach($students as $student){
                            HMS_Email::send_email($student->asu_username . '@appstate.edu', Current_User::getEmail(), $subject, $content);
                        }
                    }
                }
            }
        } else {
            $hall = new HMS_Residence_Hall($_REQUEST['hall']);
            $floors = $hall->get_floors();
            foreach($floors as $floor){
                $rooms = $floor->get_rooms();
                foreach($rooms as $room){
                    $students = $room->get_assignees();
                    foreach($students as $student){
                        HMS_Email::send_email($student->asu_username . '@appstate.edu', Current_User::getEmail(), $subject, $content);
                    }
                }
            }
        }

        return Notification::show_confirmation();
    }
 
    public function show_confirmation()
    {
        return '<font color="green">Emails sent successfully</font><br /><br />Please click '. PHPWS_Text::secureLink(_('Here'), 'hms', array('type'=>'maintenance','op'=>'show_maintenance_options','tab'=>'maintenance_main')) . ' to return to the main menu.';
    }
}
?>
