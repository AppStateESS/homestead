<?php

/**
 * HMS_Email class - A class which handles the various Email delevery needs of HMS.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

class HMS_Email{

    function get_tech_contacts()
    {
        $contacts = array();

        $contacts[] = 'jtickle@tux.appstate.edu';
        $contacts[] = 'jbooker@tux.appstate.edu';

        return $contacts;
    }

    function get_housing_contacts()
    {
        $contacts = array();

        $contacts[] = 'dbraswell@appstate.edu';

        return $contacts;
    }

    function send_template_message($to, $subject, $tpl, $tags)
    {
        $content = PHPWS_Template::process($tags, 'hms', $tpl);

        HMS_Email::send_email($to, NULL, $subject, $content);
    }

    /*
     * This is the central message sending function for HMS.
     * Returns true or false.
     */
    function send_email($to, $from, $subject, $content, $cc = NULL, $bcc = NULL)
    {
        # Sanity checking
        if(!isset($to) || is_null($to)){
            return false;
        }

        if(!isset($from) || is_null($from)){
            $from = 'ASU Housing Management System <hms@tux.appstate.edu>';
        }

        if(!isset($subject) || is_null($subject)){
            return false;
        }

        if(!isset($content) || is_nulL($content)){
            return false;
        }

        # Create a Mail object and set it up
        PHPWS_Core::initCoreClass('Mail.php');
        $message = &new PHPWS_Mail;
        $message->addSendTo($to);

        if(isset($cc)){
            $message->addCarbonCopy($cc);
        }

        if(isset($bcc)){
            $message->addBlindCopy($bcc);
        }

        $message->setFrom($from);
        $message->setSubject($subject);
        $message->setMessageBody($content);

        # Send the message
        if(EMAIL_TEST_FLAG){
            HMS_Email::log_email($message);
            $result = true;
        }else{
            $result = $message->send();
        }

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return false;
        }

        return true;
    }

    /**
     * Logs a PHPWS_Mail object to a text file
     */
    function log_email($message)
    {
        // Log the message to a text file
        $fd = fopen(PHPWS_SOURCE_DIR . 'logs/email.log',"a");
        fprintf($fd, "=======================\n");
       
        foreach($message->send_to as $recipient){
            fprintf($fd, "To: %s\n", $recipient);
        }

        if(isset($message->carbon_copy)){
            foreach($message->carbon_copy as $recipient){
                fprintf($fd, "Cc: %s\n", $recipient);
            }
        }

        if(isset($message->blind_copy)){
            foreach($message->blind_copy as $recipient){
                fprintf($fd, "Bcc: %s\n", $bcc);
            }
        }

        fprintf($fd, "From: %s\n", $message->from_address);
        fprintf($fd, "Subject: %s\n", $message->subject_line);
        fprintf($fd, "Content: \n");
        fprintf($fd, "%s\n\n", $message->message_body);

        fclose($fd);
    }

    /**********************
     * Error notification *
     **********************/

    function send_error_notification($content){
        HMS_Email::send_email(HMS_Email::get_technical_contacts(), NULL, 'HMS Error', $content);
    }

    /****************
     * Contact form *
     ****************/

    function send_contact_form()
    {

    }

    /*********************
     * Roommate Messages *
     *********************/

    /********************
     * Lottery Messages *
     ********************/

    function send_lottery_invite($to, $name, $expires_on, $year)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['EXPIRES_ON']  = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;

        HMS_Email::send_template_message($to . '@appstate.edu', '[Testing] You Have Been Selected for On-campus Housing!', 'email/lottery_invite.tpl', $tpl);
    }

    function send_lottery_invite_reminder($to, $name, $expires_on, $year)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['EXPIRES_ON']  = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $hours              = round(($expires_on - mktime()) / 3600);
        
        // TODO:
        //$hours = 

        HMS_Email::send_template_message($to . '@appstate.edu', "[Testing] On-Campus Housing Reminder: Only $hours hours left!", 'email/lottery_invite_reminder.tpl', $tpl);
    }

    function send_lottery_roommate_invite($to, $name, $expires_on, $requestor_name, $hall_room, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $name;
        $tpl['EXPIRES_ON'] = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $tpl['REQUESTOR']   = $requestor_name;
        $tpl['HALL_ROOM']   = $hall_room;

        HMS_Email::send_template_message($to . '@appstate.edu', '[Testing] Roommate Invitation for On-campus Housing!', 'email/lottery_roommate_invite.tpl', $tpl);
    }

    function send_lottery_roommate_reminder($to, $name, $expires_on, $requestor_name, $hall_room, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $name;
        $tpl['EXPIRES_ON'] = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $tpl['REQUESTOR']   = $requestor_name;
        $tpl['HALL_ROOM']   = $hall_room;
        $hours              = round(($expires_on - mktime()) / 3600);

        HMS_Email::send_template_message($to . '@appstate.edu', "[Testing] Roommate Invitation Reminder: Only $hours hours left!", 'email/lottery_roommate_invite_reminder.tpl', $tpl);
    }

    function send_signup_invite($to, $name, $requestor_name, $year)
    {
        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['REQUESTOR']   = $requestor_name;
        $tpl['YEAR']        = $year;

        HMS_Email::send_template_message($to . '@appstate.edu', "[Testing] Signup for On-campus Housing!", 'email/lottery_signup_invite.tpl', $tpl);
    }

    function send_lottery_status_report($status, $log)
    {
        HMS_Email::send_email(HMS_Email::get_tech_contacts(), NULL, "HMS Lottery results: $status", $log);
    }

    function send_assignment_email($to, $name, $location, $roommates, $phone, $movein_time, $type){
        $tpl = array();

        $tpl['NAME']         = $name;
        $tpl['LOCATION']     = $location;
        $tpl['PHONE_NUMBER'] = $phone;
        $tpl['MOVE_IN_TIME'] = $movein_time;
        $tpl['TYPE']         = ($type == TYPE_CONTINUING ? 'RETURNING STUDENTS ONLY' : 'FRESHMAN & TRANSFER ONLY');
        $tpl['DATE']         = strftime("%B %d, %Y");

        foreach($roommates as $roommate){
            $tpl['roommates'][] = array('ROOMMATE' => $roommate);
        }

        HMS_Email::send_template_message($to . '@appstate.edu', '[Testing] Housing Assignment Notice!', 'email/assignment_notice.tpl', $tpl);
    }

    function send_roommate_confirmation($to, $name, $roomie){
        $tpl = array();

        if($name == null){
            PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
            $tpl['NAME'] = HMS_SOAP::get_full_name($to); //to is holding their asu_username
        } else {
            $tpl['NAME'] = $name;
        }

        $tpl['ROOMIE'] = $roomie;

        HMS_Email::send_template_message($to . '@appstate.edu', '[Testing] Roommate Confirmation!', 'email/roommate_confirmation.tpl', $tpl);
    }

    function send_lottery_application_confirmation($to, $name)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $tpl = array();

        if($name == null){
            PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
            $tpl['NAME'] = HMS_SOAP::get_full_name($to); //to is holding their asu_username
        } else {
            $tpl['NAME'] = $name;
        }

        $tpl['TERM'] = HMS_Term::term_to_text(HMS_Term::get_selected_term(), true);

        HMS_Email::send_template_message($to . '@appstate.edu', '[Testing] Lottery Application Confirmation!', 'email/lottery_confirmation.tpl', $tpl);
    }

    function send_hms_application_confirmation($to, $name)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $tpl = array();

        if($name == null){
            PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
            $tpl['NAME'] = HMS_SOAP::get_full_name($to); //to is holding their asu_username
        } else {
            $tpl['NAME'] = $name;
        }

        $tpl['TERM'] = HMS_Term::term_to_text(HMS_Term::get_selected_term(), true);

        HMS_Email::send_template_message($to . '@appstate.edu', '[Testing] Lottery Application Confirmation!', 'email/application_confirmation.tpl', $tpl);
    }

} // End HMS_Email class
?>
