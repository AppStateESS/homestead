<?php
/**
 * The HMS_Contact_Form class
 * Handles displaying and submission of the contact form
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Contact_Form{

    function main()
    {
        switch($_REQUEST['op'])
        {
            case 'show_contact_form':
                return HMS_Contact_Form::show_contact_form();
                break;
            case 'submit_contact_form':
                return HMS_Contact_Form::submit_contact_form();
                break;
            default:
                return "Undefined term op: {$_REQUEST['op']}";
                break;
        }
    }

    function show_contact_form()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $tpl = array();
        $tpl['TITLE'] = 'Contact Form';

        $form = &new PHPWS_Form();

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'contact_form');
        $form->addHidden('op', 'submit_contact_form');

        $form->addHidden('asu_username', $_SESSION['asu_username']);
        $form->addHidden('application_term', HMS_SOAP::get_application_term($_SESSION['asu_username']));
        $form->addHidden('student_type', HMS_SOAP::get_student_type($_SESSION['asu_username'], $_SESSION['application_term']));

        $form->addText('name');
        $form->setLabel('name', 'Name');

        $form->addText('email');
        $form->setLabel('email', 'Email Address');
        
        $form->addText('phone');
        $form->setLabel('phone', 'Phone number');

        $form->addDropBox('stype', array('F'=>'New Freshmen', 'T'=>'Transfer', 'C'=>'Returning'));
        $form->setLabel('stype', 'Classification');

        $form->addTextArea('comments');
        $form->setLabel('comments', 'Comments and/or what you were trying to do');

        $form->addSubmit('submit');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/contact_page.tpl');
    }

    /**
     * Called in response to the submission of a contact form
     * Handles emailing the data to a predefined list of admins
     */
    function submit_contact_form()
    {
        PHPWS_Core::initCoreClass('Mail.php');

        $send_to = array();
        $send_to[] = 'brian@tux.appstate.edu';
        $send_to[] = 'jtickle@tux.appstate.edu';
        $send_to[] = 'jbooker@tux.appstate.edu';
        $send_to[] = 'searssr@appstate.edu';
        $send_to[] = 'braswelldl@appstate.edu';
        $send_to[] = 'winebargerab@appstate.edu';
        
        $mail = &new PHPWS_Mail;

        $mail->addSendTo($send_to);
        $mail->setFrom('hms@tux.appstate.edu');
        $mail->setSubject('HMS Contact Form');

        $body = "Username: {$_REQUEST['asu_username']}\n";
        $body .= "Application date: {$_REQUEST['application_term']}\n";
        $body .= "Student Type: {$_REQUEST['student_type']}\n";
        
        $body .= "\n\nInput from student:\n\n";
        $body .= "Name: {$_REQUEST['name']}\n";
        $body .= "Email: {$_REQUEST['email']}\n";
        $body .= "Phone #: {$_REQUEST['phone']}\n";
        $body .= "Type: {$_REQUEST['stype']}\n";
        $body .= "Text field:\n";
        $body .= "{$_REQUEST['comments']}\n\n";
        
        $mail->setMessageBody($body);

        $result = $mail->send();

        $tpl = array();
        $tpl['LOGOUT_LINK'] = PHPWS_Text::secureLink(_('Log Out'), 'users', array('action'=>'user', 'command'=>'logout'));

        return PHPWS_Template::process($tpl, 'hms', 'student/contact_form_thankyou.tpl');
    }
}

?>
