<?php

require_once PHPWS_SOURCE_DIR . 'mod/hms/lib/SwiftMailer/swift_required.php';
require_once PHPWS_SOURCE_DIR . 'mod/hms/lib/PhpMarkdown/Markdown.php';

use \Michelf\Markdown;

/**
 * HMS_Email class - A class which handles the various Email delevery needs of HMS.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

PHPWS_Core::initModClass('hms', 'HMS_Util.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

class HMS_Email{

    public static function get_tech_contacts()
    {
        $contacts = array();

        $contacts[] = 'ticklejw@appstate.edu';
        $contacts[] = 'jb67803@appstate.edu';

        return $contacts;
    }

    public static function get_housing_contacts()
    {
        $contacts = array();

        $contacts[] = 'dbraswell@appstate.edu';
        $contacts[] = 'burlesonst@appstate.edu';

        return $contacts;
    }

    public static function send_template_message($to, $subject, $tpl, $tags)
    {
        $content = PHPWS_Template::process($tags, 'hms', $tpl);

        HMS_Email::send_email($to, NULL, $subject, $content);
    }

    /*
     * This is the central message sending public function for HMS.
     * Returns true or false.
     */
    public static function send_email($to, $from, $subject, $content, $cc = NULL, $bcc = NULL)
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

        $message->addSendTo($to);
        $message->setFrom($from);
        $message->setSubject($subject);
        $message->setMessageBody($content);

        if(isset($cc)){
            $message->addCarbonCopy($cc);
        }

        if(isset($bcc)){
            $message->addBlindCopy($bcc);
        }

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
    public static function log_email($message)
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
        fprintf($fd, "Date: %s\n", date('Y-m-d H:i:s'));
        fprintf($fd, "Content: \n");
        fprintf($fd, "%s\n\n", $message->message_body);

        fclose($fd);
    }

    /**
     * Log a Swift_Message object to a text file
     */
    public static function logSwiftmailMessageLong(Swift_Message $message)
    {
        $fd = fopen(PHPWS_SOURCE_DIR . 'logs/email.log', 'a');
        fprintf($fd, "=======================\n");

        foreach($message->getFrom() as $address => $name) {
            fprintf($fd, "From: %s <%s>\n", $name, $address);
        }

        foreach($message->getTo() as $address => $name) {
            fprintf($fd, "To: %s <%s>\n", $name, $address);
        }

        $cc = $message->getCc();
        if(!empty($cc)){
            foreach($cc() as $address => $name) {
                fprintf($fd, "Cc: %s <%s>\n", $name, $address);
            }
        }

        $bcc = $message->getBcc();
        if(!empty($bcc)){
            foreach($bcc as $address => $name) {
                fprintf($fd, "Bcc: %s <%s>\n", $name, $address);
            }
        }

        fprintf($fd, "Sender: %s\n", $message->getSender());
        fprintf($fd, "Subject: %s\n", $message->getSubject());
        fprintf($fd, "Date: %s\n", date('Y-m-d H:i:s'));
        fprintf($fd, "Content: \n");
        fprintf($fd, "%s\n\n", $message->toString());
    }

    /**
     * PHPWS_Email has a built-in simple logging function.  This replicates
     * the functionality of that function for SwiftMail.
     */
    public static function logSwiftmailMessage(Swift_Message $message)
    {
        $id      = 'id:'       . $message->getId();
        $from    = 'from:'     . $message->getSender();
        $to      = 'to:'       . implode(',', array_keys($message->getTo()));

        // Optional fields, If the message has them, implode the arrays to simple strings.
        $cc      = $message->getCc()        != null ? ('cc:'       . implode(',', array_keys($message->getCc()))) : '';
        $bcc     = $message->getBcc()       != null ? ('bcc:'      . implode(',', array_keys($message->getBcc()))) : '';
        $replyto = $message->getReplyTo()   != null ? ('reply-to:' . implode(',', array_keys($message->getReplyTo()))) : '';

        $subject = 'subject:'  . $message->getSubject();
        $module  = 'module:'   . PHPWS_Core::getCurrentModule();
        $user    = 'user:'     . (Current_User::isLogged() ? Current_User::getUsername() : '');

        PHPWS_Core::log("$id $module $user $subject $from $to $cc $bcc $replyto", 'phpws-mail.log', 'mail');
    }

    /**********************
     * Error notification *
     **********************/

    public static function send_error_notification($content){
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

    public static function send_lottery_invite($to, $name, $year)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['YEAR']        = $year;

        HMS_Email::send_template_message($to . TO_DOMAIN, 'Offer for On-Campus Housing', 'email/lottery_invite.tpl', $tpl);
    }

    public static function send_lottery_invite_reminder($to, $name, $year)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['YEAR']        = $year;

        HMS_Email::send_template_message($to . TO_DOMAIN, "Reminder Offer for On-Campus Housing", 'email/lottery_invite_reminder.tpl', $tpl);
    }

    public static function send_lottery_roommate_invite(Student $to, Student $from, $expires_on, $hall_room, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $to->getName();
        $tpl['EXPIRES_ON'] = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $tpl['REQUESTOR']   = $from->getName();
        $tpl['HALL_ROOM']   = $hall_room;

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Roommate Invitation for On-campus Housing', 'email/lottery_roommate_invite.tpl', $tpl);
    }

    public static function send_lottery_roommate_reminder($to, $name, $expires_on, $requestor_name, $hall_room, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $name;
        $tpl['EXPIRES_ON'] = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $tpl['REQUESTOR']   = $requestor_name;
        $tpl['HALL_ROOM']   = $hall_room;
        $hours              = round(($expires_on - time()) / 3600);

        HMS_Email::send_template_message($to . TO_DOMAIN, "Roommate Invitation Reminder: Only $hours hours left!", 'email/lottery_roommate_invite_reminder.tpl', $tpl);
    }

    public static function send_lottery_application_confirmation(Student $student, $year)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();

        $tpl['NAME'] = $student->getName();

        $tpl['TERM'] = $year;

        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'On-campus Housing Re-application Confirmation!', 'email/lottery_confirmation.tpl', $tpl);
    }

    public static function send_lottery_assignment_confirmation(Student $to, $location, $term)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');
        $tpl = array();

        $tpl['NAME']     = $to->getName();

        $tpl['TERM']     = Term::toString($term);
        $tpl['LOCATION'] = $location;

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'On-campus Housing Re-assignment Confirmation!', 'email/lottery_self_assignment_confirmation.tpl', $tpl);
    }

    public static function sendWaitListApplicationConfirmation(Student $student, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $student->getName();
        $tpl['YEAR'] = $year;

        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'On-campus Housing Waiting List Confirmation', 'email/waitingListConfirmation.tpl', $tpl);
    }

    /**
     * Sends an individual assignment notice message.
     *
     * @param String $to ASU username
     * @param String $name Student's first and last name
     * @param String $term
     * @param String $location
     * @param Array $roommates
     * @param String $moveinTime
     */
    public static function sendAssignmentNotice($to, $name, $term, $location, Array $roommates, $moveinTime){
        $tpl = array();

        $tpl['NAME']            = $name;
        $tpl['TERM']            = Term::toString($term);
        $tpl['LOCATION']        = $location;
        $tpl['MOVE_IN_TIME']    = $moveinTime;
        $tpl['DATE']            = strftime("%B %d, %Y");

        if(!empty($roommates)){
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

    public static function send_roommate_confirmation(Student $to, Student $roomie){
        $tpl = array();

        $tpl['NAME'] = $to->getName();
        $tpl['ROOMIE'] = $roomie->getName();

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Roommate Confirmation!', 'email/roommate_confirmation.tpl', $tpl);
    }


    /**
     * Sends an email to the specified student to confirm submission of a housing application for a particular term
     * @param $to Student object representing the student to send this email too
     * @param $term The term the housing application was submitted for.
     */
    public static function send_hms_application_confirmation(Student $to, $term)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();
        $tpl['NAME'] = $to->getName();

        $tpl['TERM'] = Term::toString($term);

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'On-campus Housing Application Confirmation!', 'email/application_confirmation.tpl', $tpl);
    }

    /**************************************
     * Emergency Contact & Missing Person *
     **************************************/

    /**
     * Sends an email to the specified student to confirm any updates to their
     * emergency contact and missing person information for a particular term.
     *
     * @param $to Student object representing the student to send this email to
     * @param $term The term the emergency contact info was updated for
     */
    public static function send_emergency_contact_updated_confirmation(Student $to, $term) {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();
        $tpl['NAME'] = $to->getName();
        $tpl['TERM'] = Term::toString($term);

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Emergency Contact Information Updated!', 'email/emergency_contact_update_confirmation.tpl', $tpl);
    }

    /********************
     * Roommate Request *
     ********************/
    public static function send_request_emails(HMS_Roommate $request)
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

    public static function send_confirm_emails(HMS_Roommate $request)
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

    public static function send_reject_emails(HMS_Roommate $request)
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

    public static function send_break_emails(HMS_Roommate $request, $breaker)
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

    public static function send_cancel_emails(HMS_Roommate $request)
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
    public static function send_rlc_application_confirmation(Student $to)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();
        $tpl['NAME'] = $to->getName();
        $tpl['TERM'] = Term::toString($to->getApplicationTerm());

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Learning Community Application Confirmation!', 'email/rlc_application_confirmation.tpl', $tpl);
    }

    public static function sendRlcApplicationRejected(Student $to, $term)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();
        $tpl['NAME'] = $to->getName();
        $tpl['TERM'] = Term::toString($term);

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Learning Community Application Rejected', 'email/rlc_application_rejection.tpl',$tpl);
    }

    public static function sendRlcInviteEmail(Student $student, HMS_Learning_Community $community, $term, $respondByTimestamp)
    {
        $to = $student->getUsername() . TO_DOMAIN;
        $subject = 'Response Needed: Residential Learning Community Invitation';

        $tags = array();
        $tags['NAME'] = $student->getName();
        $tags['COMMUNITY_NAME'] = $community->get_community_name();
        $tags['TERM'] = Term::toString($term) . ' - ' . Term::toString(Term::getNextTerm($term));
        $tags['COMMUNITY_TERMS_CONDITIONS'] = $community->getTermsConditions();
        $tags['RESPOND_BY'] = date("l, F jS, Y", $respondByTimestamp) . ' at ' . date("ga", $respondByTimestamp);

        HMS_Email::send_template_message($to, $subject, 'email/RlcInvite.tpl', $tags);
    }

    /**
     * Sends the email for the nightly withdrawn search output.
     *
     * @param String $text
     */
    public static function sendWithdrawnSearchOutput($text)
    {
        $to = array('jb67803@appstate.edu', 'burlesonst@appstate.edu');
        $subject = 'Withdrawn Student Search';

        HMS_Email::send_email($to, null, $subject, $text);
    }

    public static function sendReportCompleteNotification($username, $reportName)
    {
        $to = $username . TO_DOMAIN;
        $subject = '[hms] Report Complete: ' . $reportName;

        $tpl = array();
        $tpl['REPORT_NAME'] = $reportName;

        HMS_Email::send_template_message($to, $subject, 'email/ReportCompleteNotification.tpl', $tpl);
    }

    public static function sendCheckinConfirmation(Student $student, InfoCard $infoCard, InfoCardPdfView $infoCardView){

        $tags['NAME']       = $student->getName();
        $tags['HALL_NAME']  = $infoCard->getHall()->getHallName();
        $tags['ASSIGNMENT'] = $infoCard->getRoom()->where_am_i();

        $content = PHPWS_Template::process($tags, 'hms', 'email/checkinConfirmation.tpl');

        $htmlContent = Markdown::defaultTransform($content);

        $message = Swift_Message::newInstance();

        $message->setSubject('Check-in Confirmation');
        $message->setFrom(array(FROM_ADDRESS => SYSTEM_NAME));
        $message->setTo(array(($student->getUsername() . TO_DOMAIN) => $student->getName()));

        $message->setBody($content);
        $message->addPart($htmlContent, 'text/html');

        // Attach info card
        $attachment = Swift_Attachment::newInstance($infoCardView->getPdf()->output('my-pdf-file.pdf', 'S'), 'ResidentInfoCard.pdf', 'application/pdf');
        $message->attach($attachment);
        
        if(EMAIL_TEST_FLAG) {
        	self::logSwiftmailMessageLong($message);
            return;
        }

        $transport = Swift_SmtpTransport::newInstance('localhost');
        $mailer = Swift_Mailer::newInstance($transport);

        $mailer->send($message);
    }

    public static function sendCheckoutConfirmation(Student $student, InfoCard $infoCard) {
        $tags['NAME']       = $student->getName();
        $tags['ASSIGNMENT'] = $infoCard->getRoom()->where_am_i();

        $content = PHPWS_Template::process($tags, 'hms', 'email/checkoutConfirmation.tpl');
        $htmlContent = Markdown::defaultTransform($content);

        $message = Swift_Message::newInstance();

        $message->setSubject('Check-out Confirmation');
        $message->setFrom(array(FROM_ADDRESS => SYSTEM_NAME));
        $message->setTo(array(($student->getUsername() . TO_DOMAIN) => $student->getName()));

        $message->setBody($content);
        $message->addPart($htmlContent, 'text/html');

        // Attach info card
        //$attachment = Swift_Attachment::newInstance($infoCardView->getPdf()->output('my-pdf-file.pdf', 'S'), 'ResidentInfoCard.pdf', 'application/pdf');
        //$message->attach($attachment);
        
        if(EMAIL_TEST_FLAG) {
            self::logSwiftmailMessageLong($message);
            return;
        }

        $transport = Swift_SmtpTransport::newInstance('localhost');
        $mailer = Swift_Mailer::newInstance($transport);

        $mailer->send($message);
    }

    /**
     * makeSwiftmailMessage
     *
     * I saw some copypasta and decided to wrap this up in a useful function.  This
     * makes a new SwiftMail message from the system, to the provided recipient.
     *
     * The recipient can be a Student object, or the SwiftMail style array notation.
     *
     * Note that this function just makes the message, to which you can add further
     * attachments or otherwise change how you please.  To send it, I recommend
     * @see HMS_Email::sendSwiftmailMessage()
     *
     * @param $to Student The student to which you want to send a message
     * @param $subject string The subject line
     * @param $tags Array The template tags to replace
     * @param $tpl string The template file (module 'hms' is implied)
     * @return Swift_Message An instance of Swift_Message with text and html parts,
     *                        from, to, and subject already set up
     */
    public static function makeSwiftmailMessage($to, $subject, $tags, $tpl)
    {
        if($to instanceof Student) {
            $to = array(($to->getUsername() . TO_DOMAIN) => $to->getName());
        }

        $content = PHPWS_Template::process($tags, 'hms', $tpl);
        $htmlContent = Markdown::defaultTransform($content);

        $message = Swift_Message::newInstance();

        $message->setSubject($subject);
        $message->setFrom(array(FROM_ADDRESS => SYSTEM_NAME));

        if(!is_null($to)) {
            $message->setTo($to);
        }

        $message->setBody($content);
        $message->addPart($htmlContent, 'text/html');

        return $message;
    }

    /**
     * sendSwiftmailMessage
     *
     * Sets up transports so you don't have to; a convenience function to use a sensible
     * transport to send a Swift_Message object.
     *
     * You can either make a Swift_Message manually or use
     * @see HMS_Email::makeSwiftmailMessageFromSystemToStudent()
     *
     * @param $message Swift_Message The message to send
     * @return mixed Whatever comes back from Swift_Mailer::send()
     */
    public static function sendSwiftmailMessage(Swift_Message $message)
    {
        if(EMAIL_TEST_FLAG) {
            self::logSwiftmailMessageLong($message);
        } else {
            $transport = Swift_SmtpTransport::newInstance('localhost');
            $mailer = Swift_Mailer::newInstance($transport);

            self::logSwiftmailMessage($message);
            return $mailer->send($message);
        }
    }

    /**
     * Sends an acknowledgment to the person who just submitted a room change request.
     *
     * Template Tags:
     * {STUDENT_NAME}
     *
     * @param $student Student The student who submitted the request
     */
    public static function sendRoomChangeRequestReceivedConfirmation(Student $student)
    {
        $subject = 'Room Change Request Received';
        $template = 'email/roomChangeRequestReceivedConfirmation.tpl';

        $tags = array(
            'STUDENT_NAME' => $student->getName()
        );

        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(
                $student, $subject, $tags, $template
            )
        );
    }

    /**
     * Sends a notification to anyone (other than the submitter) involved in a room
     * change request.
     *
     * Template Tags:
     * {REQUESTEE_NAME}
     * PARTICIPANTS row repeat:
     *   {NAME}
     *   {CURRENT}
     *   {DESTINATION}
     *
     * @param $requestor RoomChangeParticipant The person who invoked the room change request
     * @param $requestee RoomChangeParticipant A person involved in a room change request
     */
    public static function sendRoomChangeParticipantNotice(RoomChangeParticipant $requestor, RoomChangeParticipant $requestee)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $subject = 'Room Change Requested';
        $template = 'email/roomChangeParticipantNotice.tpl';
        $term = Term::getCurrentTerm();

        $requestorBid = $requestor->getBannerId();
        $requestorStudent = StudentFactory::getStudentByBannerID($requestorBid, $term);
        $requestorCurrent = HMS_Assignment::getAssignmentByBannerID($requestorBid, $term);
        $requestorFuture  = new HMS_Bed($requestor->getToBed());

        $requesteeBid = $requestee->getBannerId();
        $requesteeStudent = StudentFactory::getStudentByBannerID($requesteeBid, $term);
        $requesteeCurrent = HMS_Assignment::getAssignmentByBannerID($requesteeBid, $term);
        $requesteeFuture  = new HMS_Bed($requestee->getToBed());

        $tags = array(
            'REQUESTOR_NAME' => $requestorStudent->getName(),
            'PARTICIPANTS'   => array(
                array(  // Requestor
                    'NAME'    => $requestorStudent->getName(),
                    'CURRENT' => $requestorCurrent->where_am_i(),
                    'FUTURE'  => $requestorFuture->where_am_i()
                ),
                array(  // Requestee
                    'NAME'    => $requesteeStudent->getName(),
                    'CURRENT' => $requesteeCurrent->where_am_i(),
                    'FUTURE'  => $requesteeFuture->where_am_i()
                )
            )
        );

        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(
                $requesteeStudent, $subject, $tags, $template
            )
        );
    }

    /**
     * Sends a notification to the Current RD involved in a room change request
     * letting them know they need to log in and approve
     *
     * Template Tags:
     * {STUDENT_NAME}
     * {BANNER_ID}
     * {CURRENT_ASSIGNMENT}
     * {CELL_PHONE}
     *
     * @param $rd string The username of the RD
     * @param $participant RoomChangeParticipant The Participant object involved
     */
    public static function sendRoomChangeCurrRDNotice(RoomChangeRequest $request)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClasS('hms', 'HMS_Bed.php');

        $subject = 'Room Change Approval Required';
        $template = 'email/roomChangeCurrRDNotice.tpl';

        $tags = array('PARTICIPANTS' => array());

        $rds = array();

        $term = Term::getCurrentTerm();
        foreach($request->getParticipants() as $p) {
            // Add participant's RD(s) to recipients
            $rds = array_merge($rds, $p->getCurrentRdList());

            $bid = $p->getBannerId();
            $student = StudentFactory::getStudentByBannerID($bid, $term);
            $assign = HMS_Assignment::getAssignmentByBannerID($bid, $term);

            $participantTags = array(
                'BANNER_ID'          => $student->getBannerId(),
                'NAME'               => $student->getName(),
                'CURRENT'            => $assign->where_am_i()
            );

            // If they have a future assignment, show it
            $futureBedId = $p->getToBed();
            if($futureBedId) {
                $bed = new HMS_Bed($futureBedId);
                $participantTags['DESTINATION'] = $bed->where_am_i();
            }

            $tags['PARTICIPANTS'][] = $participantTags;
        }

        // In case an RD ends up in here several times, no need for dup emails
        $recips = array_unique($rds);

        $message = self::makeSwiftmailMessage(null, $subject, $tags, $template);

        foreach($recips as $recip) {
            $message->setTo($recip . TO_DOMAIN);
            self::sendSwiftmailMessage($message);
        }
    }

    /**
     * Sends a notification to the future roommate that they should make sure
     * the other side of the room is welcoming to a POTENTIAL BUT UNCONFIRMED
     * new friend.
     *
     * Template Tags:
     * {ROOMMATE} (the name of the person receiving the email)
     *
     * @param $student Student The person to notify
     */
    public static function sendRoomChangePreliminaryRoommateNotice(Student $student)
    {
        $subject = 'Roommate Notice';
        $template = 'email/roomChangePreliminaryRoommateNotice.tpl';

        $tags = array(
            'ROOMMATE' => $student->getName()
        );

        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(
                $student, $subject, $tags, $template
            )
        );
    }

    /**
     * Sends a notification to the future roommate that this is confirmed.
     *
     * Template Tags:
     * {ROOMMATE} (the name of the person already in a room)
     * {NAME} (the name of the person moving in)
     *
     * @param $student Student The person to notify
     * @param $newRoomie Student The person moving in
     */
    public static function sendRoomChangeApprovedNewRoommateNotice(Student $student, Student $newRoomie)
    {
        $subject = 'Roommate Confirmation';
        $template = 'email/roomChangeApprovedNewRoommateNotice.tpl';

        $tags = array(
            'ROOMMATE' => $student->getName(),
            'NAME' => $newRoomie->getName()
        );

        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(
                $student, $subject, $tags, $template
            )
        );
    }

    /**
     * Sends a notification to the old roommate that this is confirmed.
     *
     * Template Tags:
     * {ROOMMATE} (the name of the person who is NOT moving)
     * {NAME} (the name of the person who IS moving)
     */
    public static function sendRoomChangeApprovedOldRoommateNotice(Student $student, Student $oldRoomie)
    {
        $subject = 'Roommate Change Notice';
        $template = 'email/roomChangeApprovedOldRoommateNotice.tpl';

        $tags = array(
            'ROOMMATE' => $student->getName(),
            'NAME' => $oldRoomie->getName()
        );

        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(
                $student, $subject, $tags, $template
            )
        );
    }

    /**
     * Sends a notification to the Future RD involved in a room change request
     * letting them know they need to log in and approve
     *
     * Template Tags:
     * {STUDENT_NAME}
     * {FUTURE_ASSIGNMENT}
     * {CELL_PHONE}
     *
     * @param $rd string The username of the RD
     * @param $participant RoomChangeParticipant The Participant object involved
     */
    public static function sendRoomChangeFutureRDNotice(RoomChangeRequest $request)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $subject = 'Room Change Approval Required';
        $template = 'email/roomChangeFutureRDNotice.tpl';

        $tags = array('PARTICIPANTS' => array());

        $rds = array();

        $term = Term::getCurrentTerm();
        foreach($request->getParticipants() as $p) {
            // Add participant's future RD(s) to recipients
            $rds = array_merge($rds, $p->getFutureRdList());

            $bid = $p->getBannerId();
            $student = StudentFactory::getStudentByBannerID($bid, $term);
            $assign = HMS_Assignment::getAssignmentByBannerID($bid, $term);
            $future = new HMS_Bed($p->getToBed());

            $participantTags = array(
                'BANNER_ID'   => $student->getBannerId(),
                'NAME'        => $student->getName(),
                'CURRENT'     => $assign->where_am_i(),
                'DESTINATION' => $future->where_am_i()
            );

            $tags['PARTICIPANTS'][] = $participantTags;
        }

        // In case an RD ends up in here several times, no need for dup emails
        $recips = array_unique($rds);

        $message = self::makeSwiftmailMessage(null, $subject, $tags, $template);

        foreach($recips as $recip) {
            $message->setTo($recip . TO_DOMAIN);
            self::sendSwiftmailMessage($message);
        }
    }

    /**
     * Sends a notification to the HMS Room Change Administrator for final approval
     * of a room change request
     *
     * Template Tags:
     * PARTICIPANTS row repeat with value {NAME}
     *
     * @param $request RoomChangeRequest The Room Change Request needing approval
     * TODO: Add Banner IDs and to/from beds
     */
    public static function sendRoomChangeAdministratorNotice(RoomChangeRequest $r)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $subject = 'Room Change Approval Required';
        $template = 'email/roomChangeAdministratorNotice.tpl';

        $tags = array(
            'PARTICIPANTS' => array()
        );

        foreach($r->getParticipants() as $p) {
            $student = StudentFactory::getStudentByBannerID($p->getBannerId(), $r->getTerm());
            $tags['PARTICIPANTS'][] = array(
                'NAME' => $student->getName()
            );
        }

        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(FROM_ADDRESS, $subject, $tags, $template)
        );
    }

    /**
     * Sends everyone involved in a room change notice when it is fully approved and
     * can happen in the real world.  Note this is a little different than the other
     * ones because it does the looping itself and sends multiple messages.
     *
     * @param $r RoomChangeRequest The Room Change Request that is in process
     * TODO: Add to/from bed for each participant
     */
    public static function sendRoomChangeInProcessNotice(RoomChangeRequest $r)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $subject = 'Room Change Approved!';
        $template = 'email/roomChangeApprovalNotice.tpl';

        $tags = array(
            'PARTICIPANTS' => array()
        );

        $recipients = array();

        foreach($r->getParticipants() as $p) {
            $student = Studentfactory::getStudentByBannerID($p->getBannerID(), $r->getTerm());
            $current = new HMS_Bed($p->getFromBed());
            $future = new HMS_Bed($p->getToBed());

            $recipients[] = $student;

            $tags['PARTICIPANTS'][] = array(
                'NAME' => $student->getName(),
                'CURRENT_LOCATION' => $current->where_am_i(),
                'FUTURE_LOCATION' => $future->where_am_i()
            );
        }

        foreach($r->getAllPotentialApprovers() as $a) {
            $recipients[] = array($a . TO_DOMAIN => '');
        }

        $message = self::makeSwiftmailMessage(null, $subject, $tags, $template);
        foreach($recipients as $r) {
            if ($r instanceof Student) {
                $message->setTo($r->getUsername() . TO_DOMAIN);
            }else {
                $message->setTo($r);
            }
            self::sendSwiftmailMessage($message);
        }
    }

    /**
     * Sends the appropriate emails for a student-cancelled room change request.
     *
     * @param $r RoomChangeRequest The Room Change Request that has been cancelled
     * @param $canceller Student|null The student who cancelled the request
     */
    public static function sendRoomChangeCancelledNotice(RoomChangeRequest $r, Student $canceller = null)
    {
        $subject = 'Room Change Cancelled';
        $template = 'email/roomChangeCancelledNotice.tpl';

        $recipients = array();

        $tags = array(
            'PARTICIPANTS' => array()
        );

        if($canceller instanceof Student) {
            $tags['CANCELLER'] = $canceller->getName();
        }

        $reason = $r->getDeniedReasonPublic();
        if(!is_null($reason) && !empty($reason)) {
            $tags['REASON'] = $reason;
        }

        // Add information about participants, also add each participant to recipients
        foreach($r->getParticipants() as $p) {
            $student = StudentFactory::getStudentByBannerID($p->getBannerID(), $r->getTerm());

            $recipients[] = $student;

            $tags['PARTICIPANTS'][] = array(
                'NAME' => $student->getName()
            );
        }

        // Add any approvers that may have seen the previous email to recipients
        foreach($r->getAllPotentialApprovers() as $a) {
            $recipients[] = array($a . TO_DOMAIN => '');
        }

        // Send a message per recipient
        $message = self::makeSwiftmailMessage(null, $subject, $tags, $template);
        foreach($recipients as $r) {
            if ($r instanceof Student) {
                $message->setTo($r->getUsername() . TO_DOMAIN);
            }else {
                $message->setTo($r);
            }
            self::sendSwiftmailMessage($message);
        }
    }

    /**
     * Sends the appropriate emails for an officially denied room change request.
     *
     * @param $r RoomChangeRequest The Room Change Request that has been denied
     */
    public static function sendRoomChangeDeniedNotice(RoomChangeRequest $r)
    {
        $subject = 'Room Change Denied';
        $template = 'email/roomChangeDeniedNotice.tpl';

        $recipients = array();

        $tags = array(
            'PARTICIPANTS' => array()
        );

        $reason = $r->getDeniedReasonPublic();
        if(!is_null($reason) && !empty($reason)) {
            $tags['REASON'] = $reason;
        }

        // Add information about participants, also add each participant to recipients
        foreach($r->getParticipants() as $p) {
            $student = StudentFactory::getStudentByBannerID($p->getBannerID(), $r->getTerm());

            $recipients[] = $student;

            $tags['PARTICIPANTS'][] = array(
                'NAME' => $student->getName()
            );
        }

        // Add any approvers that may have seen the previous email to recipients
        foreach($r->getAllPotentialApprovers() as $a) {
            $recipients[] = array($a . TO_DOMAIN => '');
        }

        // Send a message per recipient
        $message = self::makeSwiftmailMessage(null, $subject, $tags, $template);
        foreach($recipients as $r) {
            if ($r instanceof Student) {
                $message->setTo($r->getUsername() . TO_DOMAIN);
            }else {
                $message->setTo($r);
            }
            self::sendSwiftmailMessage($message);
        }
    }
    
    /**
     * Send notice to student about assessed damage amount.
     * 
     * @param Student $student
     * @param string $term
     * @param integer $billedAmount
     */
    public static function sendDamageNotification(Student $student, $term, $billedAmount)
    {
    	$subject = 'University Housing Room Damages Billed';
        $template = 'email/roomDamageNotice.tpl';
        
        $tags = array();
        $tags['NAME'] = $student->getName();
        $tags['AMOUNT'] = $billedAmount;
        $tags['TERM'] = $term;
        
        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(
                $student, $subject, $tags, $template
            )
        );
    }

} // End HMS_Email class
?>
