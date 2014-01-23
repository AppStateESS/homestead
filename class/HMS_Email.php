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

class HMS_Email{

    public function get_tech_contacts()
    {
        $contacts = array();

        $contacts[] = 'ticklejw@appstate.edu';
        $contacts[] = 'jb67803@appstate.edu';

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

    public function send_lottery_invite($to, $name, $year)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['YEAR']        = $year;

        HMS_Email::send_template_message($to . TO_DOMAIN, 'Offer for On-Campus Housing', 'email/lottery_invite.tpl', $tpl);
    }

    public function send_lottery_invite_reminder($to, $name, $year)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tpl = array();

        $tpl['NAME']        = $name;
        $tpl['YEAR']        = $year;

        HMS_Email::send_template_message($to . TO_DOMAIN, "Reminder Offer for On-Campus Housing", 'email/lottery_invite_reminder.tpl', $tpl);
    }

    public function send_lottery_roommate_invite(Student $to, Student $from, $expires_on, $hall_room, $year)
    {
        $tpl = array();

        $tpl['NAME'] = $to->getName();
        $tpl['EXPIRES_ON'] = HMS_Util::get_long_date_time($expires_on);
        $tpl['YEAR']        = $year;
        $tpl['REQUESTOR']   = $from->getName();
        $tpl['HALL_ROOM']   = $hall_room;

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Roommate Invitation for On-campus Housing', 'email/lottery_roommate_invite.tpl', $tpl);
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

    public function send_lottery_application_confirmation(Student $student, $year)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();

        $tpl['NAME'] = $student->getName();

        $tpl['TERM'] = $year;

        HMS_Email::send_template_message($student->getUsername() . TO_DOMAIN, 'On-campus Housing Re-application Confirmation!', 'email/lottery_confirmation.tpl', $tpl);
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
    public function sendAssignmentNotice($to, $name, $term, $location, Array $roommates, $moveinTime){
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

    public function send_roommate_confirmation(Student $to, Student $roomie){
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
    public function send_hms_application_confirmation(Student $to, $term)
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
    public function send_emergency_contact_updated_confirmation(Student $to, $term) {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $tpl = array();
        $tpl['NAME'] = $to->getName();
        $tpl['TERM'] = Term::toString($term);

        HMS_Email::send_template_message($to->getUsername() . TO_DOMAIN, 'Emergency Contact Information Updated!', 'email/emergency_contact_update_confirmation.tpl', $tpl);
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

    public function sendRlcApplicationRejected(Student $to, $term)
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
    public function sendWithdrawnSearchOutput($text)
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

        $transport = Swift_SmtpTransport::newInstance('localhost');
        $mailer = Swift_Mailer::newInstance($transport);

        $mailer->send($message);
    }

    public static function sendCheckoutConfirmation(Student $student, InfoCard $infoCard, InfoCardPdfView $infoCardView){
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
        $attachment = Swift_Attachment::newInstance($infoCardView->getPdf()->output('my-pdf-file.pdf', 'S'), 'ResidentInfoCard.pdf', 'application/pdf');
        $message->attach($attachment);

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
        $transport = Swift_SmtpTransport::newInstance('localhost');
        $mailer = Swift_Mailer::newInstance($transport);

        return $mailer->send($message);
    }

    /**
     * Sends an acknowledgment to the person who just submitted a room change request.
     *
     * Template Tags:
     * {STUDENT_NAME}
     *
     * @param $student Student The student who submitted the request
     */
    public static function sendRoomChangeRequestAcknowledgment(Student $student)
    {
        $subject = 'Room Change Request Received';
        $template = 'email/roomChangeRequestAcknowledgment.tpl';

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
     * {REQUESTOR_NAME}
     *
     * @param $requestee Student A person involved in a room change request
     * @param $requestor Student The person who invoked the room change request
     */
    public static function sendRoomChangeParticipantNotice(Student $requestee, Student $requestor)
    {
        $subject = 'Room Change Requested';
        $template = 'email/roomChangeParticipantNotice.tpl';

        $tags = array(
            'REQUESTEE_NAME' => $requestee->getName(),
            'REQUESTOR_NAME' => $requestor->getName()
        );

        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(
                $requestee, $subject, $tags, $template
            )
        );
    }

    /**
     * Sends a notification to the Current RD involved in a room change request
     * letting them know they need to log in and approve
     *
     * Template Tags:
     * {STUDENT_NAME}
     * {CURRENT_ASSIGNMENT}
     * {CELL_PHONE}
     *
     * @param $rd string The username of the RD
     * @param $participant RoomChangeParticipant The Participant object involved
     */
    public static function sendRoomChangeCurrRDNotice($rd, RoomChangeParticipant $p)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        $subject = 'Room Change Approval Required';
        $template = 'email/roomChangeCurrRDNotice.tpl';

        $bid = $p->getBannerId();
        $term = Term::getCurrentTerm();

        $student = StudentFactory::getStudentByBannerID($bid, $term);
        $assign = HMS_Assignment::getAssignmentByBannerID($bid, $term);

        $tags = array(
            'STUDENT_NAME'       => $student->getName(),
            'CURRENT_ASSIGNMENT' => $assign->where_am_i(),
            'CELL_PHONE'         => $p->getCellPhone()
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
    public static function sendRoomChangeFutureRDNotice($rd, RoomChangeParticipant $p)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $subject = 'Room Change Approval Required';
        $template = 'email/roomChangeFutureRDNotice.tpl';

        $bid = $p->getBannerId();
        $term = Term::getCurrentTerm();

        $student = StudentFactory::getStudentByBannerID($bid, $term);
        $bed     = new HMS_Bed($p->getToBed());

        $tags = array(
            'STUDENT_NAME'      => $student->getName(),
            'FUTURE_ASSIGNMENT' => $bed->where_am_i(),
            'CELL_PHONE'        => $p->getCellPhone()
        );

        self::sendSwiftmailMessage(
            self::makeSwiftmailMessage(
                $rd . TO_DOMAIN, $subject, $tags, $template
            )
        );
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
            self::makeSwiftmailMessage(
                $rd . TO_DOMAIN, $subject, $tags, $template
            )
        );
    }

    /**
     * Sends everyone involved in a room change notice when it is fully approved and
     * can happen in the real world.  Note this is a little different than the other
     * ones because it does the looping itself and sends multiple messages.
     *
     * @param $dest
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
            $student = Studentfactory::getStudentByBannerID($p->getBannerID());
            $recipients[] = $student;
            $tags['PARTICIPANTS'][] = array(
                'NAME' => $student->getName()
            );
        }

        foreach($r->getAllPotentialApprovers() as $a) {
            $recipients[] = array($a . TO_DOMAIN => '');
        }

        $message = self::makeSwiftmailMessage(null, $subject, $tags, $template);
        foreach($recipient as $r) {
            $message->setTo($r);
            self::sendSwiftmailMessage($r);
        }
    }

} // End HMS_Email class
?>
