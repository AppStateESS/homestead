<?php

/**
 * HMS Roommate class - Handles creating, confirming, and deleting roommate groups
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

// The number of seconds before a roommate request expires, (hrs * 60 * 60)
define('ROOMMATE_REQ_TIMEOUT', 259200); // 259200 = 72 hours

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
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
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        if($result === FALSE) {
            $this->id = 0;
        }
    }

    public function request($requestor, $requestee, $term)
    {
        $this->term         = $term;
        $this->requestor    = strToLower($requestor);
        $this->requestee    = strToLower($requestee);
        $this->confirmed    = 0;
        $this->requested_on = mktime();

        $result = $this->is_request_valid();
        if($result != E_SUCCESS) {
            PHPWS_Core::initModClass('hms', 'exception/RoommateCompatibilityException.php');
            throw new RoommateCompatibilityException($result);
        }

        return true;
    }

    public function confirm()
    {
        $result = $this->can_live_together();
        if($result != E_SUCCESS) {
            PHPWS_Core::initModClass('hms', 'exception/RoommateCompatibilityException.php');
            throw new RoommateCompatibilityException($result);
        }

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
        } else if(trim($this->requestee) == trim($username)) {
            return $this->requestor;
        }

        throw new RoommateException("$username is not in roommate pairing " . $this->id);
    }

    /******************
     * Static Methods *
     ******************/

    public static function getByUsernames($a, $b, $term)
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('term', $term);
        $db->addWhere('requestor', $a, 'ILIKE', 'AND', 'ab');
        $db->addWhere('requestee', $b, 'ILIKE', 'AND', 'ab');
        $db->addWhere('requestor', $b, 'ILIKE', 'AND', 'ba');
        $db->addWhere('requestee', $a, 'ILIKE', 'AND', 'ba');
        $db->setGroupConj('ab', 'AND');
        $db->setGroupConj('ba', 'OR');

        $db->groupIn('ab', 'ba');

        $roommate = new HMS_Roommate();
        $result = $db->loadObject($roommate);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $roommate;
    }

    public function get_all_confirmed_roommates($term = NULL, $random = FALSE)
    {
        if(is_null($term)) {
            $term = Term::getSelectedTerm();
        }

        $db = new PHPWS_DB('hms_roommate');
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

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException('Unexpected error in has_roommate_request');
        }

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

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'DatabaseException.php');
            throw new DatabaseException("Could not select confirmed roommate for $asu_username $term");
        }

        if(count($result) > 1) {
            // TODO: Log Weird Situation
        }

        if(count($result) == 0){
            return null;
        }

        if(trim($result['requestor']) == trim($asu_username)) {
            return StudentFactory::getStudentByUsername($result['requestee'], $term);
        }

        return StudentFactory::getStudentByUsername($result['requestor'], $term);
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

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException('Unexpected error in has_roommate_request');
        }

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
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException('Could not remove outstanding requests');
        }

        if($requests == null)
        return TRUE;

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        foreach($requests as $request) {
            HMS_Activity_Log::log_activity($request->requestor, ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ, UserStatus::getUsername(), "$request->requestee: Due to confirmed roommate");
            HMS_Activity_Log::log_activity($request->requestee, ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ, UserStatus::getUsername(), "$request->requestor: Due to confirmed roommate");
            $request->delete();
        }

        return TRUE;
    }

    // Depricated per ticket #530
    public function check_rlc_applications()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        $result  = HMS_RLC_Application::checkForApplication($this->requestor, $this->term, FALSE);
        $resultb = HMS_RLC_Application::checkForApplication($this->requestee, $this->term, FALSE);

        if($result === FALSE && $resultb === FALSE)
        return TRUE;

        if($result === FALSE || $resultb === FALSE)
        return FALSE;

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
            return TRUE;
        }

        return FALSE;
    }

    // Depricated per ticket #530
    public function check_rlc_assignments()
    {
        PHPWS_Core::initModClass('hms','HMS_RLC_Assignment.php');
        $resulta = HMS_RLC_Assignment::checkForAssignment($this->requestor, $this->term);
        $resultb = HMS_RLC_Assignment::checkForAssignment($this->requestee, $this->term);

        if($resulta === FALSE && $resultb === FALSE) {
            return TRUE;
        }

        if($resulta !== FALSE && $resultb !== FALSE) {
            if($resulta['rlc_id'] == $resultb['rlc_id']) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Gets pager tags for the Student Main Menu page
     */
    public function get_requested_pager_tags()
    {
        $requestor = StudentFactory::getStudentByUsername($this->requestor, $this->term);
        $name = $requestor->getFullName();

        // TODO: COMMAND PATTERN
        $cmd = CommandFactory::getCommand('ShowRoommateConfirmation');
        $cmd->setRoommateId($this->id);
        $tpl['NAME'] = $cmd->getLink($name);

        $expires = floor(($this->calc_req_expiration_date() - mktime()) / 60 / 60);
        if($expires == 0) {
            $expires = floor(($this->calc_req_expiration_date() - mktime()) / 60);
            $tpl['EXPIRES'] = $expires . ' minute' . ($expires > 1 ? 's' : '');
        } else {
            $tpl['EXPIRES'] = $expires . ' hour' . ($expires > 1 ? 's' : '');
        }
        return $tpl;
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

    /**
     * Checks to see if two people hypothetically could live together based on
     * our rules.
     *
     * @returns TRUE if so, FALSE if not
     *
     * @param requestor The person requesting a roommate
     * @param requestee The person requested as a roommate
     */
    public function is_request_valid()
    {
        $requestor = strToLower($this->requestor);
        $requestee = strToLower($this->requestee);
        $term = $this->term;

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

        // Make sure requestor and requestee are not requesting each other
        if(HMS_Roommate::have_requested_each_other($requestor, $requestee, $term)) {
            return E_ROOMMATE_ALREADY_REQUESTED;
        }

        // Make sure requestor does not have a pending roommate request
        if(HMS_Roommate::has_roommate_request($requestor,$term)) {
            return E_ROOMMATE_PENDING_REQUEST;
        }

        return $this->can_live_together();
    }

    function can_live_together()
    {
        $requestor = strToLower($this->requestor);
        $requestee = strToLower($this->requestee);
        $term = $this->term;

        // Check if the requestor has a confirmed roommate
        if(HMS_Roommate::has_confirmed_roommate($requestor, $term)){
            return E_ROOMMATE_ALREADY_CONFIRMED;
        }

        // Check if the requestee has a confirmed roommate
        if(HMS_Roommate::has_confirmed_roommate($requestee, $term)){
            return E_ROOMMATE_REQUESTED_CONFIRMED;
        }

        // Use SOAP for the rest of the checks
        $requestor_info = StudentFactory::getStudentByUsername($requestor, $term);

        // Make sure the requestee is actually a user
        try {
            $requestee_info = StudentFactory::getStudentByUsername($requestee, $term);
        } catch(StudentNotFoundException $snfe) {
            return E_ROOMMATE_USER_NOINFO;
        }

        // Make sure we have compatible genders
        if($requestor_info->getGender() != $requestee_info->getGender()) {
            return E_ROOMMATE_GENDER_MISMATCH;
        }

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        // Make sure the requestee has filled out an application
        if(HousingApplication::checkForApplication($requestee, $term) === FALSE) {
            return E_ROOMMATE_NO_APPLICATION;
        }

        // Students can only request a student of the same type
        if($requestor_info->getType() != $requestee_info->getType()){
            return E_ROOMMATE_TYPE_MISMATCH;
        }

        /*
         // Make sure RLC Applications are compatible
         if(!$this->check_rlc_applications()) {
         return E_ROOMMATE_RLC_APPLICATION;
         }

         // If either student is assigned to an RLC, do not allow the request
         if(!$this->check_rlc_assignments()) {
         return E_ROOMMATE_RLC_ASSIGNMENT;
         }
         */

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
     
    //TODO move email messages below into templates of their own and use HMS_Email class

    public function send_request_emails()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $requestorStudent = StudentFactory::getStudentByUsername($this->requestor, $this->term);
        $requesteeStudent = StudentFactory::getStudentByUsername($this->requestee, $this->term);

        // set tags for the email to the person doing the requesting
        $message  = "To:     " . $requestorStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= "This is a follow-up email to let you know you have requested " . $requesteeStudent->getFullName() . " as your roommate.\n\n";
        $message .= "We have sent your requested roommate an email invitation to confirm his/her desire to be your roommate. Your requested ";
        $message .= "roommate must respond to this invitation within 72 hours or the invitation will expire. You will be notified ";
        $message .= "via email when your requested roommate either accepts or rejects the invitation.\n\n";
        $message .= "Please note that you can not reply to this email.\n";

        // create the Mail object and send it
        $success = HMS_Email::send_email($this->requestor . '@appstate.edu', NULL, 'HMS Roommate Request', $message);
         
        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the requestor ' . $this->requestor . ' of a roommate request for requestee ' . $this->requestee . ', HMS_Roommate ' . $this->id);
        }

        $expire_date = $this->calc_req_expiration_date();

        // create the Mail object and send it
        $message  = "To:    " . $requesteeStudent->getFullName() . "\n";
        $message .= "From:  Housing Management System\n\n";
        $message .= "This email is to let you know " . $requestorStudent->getFullName() . " has requested you as a roommate.\n\n";
        $message .= "This request will expire on " . date('l, F jS, Y', $expire_date) . " at " . date('g:i A', $expire_date) . "\n\n";
        $message .= "You can accept or reject this invitation by logging into the Housing Management System.  Please log in and follow the directions under Roommate Selection.\n\n";
        $message .= "Click the link below to access the Housing Management System:\n\n";
        $message .= "http://hms.appstate.edu/\n\n";
        $message .= "Please note that you can not reply to this email.\n";

        $success = HMS_Email::send_email($this->requestee . '@appstate.edu', NULL, 'HMS Roommate Request', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred notifying the requestee ' . $this->requestee . ' of a roommate request from requestor ' . $this->requestor . ', HMS_Roommate ' . $this->id);
        }

        return TRUE;
    }

    public function send_confirm_emails()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $requestorStudent = StudentFactory::getStudentByUsername($this->requestor, $this->term);
        $requesteeStudent = StudentFactory::getStudentByUsername($this->requestee, $this->term);

        // to the requestor
        $message  = "To:     " . $requestorStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= "Congratulations!  " . $requesteeStudent->getFullName() . " has accepted your roommate request.\n\n";
        $message .= "Please do not reply to this email.  If there is a problem, please contact the Housing Assignments office ";
        $message .= "at 828-262-6111, or by emailing housing@appstate.edu.\n";

        $success = HMS_Email::send_email($this->requestor . '@appstate.edu', NULL, 'HMS Roommate Confirmed', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the requestor ' . $this->requestor . ' of a roommate confirmation from requestee ' . $this->requestee . ', HMS_Roommate ' . $this->id);
        }

        // to the requestee
        $message  = "To:     " . $requesteeStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= "This is a follow-up email to notify that you have accepted the roommate request from " . $requestorStudent->getFullName() . ".\n\n";
        $message .= "Please do not reply to this email.  If there is a problem, please contact the Housing Assignments office ";
        $message .= "at 828-262-6111, or by emailing housing@appstate.edu.\n";

        $success = HMS_Email::send_email($this->requestee . '@appstate.edu', NULL, 'HMS Roommate Confirmed', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the requestee ' . $this->requestee . ' of a roommate confirmation for requestor ' . $this->requestor . ', HMS_Roommate ' . $this->id);
        }

        return TRUE;
    }

    public function send_break_emails($breakor)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $breakee = $this->get_other_guy($breakor);

        $breakorStudent = StudentFactory::getStudentByUsername($breakor, $this->term);
        $breakeeStudent = Studentfactory::getStudentByUsername($breakee, $this->term);

        // to the breakor
        $message  = "To:     " . $breakorStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= "This is a follow-up email to notify you that you have broken your roommate pairing with " . $breakeeStudent->getFullName() . ".\n\n";
        $message .= "If this was in error, you may go through the request process again by logging into the Housing Management System at ";
        $message .= "http://hms.appstate.edu\n\n";
        $message .= "Please do not reply to this email.  If there is a problem, please contact the Housing Assignments office ";
        $message .= "at 828-262-6111, or by emailing housing@appstate.edu.\n";

        $success = HMS_Email::send_email($breakor . '@appstate.edu', NULL, 'HMS Roommate Pairing Broken', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the breakor ' . $breakor . ' of their broken roommate pairing with ' . $breakee . ', HMS_Roommate ' . $this->id);
        }

        // to the breakee
        $message  = "To:     " . $breakeeStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= "We're sorry, but " . $breakorStudent->getFullName() . " has broken your roommate pairing.  Please contact ";
        $message .= $breakorStudent->getFirstName() . " to resolve any issues.\n\n";
        $message .= "Please do not reply to this email.  If there is a problem, please contact the Housing Assignments office ";
        $message .= "at 828-262-6111, or by emailing housing@appstate.edu.\n";

        $success = HMS_Email::send_email($breakee . '@appstate.edu', NULL, 'HMS Roommate Pairing Broken', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the breakee ' . $breakee . ' of their broken roommate pairing with ' . $breakor . ', HMS_Roommate ' . $this->id);
        }

        return TRUE;
    }

    public function send_reject_emails()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $requestorStudent = StudentFactory::getStudentByUsername($this->requestor, $this->term);
        $requesteeStudent = StudentFactory::getStudentByUsername($this->requestee, $this->term);

        // to the requestor
        $message  = "To:     " . $requestorStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= "We're sorry, but " . $requesteeStudent->getFullName() . " has declined your roommate request.  Please contact ";
        $message .= $requesteeStudent->getFirstName() . " to resolve any issues.\n\n";
        $message .= "Please do not reply to this email.  If there is a problem, please contact the Housing Assignments office ";
        $message .= "at 828-262-6111, or by emailing housing@appstate.edu.\n";

        $success = HMS_Email::send_email($this->requestor . '@appstate.edu', NULL, 'HMS Roommate Declined', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the requestor ' . $this->requestor . ' of a declined roommate request from requestee ' . $this->requestee . ', HMS_Roommate ' . $this->id);
        }

        // to the requestee
        $message  = "To:     " . $requesteeStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= "This is a follow-up email to notify that you have declined the roommate request from " . $requestorStudent->getFullName() . ".\n\n";
        $message .= "If this was in error, you may re-request " . $requestorStudent->getFirstName() . " by logging into the Housing Management System at ";
        $message .= "http://hms.appstate.edu\n\n";
        $message .= "Please do not reply to this email.  If there is a problem, please contact the Housing Assignments office ";
        $message .= "at 828-262-6111, or by emailing housing@appstate.edu.\n";

        $success = HMS_Email::send_email($this->requestee . '@appstate.edu', NULL, 'HMS Roommate Declined', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the requestee ' . $this->requestee . ' of a declined roommate request for requestor ' . $this->requestor . ', HMS_Roommate ' . $this->id);
        }

        return TRUE;
    }

    public function send_cancel_emails()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');

        $requestorStudent = StudentFactory::getStudentByUsername($this->requestor, $this->term);
        $requesteeStudent = StudentFactory::getStudentByUsername($this->requestee, $this->term);

        // to the requestor
        $message  = "To:     " . $requestorStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= "This is a follow-up email to notify that you have cancelled your roommate request for " . $requesteeStudent->getFullName() . ".\n\n";
        $mesaage .= "If this was in error, you may re-request " . $requesteeStudent->getFirstName() . " by logging into the Housing Management System at ";
        $message .= "http://hms.appstate.edu\n\n";
        $message .= "Please do not reply to this email.  If there is a problem, please contact the Housing Assignments office ";
        $message .= "at 828-262-6111, or by emailing housing@appstate.edu.\n";

        $success = HMS_Email::send_email($this->requestor . '@appstate.edu', NULL, 'HMS Roommate Request Cancelled', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the requestor ' . $this->requestor . ' of a roommate request cancellation for requestee ' . $this->requestee . ', HMS_Roommate ' . $this->id);
        }

        // to the requestee
        $message  = "To:     " . $requesteeStudent->getFullName() . "\n";
        $message .= "From:   Housing Management System\n\n";
        $message .= $requestorStudent->getFullName() . " has cancelled the request for you to be roommates.  Please contact ";
        $message .= $requestorStudent->getFirstName() . " to resolve any issues.\n\n";
        $message .= "Please do not reply to this email.  If there is a problem, please contact the Housing Assignments office ";
        $message .= "at 828-262-6111, or by emailing housing@appstate.edu.\n";

        $success = HMS_Email::send_email($this->requestee . '@appstate.edu', NULL, 'HMS Roommate Request Cancelled', $message);

        if($success != TRUE) {
            throw new RoommateException('Error occurred emailing the requestee ' . $this->requestee . ' of a roommate request cancellation from requestor ' . $this->requestor . ', HMS_Roommate ' . $this->id);
        }

        return TRUE;
    }

    /**************
     * UI Methods *
     **************/

    /**
     * Shows a pager of roommate requests
     */
    public static function display_requests($asu_username, $term)
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
    public static function roommate_pager()
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
}
?>
