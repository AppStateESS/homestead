<?php

/**
 * HMS Roommate class - Handles creating, confirming, and deleting roommate groups
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */
 
// The number of seconds before a roommate request expires, (hrs * 60 * 60) 
define('ROOMMATE_REQ_TIMEOUT', 259200); // 259200 = 72 hours

PHPWS_Core::initModClass('hms', 'exception/RoommateException.php');

class HMS_Roommate
{

    var $id                = 0;
    var $term              = null;
    var $requestor         = null;
    var $requestee         = null;
    var $confirmed         = 0;
    var $requested_on      = 0;
    var $confirmed_on      = null;

    /**
     * Constructor
     */
    public function HMS_Roommate($id = 0)
    {
        if(!$id) {
            return;
        }

        $this->id = $id;
        $db = new PHPWS_DB('HMS_Roommate');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if(!$result || PHPWS_Error::logIfError($result)) {
            $this->id = 0;
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }

    public function request($requestor, $requestee, $term)
    {
        if(HMS_Roommate::can_live_together($requestor, $requestee, $term) != E_SUCCESS) {
            return false;
        }

        $this->term         = isset($term) ? $term : $_SESSION['application_term'];
        $this->requestor    = $requestor;
        $this->requestee    = $requestee;
        $this->confirmed    = 0;
        $this->requested_on = mktime();

        return true;
    }

    public function confirm()
    {
        if($id == 0)
            return false;

        $this->confirmed    = 1;
        $this->confirmed_on = mktime();

        return true;
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_roommate');
        $result = $db->saveObject($this);
        if(!$result || PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        return true;

    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $this->id = 0;

        return TRUE;
    }

    public function get_other_guy($username)
    {
        if(trim($this->requestor) == trim($username)) {
            return $this->requestee;
        }
        return $this->requestor;
    }

    /******************
     * Static Methods *
     ******************/

    public function get_all_confirmed_roommates($term = NULL, $random = FALSE)
    {
        if(is_null($term)) {
            $term = Term::getSelectedTerm();
        }

        $db = &new PHPWS_DB('hms_roommate');
        $db->addWhere('term', $term);
        $db->addWhere('confirmed', 1);
        if($random) {
            $db->addOrder('random');
        }
        $db->addColumn('requestor');
        $db->addColumn('requestee');
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
        }

        return $result;
    }
     
    public function main()
    {
        if( !Current_User::allow('hms', 'roommate_maintenance') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        switch($_REQUEST['op'])
        {
            case 'show_admin_create_roommate_group':
                return HMS_Roommate::show_admin_create_roommate_group();
                break;
            case 'show_admin_create_roommate_group_result':
                return HMS_Roommate::show_admin_create_roommate_group_result();
                break;
            case 'show_confirmed_roommates':
                return HMS_Roommate::show_confirmed_roommates();
                break;
            case 'delete_roommate_group':
                return HMS_Roommate::delete_roommate_group();
                break;
            default:
                echo "Unknown roommate op {$REQUEST['op']}";
                break;
        }
    }

    /**
     * Checks whether a given pair are involved in a roommate request already.
     *
     * @returns TRUE if so, FALSE if not
     *
     * @param a A user to check on
     * @param b Another user to check on
     */
    public function have_requested_each_other($a, $b, $term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('term', $term);
        $db->addWhere('confirmed', 0, NULL, 'AND');
        $db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        $db->addWhere('requestor', $a, 'ILIKE', 'AND', 'ab');
        $db->addWhere('requestee', $b, 'ILIKE', 'AND', 'ab');
        $db->addWhere('requestor', $b, 'ILIKE', 'AND', 'ba');
        $db->addWhere('requestee', $a, 'ILIKE', 'AND', 'ba');
        $db->setGroupConj('ab', 'AND');
        $db->setGroupConj('ba', 'OR');

        $db->groupIn('ab','ba');

        $result = $db->count();

        if($result > 1) {
            // TODO: Log Weird Situation
        }

        return ($result > 0 ? TRUE : FALSE);
    }

    /* 
     * Returns TRUE if the student has a confirmed roommate, FALSE otherwise
     */ 
    public function has_confirmed_roommate($asu_username, $term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addwhere('term', $term);
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->addwhere('requestee', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->setGroupConj('grp', 'AND');
        $db->addWhere('confirmed', 1);
        $result = (int)$db->count();

        if(PHPWS_Error::logIfError($result))
            return $result;

        if($result > 1) {
            // TODO: Log Weird Situation
        }

        return ($result > 0 ? TRUE : FALSE);
    }
    
    /*
     * Returns the given user's confirmed roommate or FALSE if the roommate is unconfirmed
     */
    public function get_confirmed_roommate($asu_username, $term)
    {
        
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->addWhere('requestee', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->setGroupConj('grp', 'AND');
        $db->addWhere('confirmed', 1);
        $db->addWhere('term', $term);
        $db->addColumn('requestor');
        $db->addColumn('requestee');
        
        //$db->setTestMode();
        
        $result = $db->select('row');

        if(count($result) > 1) {
            // TODO: Log Weird Situation
        }

        if(count($result) == 0){
            return null;
        }

        if(trim($result['requestor']) == trim($asu_username)) {
            return $result['requestee'];
        }
        
        return $result['requestor'];
    }

    public function get_pending_roommate($asu_username, $term)
    {   
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->addWhere('requestee', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->setGroupConj('grp', 'AND');
        $db->addWhere('confirmed', 0);
        $db->addWhere('term', $term);
        $db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        $db->addColumn('requestor');
        $db->addColumn('requestee');
        $result = $db->select('row');

        if(count($result) > 1) {
            // TODO: Log Weird Situation
        }

        if(count($result) == 0)
            return null;
            
            test($result,1);

        if(trim($result['requestor']) == trim($asu_username)) {
            return $result['requestee'];
        }

        return $result['requestor'];
    }

    /**
     * Checks whether a given user has made a roommate request which is still pending.
     *
     * @returns TRUE if so, FALSE if not
     *
     * @param username The user to check on
     */
    public function has_roommate_request($username,$term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $username, 'ILIKE');
        $db->addWhere('confirmed', 0);
        $db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        $db->addWhere('term', $term);
        $result = $db->count();

        if(PHPWS_Error::logIfError($result))
            return $result;

        return ($result > 0 ? TRUE : FALSE);
    }

    /**
     * Returns the asu username of the student which the given user has requested, or NULL
     * if either the user has not requested anyone or the pairing is confirmed.
     */
    public function get_unconfirmed_roommate($asu_username, $term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $asu_username, 'ILIKE');
        $db->addWhere('confirmed', 0);
        $db->addWhere('term', $term);
        $db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        $db->addColumn('requestee');
        $result = $db->select('col');

        if(count($result) > 1) {
            // TODO: Log Weird Situation
        }

        if(!isset($result[0])){
            return null;
        }else{
            return $result[0];
        }
    }

    /**
     * Returns an array of requests in which the given user is requestee
     */
    public function get_pending_requests($asu_username,$term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestee', $asu_username, 'ILIKE');
        $db->addWhere('term', $term);
        $db->addWhere('confirmed', 0);
        $db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        $result = $db->getObjects('HMS_Roommate');

        return $result;
    }

    /**
     * Returns a count of pending requests
     */
    public function countPendingRequests($asu_username,$term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestee', $asu_username, 'ILIKE');
        $db->addWhere('confirmed', 0);
        $db->addWhere('term', $term);
        $db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        $result = $db->count();

        return $result;
    }

    /**
     * Gets all Roommate objects in which this user is involved
     */
    public function get_all_roommates($asu_username, $term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->addWhere('requestee', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->setGroupConj('grp', 'AND');
        $db->addWhere('term', $term);
        $result = $db->getObjects('HMS_Roommate');

        if(PHPWS_Error::logIfError($result))
            return FALSE;

        return $result;
    }

    /**
     * Removes all pending requests.  THIS DOES WORK SO BE CAREFUL.  Used when roommates are confirmed.
     * Logs each individual removal to cover our butts.
     */
    public function removeOutstandingRequests($asu_username, $term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestee', $asu_username, 'ILIKE', NULL, 'username_group');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'username_group');
        $db->setGroupConj('username_group', 'AND');
        
        $db->addWhere('confirmed', 0);
        $db->addWhere('term', $term);
        $requests = $db->getObjects('HMS_Roommate');

        if(PHPWS_Error::logIfError($requests)) {
            return FALSE;
        }

        if($requests == null)
            return TRUE;

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        foreach($requests as $request) {
            HMS_Activity_Log::log_activity($request->requestor, ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ, $_SESSION['asu_username'], "$request->requestee: Due to confirmed roommate");
            HMS_Activity_Log::log_activity($request->requestee, ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ, $_SESSION['asu_username'], "$request->requestor: Due to confirmed roommate");
            $request->delete();
        }

        return TRUE;
    }

    public function check_rlc_applications($a, $b, $term)
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        $result = HMS_RLC_Application::check_for_application($a, $term, FALSE);

        if(PHPWS_Error::isError($result)) {
            test($result,1);    // TODO: Break Cleanly
        }

        if($result == FALSE || $result == NULL)
            return TRUE;
        

        $resultb = HMS_RLC_Application::check_for_application($b, $term, FALSE);

        if($result == FALSE || $result == NULL)
            echo "roommate has not applied for an RLC";

        // Check to see if any of a's choices match any of b's choices
        if($result['rlc_first_choice_id']  == $resultb['rlc_first_choice_id'] ||
           $result['rlc_first_choice_id']  == $resultb['rlc_second_choice_id'] ||
           $result['rlc_first_choice_id']  == $resultb['rlc_third_choice_id'] ||
           $result['rlc_second_choice_id'] == $resultb['rlc_first_choice_id'] ||
           $result['rlc_second_choice_id'] == $resultb['rlc_second_choice_id'] ||
           $result['rlc_second_choice_id'] == $resultb['rlc_third_choice_id'] ||
           $result['rlc_third_choice_id']  == $resultb['rlc_first_choice_id'] ||
           $result['rlc_third_choice_id']  == $resultb['rlc_second_choice_id'] ||
           $result['rlc_third_choice_id']  == $resultb['rrlc_third_choice_id']){
            echo "applications match";
            return TRUE;
        }
    }

    public function check_rlc_assignments($a, $b, $term)
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Assignment.php');
        $resulta = HMS_RLC_Assignment::check_for_assignment($a, $term);

        $resultb = HMS_RLC_Assignment::check_for_assignment($b, $term);

        if($resulta !== FALSE || $resultb !== FALSE){
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Gets pager tags for the Student Main Menu page
     */
    public function get_requested_pager_tags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $name = HMS_SOAP::get_full_name($this->requestor);
        $tpl['NAME'] = PHPWS_Text::secureLink($name, 'hms', array('type'=>'student','op'=>'show_roommate_confirmation','id'=>$this->id, 'term'=>$this->term));
        $expires = floor(($this->calc_req_expiration_date() - mktime()) / 60 / 60);
        if($expires == 0) {
            $expires = floor(($this->calc_req_expiration_date() - mktime()) / 60);
            $tpl['EXPIRES'] = $expires . ' minute' . ($expires > 1 ? 's' : '');
        } else {
            $tpl['EXPIRES'] = $expires . ' hour' . ($expires > 1 ? 's' : '');
        }
        return $tpl;
    }

    /**
     * Checks to see if two people hypothetically could live together based on
     * our rules.
     *
     * @returns TRUE if so, FALSE if not
     *
     * @param requestor The person requesting a roommate
     * @param requestee The person requested as a roommate
     */
    public function can_live_together($requestor, $requestee, $term=null)
    {
        if(!isset($term) && isset($_SESSION['application_term'])){
            $term = $_SESSION['application_term'];
        }

        // This is always a good idea
        $requestor = strToLower($requestor);
        $requestee = strToLower($requestee);

        // Sanity Checking
        if(is_null($requestor)) {
            return E_ROOMMATE_MALFORMED_USERNAME;
        }

        if(is_null($requestee)) {
            return E_ROOMMATE_MALFORMED_USERNAME;
        }

        // Make sure requestor didn't request self
        if($requestor == $requestee) {
            return E_ROOMMATE_REQUESTED_SELF;
        }

        // Check if the requestor has a confirmed roommate
        if(HMS_Roommate::has_confirmed_roommate($requestor, $term)){
            return E_ROOMMATE_ALREADY_CONFIRMED;
        }

        // Check if the requestee has a confirmed roommate
        if(HMS_Roommate::has_confirmed_roommate($requestee, $term)){
            return E_ROOMMATE_REQUESTED_CONFIRMED;
        }

        // Make sure requestor and requestee are not requesting each other
        if(HMS_Roommate::have_requested_each_other($requestor, $requestee, $term)) {
            return E_ROOMMATE_ALREADY_REQUESTED;
        }

        // Make sure requestor does not have a pending roommate request
        if(HMS_Roommate::has_roommate_request($requestor,$term)) {
            return E_ROOMMATE_PENDING_REQUEST;
        }

        // Use SOAP for the rest of the checks
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $requestor_info = HMS_SOAP::get_student_info($requestor, $term);
        $requestee_info = HMS_SOAP::get_student_info($requestee, $term);

        // Make sure the requestee is actually a user
        if(empty($requestee_info->last_name)) {
            return E_ROOMMATE_USER_NOINFO;
        }

        // Make sure we have compatible genders
        if($requestor_info->gender != $requestee_info->gender) {
            return E_ROOMMATE_GENDER_MISMATCH;
        }

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        // Make sure the requestee has filled out an application
        if(HousingApplication::checkForApplication($requestee, $term) === FALSE) {
            return E_ROOMMATE_NO_APPLICATION;
        }

        // Students can only request a student of the same type
        if($requestor_info->student_type != $requestee_info->student_type){
            return E_ROOMMATE_TYPE_MISMATCH;
        }

        // If either student is assigned to an RLC, do not allow the request
        if(!HMS_Roommate::check_rlc_assignments($requestor, $requestee, $requestor_info->application_term)) {
            return E_ROOMMATE_RLC_ASSIGNMENT;
        }

        return E_SUCCESS;
    }

    /*
     * Performs all the checks necessary before allowing an administrator to
     * create a roommate pairing
     */
    public function canLiveTogetherAdmin(Student $roommate1, Student $roommate2, $term){
        
        # Sanity Checking
        if(is_null($roommate1)) {
            throw new RoommateException('Null student object for roommate 1.');
        }

        if(is_null($roommate2)) {
            throw new RoommateException('Null student object for roommate 1.');
        }

        # Check that the two user names aren't the same
        if($roommate1->getUsername() == $roommate2->getUsername()){
            throw new RoommateException('Roommate user names must be unique.');
        }
        
        # Use SOAP for the following checks
        # Make that both roommate have some sort of soap info
        $name = $roommate1->getLastName();
        if(empty($name)) {
            throw new RoommateException('No banner information for first roommate.');
        }

        $name = $roommate2->getLastName();
        if(empty($name)) {
            throw new RoommateException('No banner information for second roommate.');
        }
        
        # Make sure the genders match
        if($roommate1->getGender() != $roommate2->getGender()){
            throw new RoommateException('Roommate genders do not match.');
        }
        
        # Check if either has a confirmed roommate
        if(HMS_Roommate::has_confirmed_roommate($roommate1->getUsername(), $term)){
            throw new RoommateException('The first roommate already has a confirmed roommate.');
        }

        if(HMS_Roommate::has_confirmed_roommate($roommate2->getUsername(), $term)){
            throw new RoommateException('The second roommate already has a confirmed roommate.');
        }

        true;
    }

    /*******************
     * Utility Methods *
     *******************/

    /**
     * Calculates the date (in seconds since epoch) when a request made *now* will expire
     */
    public function calc_req_expiration_date()
    {
        return ($this->requested_on + ROOMMATE_REQ_TIMEOUT);
    }
    
    /*****************
     * Email Methods *
     *****************/
     
    public function send_emails() 
    {
        PHPWS_Core::initCoreClass('Mail.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        // set tags for the email to the person doing the requesting
        $message = "To:     " . HMS_SOAP::get_full_name($this->requestor) . "\n"; 
        $message .= "From:   Housing Management System\n\n";
        $message .= "This is a follow-up email to let you know you have requested " . HMS_SOAP::get_full_name($this->requestee) . " as your roommate.\n\n";
        $message .= "We have sent your requested roommate an email invitation to confirm his/her desire to be your roommate. Your requested ";
        $message .= "roommate must respond to this invitation within 72 hours or the invitation will expire. You will be notified ";
        $message .= "via email when your requested roommate either accepts or rejects the invitation.\n\n";
        $message .= "Please note that you can not reply to this email.\n";

        // create the Mail object and send it
        $requestor_mail = &new PHPWS_Mail;
        $requestor_mail->addSendTo($this->requestor . "@appstate.edu");
        $requestor_mail->setFrom('hms@tux.appstate.edu');
        $requestor_mail->setSubject('HMS Roommate Request');
        $requestor_mail->setMessageBody($message);
        $success = $requestor_mail->send();
        $success = true;
       
        if($success != TRUE) {
            return "There was an error emailing your requested roommate. Please contact Housing and Residence Life.";
        }

        $expire_date = $this->calc_req_expiration_date();

        // create the Mail object and send it
        $message = "To:     " . HMS_SOAP::get_full_name($this->requestee) . "\n";
        $message .= "From:  Housing Management System\n\n";
        $message .= "This email is to let you know " . HMS_SOAP::get_full_name($this->requestor) . " has requested you as a roommate.\n\n";
        $message .= "This request will expire on " . date('l, F jS, Y', $expire_date) . " at " . date('g:i A', $expire_date) . "\n\n";
        $message .= "You can accept or reject this invitation by logging into the Housing Management System.  Please log in and follow the directions under Step 5: Select A Roommate.\n\n";
        $message .= "Click the link below to access the Housing Management System:\n\n";
        $message .= "http://hms.appstate.edu/\n\n";
        $message .= "Please note that you can not reply to this email.\n";

        $requestee_mail = &new PHPWS_Mail;
        $requestee_mail->addSendTo($this->requestee . '@appstate.edu');
        $requestee_mail->setFrom('hms@tux.appstate.edu');
        $requestee_mail->setSubject('HMS Roommate Request');
        $requestee_mail->setMessageBody($message);
        $success = $requestee_mail->send();
        $success = true;

        if($success != TRUE) {
            return "There was an error emailing your requested roommate. Please contact Housing and Residence Life.";
        }

        return TRUE;
    }

    /**************
     * UI Methods *
     **************/

    public function show_request_roommate($error_message = NULL, $term = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');

        # If the term was passed in, then use it.... if not, then look in the request
        if(is_null($term)){
            $term = $_REQUEST['term'];
        }

        # Make sure the user doesn't already have a request out
        $result = HMS_Roommate::has_roommate_request($_SESSION['asu_username'],$term);
        if(PHPWS_Error::isError($result)) {
            $tpl['ERROR_MSG'] = 'There was an unexpected database error which has been reported to the administrators.  Please try again later.';
            // TODO: Log and Report
            return PHPWS_Template::process($tpl, 'hms', 'student/select_roommate.tpl');
        }
        if($result === TRUE){
            $tpl['ERROR_MSG'] = 'You have a pending roommate request. You can not request another roommate request until your current request is either denied or expires.';
            return PHPWS_Template::process($tpl, 'hms', 'student/select_roommate.tpl');
        }

        # Make sure the user doesn't already have a confirmed roommate
        $result = HMS_Roommate::has_confirmed_roommate($_SESSION['asu_username'], $term);
        if(PHPWS_Error::isError($result)) {

            $tpl['ERROR_MSG'] = 'There was an unexpected database error which has been reported to the administrators.  Please try again later.';
            // TODO: Log and Report
            return PHPWS_Template::process($tpl, 'hms', 'student/select_roommate.tpl');
        }
        if($result === TRUE) {
            $tpl['ERROR_MSG'] = 'You already have a roommate so you cannot make a roommate request.';
            return PHPWS_Template::process($tpl, 'hms', 'student/select_roommate.tpl');
        }
        
        $form = &new PHPWS_Form;

        $form->addText('username');
        
        $form->addHidden('term', $term);
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'request_roommate');
        $form->addSubmit('submit', _('Request Roommate'));
        
        $form->addButton('cancel', 'Cancel');
        $form->setExtra('cancel','onClick="document.location=\'index.php?module=hms&type=student&op=show_main_menu\'"');

        $tpl = $form->getTemplate();

        if(isset($error_message)){
            $tpl['ERROR_MSG'] = $error_message;
        }

        return PHPWS_Template::process($tpl, 'hms', 'student/select_roommate.tpl');
    }

    /**
     * Creates a new roommate request, doing all appropriate gender
     * checks and such to make sure they can actually room together.
     *
     * @param requestor The person requesting a roommate
     * @param requestee The person requested as a roommate
     */
    public function create_roommate_request($remove_rlc_app = FALSE, $term = NULL)
    {
        if(isset($_REQUEST['term'])){
            $term = $_REQUEST['term'];
        }else if(!isset($term) && isset($_SESSION['application_term'])){
            $term = $_SESSION['application_term'];
        }else if(!isset($term)){
            $term = HMS_SOAP::get_application_term($_SESSION['asu_username']);
        }

        if(empty($_REQUEST['username'])) {
            $error = "You did not enter a username.";
            return HMS_Roommate::show_select_roommate($error, $term);
        }
        if(!PHPWS_Text::isValidInput($_REQUEST['username'])) {
            $error = "You entered an invalid user name. Please use letters and numbers *only*.";
            return $error;
        }

        $requestor = $_SESSION['asu_username'];
        $requestee = strtolower(trim($_REQUEST['username']));

        if(!PHPWS_Text::isValidInput($requestee)) {
            return HMS_Roommate::show_request_roommate('Malformed Username.', $term);
        }

        // Did they say go ahead and trash the RLC application?
        if($remove_rlc_app) {
            PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
            $rlcapp = &new HMS_RLC_Application($requestor, $term);
            $rlcapp->delete();
        }

        // Attempt to Create Roommate Request
        $result = HMS_Roommate::can_live_together($requestor, $requestee, $term);

        if($result != E_SUCCESS) {
            // Pairing Error
            $msg = "";
            switch($result) {
                case E_ROOMMATE_MALFORMED_USERNAME:
                    $msg = "Malformed Username.";
                    break;
                case E_ROOMMATE_REQUESTED_SELF:
                    $msg = "You cannot request yourself.";
                    break;
                case E_ROOMMATE_ALREADY_CONFIRMED:
                    $msg = "You already have a confirmed roommate.";
                    break;
                case E_ROOMMATE_REQUESTED_CONFIRMED:
                    $msg = "The roommate you requested already has a confirmed roommate.";
                    break;
                case E_ROOMMATE_ALREADY_REQUESTED:
                    $msg = "You already have a pending request with $requestee.  Please <a href='index.php?module=hms&type=student&op=show_main_menu'>return to the main menu</a> and look under Step 5: Select A Roommate in order to confirm this request.";
                    break;
                case E_ROOMMATE_PENDING_REQUEST:
                    $msg = "You already have an uncomfirmed roommate request.";
                    break;
                case E_ROOMMATE_USER_NOINFO:
                    $msg = "Your requested roommate does not seem to have a student record.  Please be sure you typed the username correctly.";
                    break;
                case E_ROOMMATE_NO_APPLICATION:
                    $msg = "Your requested roommate has not filled out a housing application.";
                    break;
                case E_ROOMMATE_GENDER_MISMATCH:
                    $msg = "Please select a roommate of the same sex as yourself.";
                    break;
                case E_ROOMMATE_TYPE_MISMATCH:
                    $msg = "You can not choose a student of a different type than yourself (i.e. a freshmen student can only request another freshmen student, and not a transfer or continuing student).";
                    break;
                case E_ROOMMATE_RLC_ASSIGNMENT:
                    $msg = "Your roommate request could not be completed because you and/or your requested roommate are currently assigned to a Unique Housing Option.";
                    break;
                default:
                    $msg = "Unknown Error $result.";
                    // TODO: Log Weirdness
                    break;
            }
            return HMS_Roommate::show_request_roommate($msg, $term);
        }

        // Create request object and initialize
        $request = new HMS_Roommate();
        $result = $request->request($requestor,$requestee, $term);

        HMS_Activity_Log::log_activity($requestee,
                                       ACTIVITY_REQUESTED_AS_ROOMMATE,
                                       $requestor);
        if(!$result) {
            // TODO: Log and Notify
            $msg = "An unknown error has occurred.";
            return HMS_Roommate::show_request_roommate($msg, $term);
        }

        // Save the Roommate object
        $result = $request->save();

        if(!$result) {
            // TODO: Log and Notify
            $msg = "An unknown error has occurred.";
            return HMS_Roommate::show_request_roommate($msg, $term);
        }

        // Email both parties
        $result = $request->send_emails();
        if($result !== TRUE) {
            // TODO: Log and Notify
            $msg = "An unknown error has occurred.";
            return HMS_Roommate::show_request_roommate($msg, $term);
        }

        return HMS_Roommate::show_requested_confirmation();
    }

    /*
     * Shows a "you successfully requested ab1234" as your roommate" message
     */
    public function show_requested_confirmation()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $tpl['REQUESTED_ROOMMATE_NAME'] = HMS_SOAP::get_full_name($_REQUEST['username']);
        $tpl['MENU_LINK']               = PHPWS_Text::secureLink('Click here to return to the main menu.', 'hms', array('module'=>'hms', 'type'=>'student'));
        return PHPWS_Template::process($tpl, 'hms', 'student/select_roommate_confirmation.tpl');
    }

    /**
     * Shows the Approve/Reject Screen
     */
    public function show_approve_reject($request)
    {
        $accept_form = new PHPWS_Form;
        $accept_form->addHidden('module', 'hms');
        $accept_form->addHidden('type', 'student');
        $accept_form->addHidden('op', 'confirm_accept_roommate');
        $accept_form->addHidden('id', $request->id);
        $accept_form->addHidden('term', $request->term);
        $accept_form->addSubmit('Accept Roommate');

        $reject_form = new PHPWS_Form;
        $reject_form->addHidden('module', 'hms');
        $reject_form->addHidden('type', 'student');
        $reject_form->addHidden('op', 'confirm_reject_roommate');
        $reject_form->addHidden('id', $request->id);
        $reject_form->addHidden('term', $request->term);
        $reject_form->addSubmit('Reject Roommate');

        $cancel_form = new PHPWS_Form;
        $cancel_form->setMethod('get');
        $cancel_form->addHidden('module', 'hms');
        $cancel_form->addHidden('type', 'student');
        $cancel_form->addHidden('op', 'show_main_menu');
        $cancel_form->addSubmit('Cancel');

        // TODO: This thing needs to handle RLC Assignments, but it's broken right now so I'm not going to waste my time.

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $tpl['REQUESTOR_NAME'] = HMS_SOAP::get_full_name($request->requestor);

        $tpl['ACCEPT'] = PHPWS_Template::process($accept_form->getTemplate(), 'hms', 'student/roommate_accept_reject_form.tpl');
        $tpl['REJECT'] = PHPWS_Template::process($reject_form->getTemplate(), 'hms', 'student/roommate_accept_reject_form.tpl');
        $tpl['CANCEL'] = PHPWS_Template::process($cancel_form->getTemplate(), 'hms', 'student/roommate_accept_reject_form.tpl');

        return PHPWS_Template::process($tpl, 'hms', 'student/roommate_accept_reject_screen.tpl');
    }

    /**
     * Shows the Confirm Accept Screen, captcha and all
     */
    public function confirm_accept($request, $error = null, $term=null)
    {
        if(!isset($term) && isset($_SESSION['application_term'])){
            $term = $_SESSION['application_term'];
        } 

        PHPWS_Core::initCoreClass('Captcha.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $form = &new PHPWS_Form;
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'for_realz_accept_roommate');
        $form->addHidden('term', $request->term);
        $form->addHidden('id', $request->id);

        $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());
        $form->addTplTag('NAME', HMS_SOAP::get_full_name($request->requestor));

        if(!HMS_Roommate::check_rlc_applications($request->requestee, $request->requestor, $term))
            $form->addTplTag('RLC', 'ohno');

        if(!is_null($error)) {
            $form->addTplTag('ERROR', $error);
        }

        $form->addSubmit('Confirm');

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/roommate_accept_confirm.tpl');
    }

    /**
     * Verify the captcha, and if it's all good, mark the confirmed flag
     * + Should probably also remove any outstanding requests for either roommate, and log that this happened
     */
    public function accept_for_realz($request, $term)
    {
        if(!isset($term) && isset($_SESSION['application_term'])){
            $term = $_SESSION['application_term'];
        }

        PHPWS_Core::initCoreClass('Captcha.php');
        $verified = Captcha::verify(TRUE);
        if($verified === FALSE) {
            return HMS_Roommate::confirm_accept($request, 'Sorry, please try again.');
        }


        // If either student is assigned to an RLC, do not allow the request
        if(!HMS_Roommate::check_rlc_assignments($request->requestor, $request->requestee, $term)) {
            return HMS_Roommate::confirm_accept($request, 'Your roommate reqeust could not be confirmed because you and/or your roommate have been assigned to a Unique Housing Option.');
        }

        $request->confirmed = 1;
        $request->confirmed_on = mktime();
        $request->save();

        HMS_Activity_Log::log_activity($request->requestor,
                                       ACTIVITY_ACCEPTED_AS_ROOMMATE,
                                       $request->requestee,
                                       "CAPTCHA: $verified");

        // Remove any other requests for the requestor
        HMS_Roommate::remove_outstanding_requests($request->requestor, $request->term);

        // Remove any other requests for the requestee
        HMS_Roommate::remove_outstanding_requests($request->requestee, $request->term);

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $tpl['NAME']      = HMS_SOAP::get_full_name($request->requestor);
        $tpl['MENU_LINK'] = PHPWS_Text::secureLink('Click here to return to the main menu.', 'hms', array('module'=>'hms', 'type'=>'student'));
        return PHPWS_Template::process($tpl, 'hms', 'student/roommate_accept_done.tpl');
    }

    /**
     * Removes the request and tells the user that if it was an oops, go back and re-request, thank you.
     */
    public function reject_for_realz($request)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $tpl['NAME']      = HMS_SOAP::get_full_name($request->requestor);
        $tpl['USERNAME']  = $request->requestor;
        $tpl['MENU_LINK'] = PHPWS_Text::secureLink('Click here to return to the main menu.', 'hms', array('module'=>'hms', 'type'=>'student', 'op'=>'show_main_menu'));

        HMS_Activity_Log::log_activity($request->requestor,
                                       ACTIVITY_REJECTED_AS_ROOMMATE,
                                       $request->requestee);

        $request->delete();

        return PHPWS_Template::process($tpl, 'hms', 'student/roommate_reject_done.tpl');
    }

    public function delete_roommate_group()
    {
        if(!Current_User::allow('hms', 'roommate_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $roommate_group = new HMS_Roommate($_REQUEST['id']);

        # Save the user names for logging if all goes well
        $requestor = $roommate_group->requestor;
        $requestee = $roommate_group->requestee;

        # Attempt to actually delete the group
        $result = $roommate_group->delete();

        if(!$result){
            return HMS_Roommate::show_confirmed_roommates(NULL,'Error deleting group.');
        }else{
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($requestor, ACTIVITY_ADMIN_REMOVED_ROOMMATE, Current_User::getUsername(), $requestee);
            HMS_Activity_Log::log_activity($requestee, ACTIVITY_ADMIN_REMOVED_ROOMMATE, Current_User::getUsername(), $requestor);
            return HMS_Roommate::show_confirmed_roommates('Roommate group deleted.');
        }
    }

    public function show_confirmed_roommates($success = NULL, $error = NULL)
    {
        if(!Current_User::allow('hms', 'roommate_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        $tpl = array();

        $tpl['MENU_LINK']   = PHPWS_Text::secureLink('Back to Main Menu', 'hms', array('type'=>'maintenance', 'op'=>'show_maintenance_options'));
        $tpl['PAGER']       = HMS_Roommate::roommate_pager();
        $tpl['TITLE']      = 'Confrimed Roommates - ' . Term::toString(Term::getSelectedTerm(), TRUE);

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/show_confirmed_roommates.tpl');
    }

    /**
     * Shows a pager of roommate requests
     */
    public function display_requests($asu_username, $term)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('hms_roommate', 'HMS_Roommate');
        $pager->setModule('hms');
        $pager->setTemplate('student/requested_roommate_list.tpl');
        $pager->addRowTags('get_requested_pager_tags');
        $pager->db->addWhere('requestee', $asu_username, 'ILIKE');
        $pager->db->addWhere('confirmed', 0);
        $pager->db->addWhere('term', $term);
        $pager->db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        return $pager->get();
    }

    /**
     *
     */
    public function roommate_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('hms_roommate', 'HMS_Roommate');
        
        $pager->db->addWhere('confirmed', 1);
        $pager->db->addWhere('term', Term::getSelectedTerm());
        
        $pager->setModule('hms');
        $pager->setTemplate('admin/roommate_pager.tpl');
        $pager->addRowTags('get_roommate_pager_tags');
        $pager->setEmptyMessage('No roommate groups found.');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');

        # Setup searching on the requestor and requestee columns
        $pager->setSearch('requestor', 'requestee');

        return $pager->get();
    }

    public function get_roommate_pager_tags(){
        $tags = array();
        
        $term = Term::getSelectedTerm();
        
        $requestor = StudentFactory::getStudentByUsername($this->requestor,$term);
        $requestee = StudentFactory::getStudentByUsername($this->requestee,$term);
        
        $deleteCmd = CommandFactory::getCommand('DeleteRoommateGroup');
        $deleteCmd->setId($this->id);
        
        $tags['REQUESTOR']      = $requestor->getFullNameProfileLink();
        $tags['REQUESTEE']      = $requestee->getFullNameProfileLink();
        $tags['REQUESTED_ON']   = HMS_Util::get_long_date_time($this->requested_on);
        $tags['CONFIRMED_ON']   = HMS_Util::get_long_date_time($this->confirmed_on);
        $tags['ACTION']         = $deleteCmd->getLink('Delete');
        
        return $tags;
    }
}

?>
