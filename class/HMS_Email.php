<?php

/**
 * HMS_Email class - A class which handles the various Email delevery needs of HMS.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

class HMS_Email{

    public function get_tech_contacts()
    {
        $contacts = array();

        $contacts[] = 'jtickle@tux.appstate.edu';
        $contacts[] = 'jbooker@tux.appstate.edu';

        return $contacts;
    }

    public function get_housing_contacts()
    {
        $contacts = array();

        $contacts[] = 'dbraswell@appstate.edu';
        $contacts[] = 'burlesonst@appstate.edu';

        return $contacts;
    }

    public function send_template_message($to, $subject, $tpl, $tags)
    {
        $content = PHPWS_Template::process($tags, 'hms', $tpl);

        HMS_Email::send_email($to, NULL, $subject, $content);
    }

    /*
     * This is the central message sending public function for HMS.
     * Returns true or false.
     */
    public function send_email($to, $from, $subject, $content, $cc = NULL, $bcc = NULL)
    {
        # Sanity checking
        if(!isset($to) || is_null($to)){
            return false;
        }

        if(!isset($from) || is_null($from)){
            $from = SYSTEM_NAME . ' <' . FROM_ADDRESS .'>';
        }

        if(!isset($subject) || is_null($subject)){
            return false;
        }

        if(!isset($content) || is_nulL($content)){
            return false;
        }

        # Create a Mail object and set it up
        PHPWS_Core::initCoreClass('Mail.php');
        $message = new PHPWS_Mail;

        $message->setFrom($from);
        $message->setSubject($subject);
        $message->setMessageBody($content);

        # Send the message
        if(EMAIL_TEST_FLAG){
            $message->addSendTo('housing_test@tux.appstate.edu');
            HMS_Email::log_email($message);
            $result = true;
        }else{
            $message->addSendTo($to);

            if(isset($cc)){
                $message->addCarbonCopy($cc);
            }

            if(isset($bcc)){
                $message->addBlindCopy($bcc);
            }

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
    public function log_email($message)
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

    public function send_error_notification($content){
        HMS_Email::send_email(HMS_Email::get_technical_contacts(), NULL, 'HMS Error', $content);
    }

    /****************
     * Contact form *
     ****************/

    public function send_contact_form()
    {

    }

    /*********************
     * Roommate Messages *
     *********************/

    /********************
     * Lottery Messages *
     ********************/

    public function send_lottery_invite($to, $name, $expires_on, $year)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['EXPIRES_ON']  = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;

        HMS_Email::send_template_message($to . TO_DOMAIN, 'You Have Been Selected for On-campus Housing!', 'email/lottery_invite.tpl', $tpl);
    }

    public function send_lottery_invite_reminder($to, $name, $expires_on, $year)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['EXPIRES_ON']  = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $hours              = round(($expires_on - mktime()) / 3600);

        // TODO:
        //$hours =

        HMS_Email::send_template_message($to . TO_DOMAIN, "On-Campus Housing Reminder: Only $hours hours left!", 'email/lottery_invite_reminder.tpl', $tpl);
    }

    public function send_lottery_roommate_invite(Student $to, Student $from, $expires_on, $hall_room, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $to->getName();
        $tpl['EXPIRES_ON'] = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $tpl['REQUESTOR']   = $from->getName();
        $tpl['HALL_ROOM']   = $hall_room;

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Roommate Invitation for On-campus Housing!', 'email/lottery_roommate_invite.tpl', $tpl);
    }

    public function send_lottery_roommate_reminder($to, $name, $expires_on, $requestor_name, $hall_room, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $name;
        $tpl['EXPIRES_ON'] = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $tpl['REQUESTOR']   = $requestor_name;
        $tpl['HALL_ROOM']   = $hall_room;
        $hours              = round(($expires_on - mktime()) / 3600);

        HMS_Email::send_template_message($to . TO_DOMAIN, "Roommate Invitation Reminder: Only $hours hours left!", 'email/lottery_roommate_invite_reminder.tpl', $tpl);
    }

    public function send_signup_invite($to, $name, $requestor_name, $year)
    {
        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['REQUESTOR']   = $requestor_name;
        $tpl['YEAR']        = $year;

        HMS_Email::send_template_message($to . TO_DOMAIN, "Signup for On-campus Housing!", 'email/lottery_signup_invite.tpl', $tpl);
    }

    public function send_lottery_status_report($status, $log)
    {
        HMS_Email::send_email(HMS_Email::get_tech_contacts(), NULL, "HMS Lottery results: $status", $log);
    }

    public function send_assignment_email($to, $name, $term, $location, $roommates, $movein_time, $type, $returning){
        $tpl = array();

        $tpl['NAME']            = $name;
        $tpl['TERM']            = Term::toString($term);
        $tpl['LOCATION']        = $location;
        $tpl['MOVE_IN_TIME']    = $movein_time;
        $tpl['DATE']            = strftime("%B %d, %Y");

        if(!is_null($roommates)){
            foreach($roommates as $roommate){
                $tpl['roommates'][] = array('ROOMMATE' => $roommate);
            }
        }

        $sem = Term::getTermSem($term);

        switch($sem){
            case TERM_SPRING:
                HMS_Email::send_template_message($to . TO_DOMAIN, 'Housing Assignment Notice!', 'email/assignment_notice_spring.tpl', $tpl);
                break;
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                HMS_Email::send_template_message($to . TO_DOMAIN, 'Housing Assignment Notice!', 'email/assignment_notice_summer.tpl', $tpl);
                break;
            case TERM_FALL:
                /*
                 if($returning == TRUE){
                 HMS_Email::send_template_message($to . TO_DOMAIN, 'Housing Assignment Notice!', 'email/assignment_notice_returning.tpl', $tpl);
                 }else{
                 HMS_Email::send_template_message($to . TO_DOMAIN, 'Housing Assignment Notice!', 'email/assignment_notice.tpl', $tpl);
                 }
                 */
                HMS_Email::send_template_message($to . TO_DOMAIN, 'Housing Assignment Notice!', 'email/assignment_notice.tpl', $tpl);
                break;
        }
    }

    public function send_roommate_confirmation(Student $to, Student $roomie){
        $tpl = array();

        $tpl['NAME'] = $to->getName();
        $tpl['ROOMIE'] = $roomie->getName();

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Roommate Confirmation!', 'email/roommate_confirmation.tpl', $tpl);
    }

    public function send_lottery_application_confirmation(Student $student, $year)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();

        $tpl['NAME'] = $student->getName();

        $tpl['TERM'] = $year;

        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'On-campus Housing Re-application Confirmation!', 'email/lottery_confirmation.tpl', $tpl);
    }

    /**
     * Sends an email to the specified student to confirm submission of a housing application for a particular term
     * @param $to Student object representing the student to send this email too
     * @param $term The term the housing application was submitted for.
     */
    public function send_hms_application_confirmation(Student $to, $term)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();
        $tpl['NAME'] = $to->getName();

        $tpl['TERM'] = Term::toString($term);

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'On-campus Houisng Application Confirmation!', 'email/application_confirmation.tpl', $tpl);
    }

    /********************
     * Roommate Request *
     ********************/
    public function send_request_emails(HMS_Roommate $request)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $requestorStudent = StudentFactory::getStudentByUsername($request->requestor, $request->term);
        $requesteeStudent = StudentFactory::getStudentByUsername($request->requestee, $request->term);

        // set tags for the email to the person doing the requesting
        $tags = array();
        $tags['REQUESTOR_NAME'] = $requestorStudent->getFullName();
        $tags['REQUESTEE_NAME'] = $requesteeStudent->getFullName();

        HMS_Email::send_template_message($request->requestor . TO_DOMAIN, 'HMS Roommate Request',
                                         'email/roommate_request_requestor.tpl', $tags);
        
        // Extra tags needed for email sent to requested roommmate
        $expire_date = $request->calc_req_expiration_date();
        $tags['EXPIRATION_DATE'] = date('l, F jS, Y', $expire_date);
        $tags['EXPIRATION_TIME'] = date('g:i A', $expire_date);

        HMS_Email::send_template_message($request->requestee . TO_DOMAIN, 'HMS Roommate Request',
                                         'email/roommate_request_requestee.tpl', $tags);

        return TRUE;
    }

    public function send_confirm_emails(HMS_Roommate $request)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $requestorStudent = StudentFactory::getStudentByUsername($request->requestor, $request->term);
        $requesteeStudent = StudentFactory::getStudentByUsername($request->requestee, $request->term);

        $tags = array();
        $tags['REQUESTOR'] = $requestorStudent->getFullName();
        $tags['REQUESTEE'] = $requesteeStudent->getFullName();

        // to the requestor
        HMS_Email::send_template_message($request->requestor . TO_DOMAIN, 'HMS Roommate Confirmed',
                                         'email/roommate_confirmation_requestor.tpl', $tags);

        // to the requestee
        HMS_Email::send_template_message($request->requestee . TO_DOMAIN, 'HMS Roommate Confirmed',
                                         'email/roommate_confirmation_requestee.tpl', $tags);      
        
        return TRUE;
    }

    public function send_reject_emails(HMS_Roommate $request)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $requestorStudent = StudentFactory::getStudentByUsername($request->requestor, $request->term);
        $requesteeStudent = StudentFactory::getStudentByUsername($request->requestee, $request->term);

        $tags = array();
        $tags['REQUESTOR'] = $requestorStudent->getFullName();
        $tags['REQUESTOR_FIRST'] = $requestorStudent->getFirstName();
        $tags['REQUESTEE'] = $requesteeStudent->getFullName();
        $tags['REQUESTEE_FIRST'] = $requesteeStudent->getFirstName();

        // To requestor
        HMS_Email::send_template_message($request->requestor . TO_DOMAIN, 'HMS Roommate Declined',
                                         'email/roommate_reject_requestor.tpl', $tags);

        // to the requestee
        HMS_Email::send_template_message($request->requestee . TO_DOMAIN, 'HMS Roommate Declined',
                                         'email/roommate_reject_requestee.tpl', $tags);

        return TRUE;
    }

    public function send_break_emails(HMS_Roommate $request, $breaker)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $breakee = $request->get_other_guy($breaker);

        $breakerStudent = StudentFactory::getStudentByUsername($breaker, $request->term);
        $breakeeStudent = Studentfactory::getStudentByUsername($breakee, $request->term);
        
        $tags = array();
        $tags['BREAKER'] = $breakerStudent->getFullName();
        $tags['BREAKER_FIRST'] = $breakerStudent->getFirstName();
        $tags['BREAKEE'] = $breakeeStudent->getFullName();

        // to the breaker
        HMS_Email::send_template_message($breaker . TO_DOMAIN, 'HMS Roommate Pairing Broken', 
                                         'email/roommate_break_breaker.tpl', $tags);

        // to the breakee
        HMS_Email::send_template_message($breakee . TO_DOMAIN, 'HMS Roommate Pairing Broken',
                                         'email/roommate_break_breakee.tpl', $tags);
        return TRUE;
    }

    public function send_cancel_emails(HMS_Roommate $request)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $requestorStudent = StudentFactory::getStudentByUsername($request->requestor, $request->term);
        $requesteeStudent = StudentFactory::getStudentByUsername($request->requestee, $request->term);

        $tags = array('REQUESTOR' => $requestorStudent->getFullName(),
                      'REQUESTOR_FIRST' => $requestorStudent->getFirstName(),
                      'REQUESTEE' => $requesteeStudent->getFullName());

        // to the requestor
        HMS_Email::send_template_message($request->requestor . TO_DOMAIN, 'HMS Roommate Request Cancelled',
                                         'email/roommate_request_cancel_requestor.tpl', $tags);

        // to the requestee
        HMS_Email::send_template_message($request->requestee . TO_DOMAIN, 'HMS Roommate Request Cancelled',
                                         'email/roommate_request_cancel_requestee.tpl', $tags);
        return TRUE;
    }

    
    /*******
     * RLC *
     *******/
    public function send_rlc_application_confirmation(Student $to)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();
        $tpl['NAME'] = $to->getName();
        $tpl['TERM'] = Term::toString($to->getApplicationTerm());

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Learning Community Application Confirmation!', 'email/rlc_application_confirmation.tpl', $tpl);
    }

    public function sendRlcApplicationRejected(Student $to)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();
        $tpl['NAME'] = $to->getName();
        $tpl['TERM'] = Term::toString($to->getApplicationTerm());

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Learning Community Application Rejected', 'email/rlc_application_rejection.tpl',$tpl);
    }

    public function send_lottery_assignment_confirmation(Student $to, $location, $term)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');
        $tpl = array();

        $tpl['NAME']     = $to->getName();

        $tpl['TERM']     = Term::toString($term);
        $tpl['LOCATION'] = $location;

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'On-campus Housing Re-assignment Confirmation!', 'email/lottery_self_assignment_confirmation.tpl', $tpl);
    }

    public function sendWaitListApplicationConfirmation(Student $student, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $student->getName();
        $tpl['YEAR'] = $year;

        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'On-campus Housing Waiting List Confirmation', 'email/waitingListConfirmation.tpl', $tpl);
    }

} // End HMS_Email class
?>
