<?php

/**
 * HMS Roommate class - Handles creating, confirming, and deleting roommate groups
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */
 
// The number of seconds before a roommate request expires, (hrs * 60 * 60) 
define('ROOMMATE_REQ_TIMEOUT', 259200); // 259200 = 72 hours

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
        }
    }

    public function request($requestor, $requestee)
    {
        if(HMS_Roommate::can_live_together($requestor, $requestee) != E_SUCCESS) {
            return false;
        }

        $this->term         = $_SESSION['application_term'];
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
            return false;
        }
        return true;

    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
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
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $term = HMS_Term::getSelectedTerm();
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
    public function have_requested_each_other($a, $b)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $a, 'ILIKE', 'AND', 'ab');
        $db->addWhere('requestee', $b, 'ILIKE', 'AND', 'ab');
        $db->addWhere('requestor', $b, 'ILIKE', 'AND', 'ba');
        $db->addWhere('requestee', $a, 'ILIKE', 'AND', 'ba');
        $db->addWhere('confirmed', 0, NULL, 'AND');
        $db->setGroupConj('ab', 'OR');
        $db->setGroupConj('ba', 'OR');
        $result = $db->count();

        if($result > 1) {
            // TODO: Log Weird Situation
        }

        return ($result > 0 ? TRUE : FALSE);
    }

    /* 
     * Returns TRUE if the student has a confirmed roommate, FALSE otherwise
     */ 
    public function has_confirmed_roommate($asu_username)
    {
        $db = new PHPWS_DB('hms_roommate');
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
    public function get_confirmed_roommate($asu_username, $term = NULL)
    {
        if(is_null($term)) PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->addWhere('requestee', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->setGroupConj('grp', 'AND');
        $db->addWhere('confirmed', 1);
        $db->addWhere('term', (is_null($term) ? HMS_Term::get_selected_term() : $term));
        $db->addColumn('requestor');
        $db->addColumn('requestee');
        $result = $db->select('row');

        if(count($result) > 1) {
            // TODO: Log Weird Situation
        }

        if(count($result) == 0)
            return null;

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
    public function has_roommate_request($username)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $username, 'ILIKE');
        $db->addWhere('confirmed', 0);
        $db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        $result = $db->count();

        if(PHPWS_Error::logIfError($result))
            return $result;

        return ($result > 0 ? TRUE : FALSE);
    }

    /**
     * Returns the asu username of the student which the given user has requested, or NULL
     * if either the user has not requested anyone or the pairing is confirmed.
     */
    public function get_unconfirmed_roommate($asu_username)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $asu_username, 'ILIKE');
        $db->addWhere('confirmed', 0);
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
    public function get_pending_requests($asu_username)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestee', $asu_username, 'ILIKE');
        $db->addWhere('confirmed', 0);
        $db->addWhere('requested_on', mktime() - ROOMMATE_REQ_TIMEOUT, '>=');
        $result = $db->getObjects('HMS_Roommate');

        return $result;
    }

    /**
     * Returns a count of pending requests
     */
    public function count_pending_requests($asu_username)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestee', $asu_username, 'ILIKE');
        $db->addWhere('confirmed', 0);
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
    public function remove_outstanding_requests($asu_username)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestee', $asu_username, 'ILIKE', NULL, 'username_group');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'username_group');
        $db->setGroupConj('username_group', 'AND');
        
        $db->addWhere('confirmed', 0);
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
        $result = HMS_RLC_Assignment::check_for_assignment($a, $term);

        if(PHPWS_Error::isError($result)) {
            test($result,1);    // TODO: Break Cleanly
        }

        if($result == FALSE)
            return TRUE;

        $resultb = HMS_RLC_Assignment::check_for_assignment($b, $term);

        if($result == FALSE)
            return FALSE;

        return $result['rlc_id'] == $resultb['rlc_id'];
    }

    /**
     * In the spring, FT can request C.  In the fall, not so much.
     */

    /*
     * Commented out since housing chnaged their minds about freshmen/transfers/continuing students requesting eachother
     *
    public function can_ft_and_c_live_together_this_term($requestor, $requestee)
    {
        $a_type = $requestor->student_type;
        $b_type = $requestee->student_type;

        // If there are no continuing students involved, we're good
        if(($a_type == TYPE_FRESHMEN ||
            $a_type == TYPE_TRANSFER) &&
           ($b_type == TYPE_FRESHMEN ||
            $b_type == TYPE_TRANSFER))
            return TRUE;

        $term = substr($_SESSION['application_term'], 4, 2);

        // This is acceptable in the spring
        if($term == TERM_SPRING && $b_type == TYPE_CONTINUING)
            return TRUE;

        // Any other time or types and we have a problem.
        return FALSE;
    }

    */
    
    /**
     * Gets pager tags for the Student Main Menu page
     */
    public function get_requested_pager_tags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $name = HMS_SOAP::get_full_name($this->requestor);
        $tpl['NAME'] = PHPWS_Text::secureLink($name, 'hms', array('type'=>'student','op'=>'show_roommate_confirmation','id'=>$this->id));
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
    public function can_live_together($requestor, $requestee)
    {
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
        if(HMS_Roommate::has_confirmed_roommate($requestor)){
            return E_ROOMMATE_ALREADY_CONFIRMED;
        }

        // Check if the requestee has a confirmed roommate
        if(HMS_Roommate::has_confirmed_roommate($requestee)){
            return E_ROOMMATE_REQUESTED_CONFIRMED;
        }

        // Make sure requestor and requestee are not requesting each other
        if(HMS_Roommate::have_requested_each_other($requestor, $requestee)) {
            return E_ROOMMATE_ALREADY_REQUESTED;
        }

        // Make sure requestor does not have a pending roommate request
        if(HMS_Roommate::has_roommate_request($requestor)) {
            return E_ROOMMATE_PENDING_REQUEST;
        }

        // Use SOAP for the rest of the checks
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $requestor_info = HMS_SOAP::get_student_info($requestor, $_SESSION['application_term']);
        $requestee_info = HMS_SOAP::get_student_info($requestee, $_SESSION['application_term']);

        // Make sure the requestee is actually a user
        if(empty($requestee_info->last_name)) {
            return E_ROOMMATE_USER_NOINFO;
        }

        // Make sure we have compatible genders
        if($requestor_info->gender != $requestee_info->gender) {
            return E_ROOMMATE_GENDER_MISMATCH;
        }

        PHPWS_Core::initModClass('hms', 'HMS_Application.php');
        // Make sure the requestee has filled out an application
        if(HMS_Application::check_for_application($requestee, $_SESSION['application_term']) === false) {
            return E_ROOMMATE_NO_APPLICATION;
        }

        /*
         * Commented out since housing changed their minds
         *
        // Depending on term, freshmen and continuing may or may not be able to live
        // together... so this public function makes sure of that.
        if(!HMS_Roommate::can_ft_and_c_live_together_this_term($requestor_info, $requestee_info)) {
            return E_ROOMMATE_TYPE_MISMATCH;
        }
        */

        // Students can only request a student of the same type
        if($requestor_info->student_type != $requestee_info->student_type){
            return E_ROOMMATE_TYPE_MISMATCH;
        }

        // If requestor is assigned to a different RLC, STOP and call HRL
        if(!HMS_Roommate::check_rlc_assignments($requestor, $requestee, $requestor_info->application_term)) {
            return E_ROOMMATE_RLC_ASSIGNMENT;
        }

        // If requestor applied to a different RLC, ask to remove application
        if(!HMS_Roommate::check_rlc_applications($requestor, $requestee, $requestor_info->application_term)) {
            return E_ROOMMATE_RLC_APPLICATION;
        }

        return E_SUCCESS;
    }

    /*
     * Performs all the checks necessary before allowing an administrator to
     * create a roommate pairing
     */
    public function can_live_together_admin($roommate_1, $roommate_2){
        
        # This is always a good idea
        $requestor = strToLower($roommate_1);
        $requestee = strToLower($roommate_2);

        # Sanity Checking
        if(is_null($roommate_1)) {
            return E_ROOMMATE_MALFORMED_USERNAME;
        }

        if(is_null($roommate_2)) {
            return E_ROOMMATE_MALFORMED_USERNAME;
        }

        # Check that the two user names aren't the same
        if($roommate_1 == $roommate_2){
            return E_ROOMMATE_REQUESTED_SELF;
        }
        
        # Use SOAP for the following checks
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $roommate_1_info = HMS_SOAP::get_student_info($roommate_1, HMS_Term::get_selected_term());
        $roommate_2_info = HMS_SOAP::get_student_info($roommate_2, HMS_Term::get_selected_term());

        # Make that both roommate have some sort of soap info
        if(empty($roommate_1_info->last_name)) {
            return E_ROOMMATE_USER_NOINFO;
        }

        if(empty($roommate_2_info->last_name)){
            return E_ROOMMATE_USER_NOINFO;
        }
        
        # Make sure the genders match
        if($roommate_1_info->gender != $roommate_2_info->gender){
            return E_ROOMMATE_GENDER_MISMATCH;
        }
        
        # Check if either has a confirmed roommate
        if(HMS_Roommate::has_confirmed_roommate($roommate_1)){
            return E_ROOMMATE_ALREADY_CONFIRMED;
        }

        if(HMS_Roommate::has_confirmed_roommate($roommate_2)){
            return E_ROOMMATE_REQUESTED_CONFIRMED;
        }

        return E_SUCCESS;
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

    public function show_request_roommate($error_message = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');

        # Make sure the user doesn't already have a request out
        $result = HMS_Roommate::has_roommate_request($_SESSION['asu_username']);
        if(PHPWS_Error::isError($result)) {
            $tpl['ERROR_MSG'] = 'There was an unexpected database error which has been reported to the administrators.  Please try again later.';
            // TODO: Log and Report
            return PHPWS_Template::process($tpl, 'hms', 'student/select_roommate.tpl');
        }
        if($result === TRUE){
            $tpl['ERROR_MSG'] = 'You have a pending roommate request. You can not request another roommate request until your current request is either denied or expires.';
            return PHPWS_Template::process($tpl, 'hms', 'student/select_roommate.tpl');
        }

        # Make sur ethe user doesn't already have a confirmed roommate
        $result = HMS_Roommate::has_confirmed_roommate($_SESSION['asu_username']);
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
    public function create_roommate_request($remove_rlc_app = FALSE)
    {
        if(empty($_REQUEST['username'])) {
            $error = "You did not enter a username.";
            return HMS_Roommate::show_select_roommate($error);
        }
        if(!PHPWS_Text::isValidInput($_REQUEST['username'])) {
            $error = "You entered an invalid user name. Please use letters and numbers *only*.";
            return $error;
        }

        $requestor = $_SESSION['asu_username'];
        $requestee = strtolower(trim($_REQUEST['username']));

        if(!PHPWS_Text::isValidInput($requestee)) {
            return HMS_Roommate::show_request_roommate('Malformed Username.');
        }

        // Did they say go ahead and trash the RLC application?
        if($remove_rlc_app) {
            PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
            $rlcapp = &new HMS_RLC_Application($requestor, $_SESSION['application_term']);
            $rlcapp->delete();
        }

        // Attempt to Create Roommate Request
        $result = HMS_Roommate::can_live_together($requestor, $requestee);

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
                    $msg = "You are currently assigned to a different Unique Housing Option than your requested roommate.  Please contact Housing and Residence Life if you would like to be removed from your Unique Housing Option.";
                    break;
                case E_ROOMMATE_RLC_APPLICATION:
                    return HMS_Roommate::requestor_handle_rlc_application($requestor, $requestee);
                default:
                    $msg = "Unknown Error $result.";
                    // TODO: Log Weirdness
                    break;
            }
            return HMS_Roommate::show_request_roommate($msg);
        }

        // Create request object and initialize
        $request = &new HMS_Roommate();
        $result = $request->request($requestor,$requestee);

        HMS_Activity_Log::log_activity($requestee,
                                       ACTIVITY_REQUESTED_AS_ROOMMATE,
                                       $requestor);
        if(!$result) {
            // TODO: Log and Notify
            $msg = "An unknown error has occurred.";
            return HMS_Roommate::show_request_roommate($msg);
        }

        // Save the Roommate object
        $result = $request->save();

        if(!$result) {
            // TODO: Log and Notify
            $msg = "An unknown error has occurred.";
            return HMS_Roommate::show_request_roommate($msg);
        }

        // Email both parties
        $result = $request->send_emails();
        if($result !== TRUE) {
            // TODO: Log and Notify
            $msg = "An unknown error has occurred.";
            return HMS_Roommate::show_request_roommate($msg);
        }

        return HMS_Roommate::show_requested_confirmation();
    }

    /**
     * Handle a requestor that has an RLC Application problem.
     */
    public function requestor_handle_rlc_application($requestor, $requestee)
    {
        $form = &new PHPWS_Form;
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'roommate_confirm_rlc_removal');
        $form->addHidden('username', $requestee);

        $form->addSubmit('submit', 'Withdraw Unique Housing Options Application');

        $form->addButton('cancel', 'Cancel');
        $form->setExtra('cancel','onClick="document.location=\'index.php?module=hms&type=student&op=show_main_menu\'"');

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/requestor_handle_rlc_application.tpl');
    }

    /*
     * Shows a "you successfully requested ab1234" as your roommate" message
     */
    public function show_requested_confirmation()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $tpl['REQUESTED_ROOMMATE_NAME'] = HMS_SOAP::get_full_name($_REQUEST['username']);
        $tpl['MENU_LINK']               = PHPWS_Text::secureLink('Click here to return to the main menu.', 'hms', array('module'=>'hms', 'type'=>'student', 'op'=>'show_main_menu'));
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
        $accept_form->addSubmit('Accept Roommate');

        $reject_form = new PHPWS_Form;
        $reject_form->addHidden('module', 'hms');
        $reject_form->addHidden('type', 'student');
        $reject_form->addHidden('op', 'confirm_reject_roommate');
        $reject_form->addHidden('id', $request->id);
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
    public function confirm_accept($request, $error = null)
    {
        PHPWS_Core::initCoreClass('Captcha.php');
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $form = &new PHPWS_Form;
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'student');
        $form->addHidden('op', 'for_realz_accept_roommate');
        $form->addHidden('id', $request->id);

        $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());
        $form->addTplTag('NAME', HMS_SOAP::get_full_name($request->requestor));

        if(!HMS_Roommate::check_rlc_applications($request->requestee, $request->requestor, $_SESSION['application_term']))
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
    public function accept_for_realz($request)
    {
        PHPWS_Core::initCoreClass('Captcha.php');
        $verified = Captcha::verify(true);
        if($verified === false) {
            return HMS_Roommate::confirm_accept($request, 'Sorry, please try again.');
        }

        HMS_Activity_Log::log_activity($request->requestor,
                                       ACTIVITY_ACCEPTED_AS_ROOMMATE,
                                       $request->requestee,
                                       "CAPTCHA: {$verified}");
        
        $request->confirmed = 1;
        $request->confirmed_on = mktime();
        $request->save();

        // Remove any other requests for the requestor
        HMS_Roommate::remove_outstanding_requests($request->requestor);

        // Remove any other requests for the requestee
        HMS_Roommate::remove_outstanding_requests($request->requestee);

        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        // If they got this far they already agreed to dump an RLC application
        if(!HMS_Roommate::check_rlc_applications($request->requestee, $request->requestor, $_SESSION['application_term'])) {
            $rlcapp = &new HMS_RLC_Application($request->requestee, $_SESSION['application_term']);
            $rlcapp->delete();
        }

        $tpl['NAME']      = HMS_SOAP::get_full_name($request->requestor);
        $tpl['MENU_LINK'] = PHPWS_Text::secureLink('Click here to return to the main menu.', 'hms', array('module'=>'hms', 'type'=>'student', 'op'=>'show_main_menu'));
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

    /**
     * Shows the UI for administratively creating a roommate group
     */
    public function show_admin_create_roommate_group($success = NULL, $error = NULL)
    {
        if(!Current_User::allow('hms', 'roommate_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        
        $tpl = array();

        $tpl['TERM'] = HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);

        $form = &new PHPWS_Form('roommate_group');
        
        if(isset($_REQUEST['roommate_1']) && isset($error)){
            $form->addText('roommate_1', $_REQUEST['roommate_1']);
        }else{
            $form->addText('roommate_1');
        }
        if(isset($_REQUEST['roommate_2']) && isset($error)){
            $form->addText('roommate_2', $_REQUEST['roommate_2']);
        }else{
            $form->addText('roommate_2');
        }

        $form->addSubmit('submit', 'Create Group');

        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'roommate');
        $form->addHidden('op', 'show_admin_create_roommate_group_result');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }

        if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $tpl['MENU_LINK'] = PHPWS_Text::secureLink('Back to Main Menu', 'hms', array('type'=>'maintenance', 'op'=>'show_maintenance_options'));

        return PHPWS_Template::process($tpl, 'hms', 'admin/create_roommate_group.tpl');
    }

    /**
     * Handles administrately creating roommate groups
     */
    public function show_admin_create_roommate_group_result()
    {
        if(!Current_User::allow('hms', 'roommate_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        # Check for reasonable input
        if(empty($_REQUEST['roommate_1']) || empty($_REQUEST['roommate_2'])){
            return HMS_Roommate::show_admin_create_roommate_group(null, 'Error: Please enter two user names.');
        }

        # Trim/lowercase and store locally
        $roommate_1 = trim(strtolower($_REQUEST['roommate_1']));
        $roommate_2 = trim(strtolower($_REQUEST['roommate_2']));

        # Check if these two can live together
        $result = HMS_Roommate::can_live_together_admin($roommate_1, $roommate_2);
        if($result != E_SUCCESS){
            switch($result){
                case E_ROOMMATE_MALFORMED_USERNAME:
                    $msg = 'Invalid user name.';
                    break;
                case E_ROOMMATE_REQUESTED_SELF:
                    $msg = 'User names cannot match.';
                    break;
                case E_ROOMMATE_USER_NOINFO:
                    $msg = 'No banner info for student.';
                    break;
                case E_ROOMMATE_GENDER_MISMATCH:
                    $msg = 'Gender mis-match';
                    break;
                case E_ROOMMATE_ALREADY_CONFIRMED:
                    $msg = "$roommate_1 already has a confirmed roommate";
                    break;
                case E_ROOMMATE_REQUESTED_CONFIRMED:
                    $msg = "$roommate_2 already has a confirmed roommate";
                    break;
                default:
                    $msg = "Unknown error: {$result}.";
                    break;
            }

            return HMS_Roommate::show_admin_create_roommate_group(NULL, $msg);
        }

        $more = "";

        # Check for pending requests for either roommate and break them
        if(HMS_Roommate::count_pending_requests($roommate_1) > 0){
            $more .= " Warning: Pending roommate requests for $roommate_1 were deleted.";
        }
        $result = HMS_Roommate::remove_outstanding_requests($roommate_1);
        if(!$result){
            return HMS_Roommate::show_admin_create_roommate_group(NULL, "Error removing pending requests for $roommate_1, roommate group was not created.");
        }

        if(HMS_Roommate::count_pending_requests($roommate_2) > 0){
            $more .= " Warning: Pending roommate requests for $roommate_2 were deleted.";
        }
        $result = HMS_Roommate::remove_outstanding_requests($roommate_2);
        if(!$result){
            return HMS_Roommate::show_admin_create_roommate_group(NULL, "Error removing pending requests for $roommate_2, roommate group was not created.");
        }

        # Create the roommate group and save it
        $roommate_group                 = &new HMS_Roommate();
        $roommate_group->term           = HMS_Term::get_selected_term();
        $roommate_group->requestor      = $roommate_1;
        $roommate_group->requestee      = $roommate_2;
        $roommate_group->confirmed      = 1;
        $roommate_group->requested_on   = mktime();
        $roommate_group->confirmed_on   = mktime();
        
        $result = $roommate_group->save();

        if(!$result){
            return HMS_Roommate::show_admin_create_roommate_group(NULL, 'Error save roommate group. The group was not created.' . $more);
        }else{
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($roommate_1, ACTIVITY_ADMIN_ASSIGNED_ROOMMATE, Current_User::getUsername(), $roommate_2);
            HMS_Activity_Log::log_activity($roommate_2, ACTIVITY_ADMIN_ASSIGNED_ROOMMATE, Current_User::getUsername(), $roommate_1);
            return HMS_Roommate::show_admin_create_roommate_group('Roommate group created.' . $more);
        }
    }

    public function delete_roommate_group()
    {
        if(!Current_User::allow('hms', 'roommate_maintenance')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $roommate_group = &new HMS_Roommate($_REQUEST['id']);

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
        $tpl['TITLE']      = 'Confrimed Roommates - ' . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);

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
    public function display_requests($asu_username)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('hms_roommate', 'HMS_Roommate');
        $pager->setModule('hms');
        $pager->setTemplate('student/requested_roommate_list.tpl');
        $pager->addRowTags('get_requested_pager_tags');
        $pager->db->addWhere('requestee', $asu_username, 'ILIKE');
        $pager->db->addWhere('confirmed', 0);
        return $pager->get();
    }

    /**
     *
     */
    public function roommate_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $pager = new DBPager('hms_roommate', 'HMS_Roommate');
        
        $pager->db->addWhere('confirmed', 1);
        $pager->db->addWhere('term', HMS_Term::get_selected_term());
        
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
        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
        
        $tags = array();
        
        $tags['REQUESTOR']      = HMS_Student::get_link($this->requestor, TRUE);
        $tags['REQUESTEE']      = HMS_Student::get_link($this->requestee, TRUE);
        $tags['REQUESTED_ON']   = HMS_Util::get_long_date_time($this->requested_on);
        $tags['CONFIRMED_ON']   = HMS_Util::get_long_date_time($this->confirmed_on);
        $tags['ACTION']         = PHPWS_Text::secureLink('Delete', 'hms', array('type'=>'roommate', 'op'=>'delete_roommate_group', 'id'=>$this->id));
        
        return $tags;
    }
}

?>
