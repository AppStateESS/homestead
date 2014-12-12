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

/**
 * HMS Roommate Class - Represents a freshmen roommate request object
 * @author Jeremy Booker
 * @package Hms
 */
class HMS_Roommate
{

    public $id           = 0;
    public $term         = null;
    public $requestor    = null;
    public $requestee    = null;
    public $confirmed    = 0;
    public $requested_on = 0;
    public $confirmed_on = null;

    /**
     * Constructor
     */
    public function HMS_Roommate($id = 0)
    {
        if (!$id) {
            return;
        }

        $this->id = $id;
        $db = new PHPWS_DB('HMS_Roommate');
        $db->addWhere('id', $this->id);
        $result = $db->loadObject($this);
        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        if ($result === false) {
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
        if ($result != E_SUCCESS) {
            PHPWS_Core::initModClass('hms', 'exception/RoommateCompatibilityException.php');
            throw new RoommateCompatibilityException($result);
        }

        return true;
    }

    public function confirm()
    {
        $result = $this->can_live_together();
        if ($result != E_SUCCESS) {
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
        if (!$result || PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        return true;

    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        $this->id = 0;

        return true;
    }

    public function get_other_guy($username)
    {
        if (trim($this->requestor) == trim($username)) {
            return $this->requestee;
        } else if (trim($this->requestee) == trim($username)) {
            return $this->requestor;
        }

        throw new RoommateException("$username is not in roommate pairing " . $this->id);
    }

    public function getRequestor()
    {
        return $this->requestor;
    }
    
    public function getRequestee()
    {
        return $this->requestee;
    }
    
    /******************
     * Static Methods *
     ******************/

    public static function getByUsernames($a, $b, $term)
    {
        PHPWS_Core::initCoreClass('PdoFactory.php');
        $db = PdoFactory::getInstance()->getPdo();
        
        $query = $db->prepare("SELECT * FROM hms_roommate WHERE term = :term AND ((requestor ILIKE :usera AND requestee ILIKE :userb) OR (requestor ILIKE :userb AND requestee ILIKE :usera))");
        $query->bindParam(':term', $term);
        $query->bindParam(':usera', $a);
        $query->bindParam(':userb', $b);
        
        $query->execute();
        $results = $query->fetchAll(PDO::FETCH_CLASS, "HMS_Roommate");
        
        return $results[0];
    }

    public function get_all_confirmed_roommates($term = NULL, $random = false)
    {
        if (is_null($term)) {
            $term = Term::getSelectedTerm();
        }

        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('term', $term);
        $db->addWhere('confirmed', 1);
        if ($random) {
            $db->addOrder('random');
        }
        $db->addColumn('requestor');
        $db->addColumn('requestee');
        $result = $db->select();

        if (PHPWS_Error::logIfError($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Checks whether a given pair are involved in a roommate request already.
     *
     * @returns true if so, false if not
     *
     * @param a A user to check on
     * @param b Another user to check on
     */
    public function have_requested_each_other($a, $b, $term)
    {
        $ttl = time() - ROOMMATE_REQ_TIMEOUT;

        $query = "SELECT COUNT(*) FROM hms_roommate WHERE hms_roommate.term = $term AND hms_roommate.confirmed = 0 AND hms_roommate.requested_on >= $ttl AND ((hms_roommate.requestor ILIKE '$a' AND hms_roommate.requestee ILIKE '$b') OR (hms_roommate.requestor ILIKE '$b' AND hms_roommate.requestee ILIKE '$a'))";

        $result = PHPWS_DB::getOne($query);

        if ($result > 1) {
            // TODO: Log Weird Situation
        }

        return ($result > 0 ? true : false);
    }

    /*
     * Returns true if the student has a confirmed roommate, false otherwise
     */
    public function has_confirmed_roommate($asu_username, $term)
    {
        PHPWS_Core::initCoreClass('PdoFactory.php');
        $db = PdoFactory::getInstance()->getPdo();
        
        $query = $db->prepare("SELECT COUNT(*) FROM hms_roommate WHERE term = :term AND (requestor ILIKE :user OR requestee ILIKE :user) AND confirmed = 1");
        $query->bindParam(':term', $term);
        $query->bindParam(':user', $asu_username);

        $query->execute();
        $result = $query->fetchColumn();
        
        if ($result > 1) {
            // TODO: Log Weird Situation
        }

        return ($result > 0 ? true : false);
    }

    /**
     * Returns the given user's confirmed roommate or false if the roommate is unconfirmed
     * 
     * @param string $asu_username
     * @param string $term
     * @return Student
     */
    public function get_confirmed_roommate($asu_username, $term)
    {
        /*
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->addWhere('requestee', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->setGroupConj('grp', 'AND');
        $db->addWhere('confirmed', 1);
        $db->addWhere('term', $term);
        $db->addColumn('requestor');
        $db->addColumn('requestee');
        */

        PHPWS_Core::initCoreClass('PdoFactory.php');
        $db = PdoFactory::getInstance()->getPdo();
        
        $stmt = $db->prepare("SELECT * FROM hms_roommate WHERE (requestor ILIKE :user OR requestee ILIKE :user) AND term = :term AND confirmed = 1");
        $stmt->bindParam(':user', $asu_username);
        $stmt->bindParam(':term', $term);
        
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($result) > 1) {
            // TODO: Log Weird Situation
        }

        if (count($result) == 0) {
            return null;
        }

        if (trim($result[0]['requestor']) == trim($asu_username)) {
            return StudentFactory::getStudentByUsername($result[0]['requestee'], $term);
        }

        return StudentFactory::getStudentByUsername($result[0]['requestor'], $term);
    }

    public static function get_pending_roommate($asu_username, $term)
    {
        /*
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
        */

        PHPWS_Core::initModClass('hms', 'PdoFactory.php');
        $db = PdoFactory::getInstance()->getPdo();
        
        $stmt = $db->prepare("SELECT * FROM hms_roommate WHERE (requestor ILIKE :user OR requestee ILIKE :user) AND term = :term AND confirmed = 0 and requested_on >= :ttl");
        $stmt->bindParam(':user', $asu_username);
        $stmt->bindParam(':term', $term);
        
        $ttl = mktime() - ROOMMATE_REQ_TIMEOUT;
        $stmt->bindParam(':ttl', $ttl);
        
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($result) > 1) {
            // TODO: Log Weird Situation
        }

        if (count($result) == 0)
        return null;

        $result = $result[0];
        
        if (trim($result['requestor']) == trim($asu_username)) {
            return StudentFactory::getStudentByUsername($result['requestee'], $term);
        }

        return StudentFactory::getStudentByUsername($result['requestor'], $term);
    }

    /**
     * Checks whether a given user has made a roommate request which is still pending.
     *
     * @returns true if so, false if not
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
        
        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException('Unexpected error in has_roommate_request');
        }

        return ($result > 0 ? true : false);
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

        if (count($result) > 1) {
            // TODO: Log Weird Situation
        }

        if (!isset($result[0])) {
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
        /*
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->addWhere('requestee', $asu_username, 'ILIKE', 'OR', 'grp');
        $db->setGroupConj('grp', 'AND');
        $db->addWhere('term', $term);
        $result = $db->getObjects('HMS_Roommate');

        if (PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
        */
        
        PHPWS_Core::initCoreClass('PdoFactory.php');
        $db = PdoFactory::getInstance()->getPdo();
        
        $stmt = $db->prepare("SELECT * FROM hms_roommate WHERE (requestor ILIKE :user OR requestee ILIKE :user) AND term = :term");
        $stmt->bindParam(':user', $asu_username);
        $stmt->bindParam(':term', $term);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, "HMS_Roommate");
    }

    /**
     * Removes all pending requests.  THIS DOES WORK SO BE CAREFUL.  Used when roommates are confirmed.
     * Logs each individual removal to cover our butts.
     */
    public function removeOutstandingRequests($asu_username, $term)
    {
        /*
        $db = new PHPWS_DB('hms_roommate');
        $db->addWhere('requestee', $asu_username, 'ILIKE', NULL, 'username_group');
        $db->addWhere('requestor', $asu_username, 'ILIKE', 'OR', 'username_group');
        $db->setGroupConj('username_group', 'AND');

        $db->addWhere('confirmed', 0);
        $db->addWhere('term', $term);
        $requests = $db->getObjects('HMS_Roommate');

        if (PHPWS_Error::logIfError($requests)) {
            throw new DatabaseException('Could not remove outstanding requests');
        }
        */
        
        PHPWS_Core::initCoreClass('PdoFactory.php');
        $db = PdoFactory::getInstance()->getPdo();
        
        $query = $db->prepare("SELECT * FROM hms_roommate WHERE (requestee ILIKE :user OR requestor ILIKE :user) AND term = :term AND confirmed = 0");
        $query->bindParam(':term', $term);
        $query->bindParam(':user', $asu_username);
        
        $query->execute();
        $requests = $query->fetchAll(PDO::FETCH_CLASS, "HMS_Roommate");

        if ($requests == null) {
            return true;
        }

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        foreach ($requests as $request) {
            HMS_Activity_Log::log_activity($request->requestor, ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ, UserStatus::getUsername(), "$request->requestee: Due to confirmed roommate");
            HMS_Activity_Log::log_activity($request->requestee, ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ, UserStatus::getUsername(), "$request->requestor: Due to confirmed roommate");
            $request->delete();
        }

        return true;
    }

    /**
     * Depricated per ticket #530
     * @deprecated
     */
    public function check_rlc_applications()
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        $result  = HMS_RLC_Application::checkForApplication($this->requestor, $this->term, false);
        $resultb = HMS_RLC_Application::checkForApplication($this->requestee, $this->term, false);

        if ($result === false && $resultb === false)
        return true;

        if ($result === false || $resultb === false)
        return false;

        // Check to see if any of a's choices match any of b's choices
        if ($result['rlc_first_choice_id']  == $resultb['rlc_first_choice_id'] ||
        $result['rlc_first_choice_id']  == $resultb['rlc_second_choice_id'] ||
        $result['rlc_first_choice_id']  == $resultb['rlc_third_choice_id'] ||
        $result['rlc_second_choice_id'] == $resultb['rlc_first_choice_id'] ||
        $result['rlc_second_choice_id'] == $resultb['rlc_second_choice_id'] ||
        $result['rlc_second_choice_id'] == $resultb['rlc_third_choice_id'] ||
        $result['rlc_third_choice_id']  == $resultb['rlc_first_choice_id'] ||
        $result['rlc_third_choice_id']  == $resultb['rlc_second_choice_id'] ||
        $result['rlc_third_choice_id']  == $resultb['rrlc_third_choice_id']) {
            return true;
        }

        return false;
    }

    /**
     * Depricated per ticket #530
     * @deprecated
     */
    public function check_rlc_assignments()
    {
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        $resulta = HMS_RLC_Assignment::checkForAssignment($this->requestor, $this->term);
        $resultb = HMS_RLC_Assignment::checkForAssignment($this->requestee, $this->term);

        if ($resulta === false && $resultb === false) {
            return true;
        }

        if ($resulta !== false && $resultb !== false) {
            if ($resulta['rlc_id'] == $resultb['rlc_id']) {
                return true;
            }
        }

        return false;
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
        if ($expires == 0) {
            $expires = floor(($this->calc_req_expiration_date() - mktime()) / 60);
            $tpl['EXPIRES'] = $expires . ' minute' . ($expires > 1 ? 's' : '');
        } else {
            $tpl['EXPIRES'] = $expires . ' hour' . ($expires > 1 ? 's' : '');
        }
        return $tpl;
    }

    public function get_roommate_pager_tags()
    {
        $tags = array();

        $term = Term::getSelectedTerm();

        $requestor = StudentFactory::getStudentByUsername($this->requestor, $term);
        $requestee = StudentFactory::getStudentByUsername($this->requestee, $term);

        $deleteCmd = CommandFactory::getCommand('DeleteRoommateGroup');
        $deleteCmd->setId($this->id);

        $tags['REQUESTOR']      = $requestor->getProfileLink();
        $tags['REQUESTEE']      = $requestee->getProfileLink();
        $tags['REQUESTED_ON']   = HMS_Util::get_long_date_time($this->requested_on);
        $tags['CONFIRMED_ON']   = HMS_Util::get_long_date_time($this->confirmed_on);
        $tags['ACTION']         = $deleteCmd->getLink('Delete');

        return $tags;
    }

    /**
     * Checks to see if two people hypothetically could live together based on
     * our rules.
     *
     * @returns true if so, false if not
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
        if (is_null($requestor)) {
            return E_ROOMMATE_MALFORMED_USERNAME;
        }

        if (is_null($requestee)) {
            return E_ROOMMATE_MALFORMED_USERNAME;
        }

        // Make sure requestor didn't request self
        if ($requestor == $requestee) {
            return E_ROOMMATE_REQUESTED_SELF;
        }

        // Make sure requestor and requestee are not requesting each other
        if (HMS_Roommate::have_requested_each_other($requestor, $requestee, $term)) {
            return E_ROOMMATE_ALREADY_REQUESTED;
        }

        // Make sure requestor does not have a pending roommate request
        if (HMS_Roommate::has_roommate_request($requestor, $term)) {
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
        if (HMS_Roommate::has_confirmed_roommate($requestor, $term)) {
            return E_ROOMMATE_ALREADY_CONFIRMED;
        }

        // Check if the requestee has a confirmed roommate
        if (HMS_Roommate::has_confirmed_roommate($requestee, $term)) {
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
        if ($requestor_info->getGender() != $requestee_info->getGender()) {
            return E_ROOMMATE_GENDER_MISMATCH;
        }

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        // Make sure the requestee has filled out an application
        if (HousingApplication::checkForApplication($requestee, $term) === false) {
            return E_ROOMMATE_NO_APPLICATION;
        }

        // Students can only request a student of the same "type"
        // This is based on the application term (because students starting
        // in the summer will have different types). The students must have
        // the same application term, unless either student's application
        // term is a summer session of the same year

        /*
        if ($requestor_info->getType() != $requestee_info->getType()) {
            return E_ROOMMATE_TYPE_MISMATCH;
        }*/

        $aTerm = $requestor_info->getApplicationTerm();
        $aYear = Term::getTermYear($aTerm);
        $aSem  = Term::getTermSem($aTerm);

        $bTerm = $requestee_info->getApplicationTerm();
        $bYear = Term::getTermYear($bTerm);
        $bSem  = Term::getTermSem($bTerm);

        // There's a mismatch if the years don't match OR (the years match AND (either student started in the Spring))
        // This allows people with summer application terms to request each other, but prevents continuing students from requesting each other
        // (even if the one student started in the Spring and has a 'F' student type at the time the request is made)
        if ($aYear != $bYear || ($aYear == $bYear && (($aSem == TERM_SPRING && $bSem != TERM_SPRING) || ($bSem == TERM_SPRING && $aSem != TERM_SPRING)))) {
            return E_ROOMMATE_TYPE_MISMATCH;
        }

        // Transfer students can only request other transfers - Prevents freshmen from requesting transfers and vice versa
        if (($requestor_info->getType() == TYPE_TRANSFER && $requestee_info->getType() != TYPE_TRANSFER) || ($requestee_info->getType() == TYPE_TRANSFER && $requestor_info->getType() != TYPE_TRANSFER)) {
            return E_ROOMMATE_TYPE_MISMATCH;
        }

        /*
         // Make sure RLC Applications are compatible
         if (!$this->check_rlc_applications()) {
         return E_ROOMMATE_RLC_APPLICATION;
         }

         // If either student is assigned to an RLC, do not allow the request
         if (!$this->check_rlc_assignments()) {
         return E_ROOMMATE_RLC_ASSIGNMENT;
         }
         */

        return E_SUCCESS;
    }

    /*
     * Performs all the checks necessary before allowing an administrator to
     * create a roommate pairing
     */
    public function canLiveTogetherAdmin(Student $roommate1, Student $roommate2, $term)
    {

        // Sanity Checking
        if (is_null($roommate1)) {
            throw new RoommateException('Null student object for roommate 1.');
        }

        if (is_null($roommate2)) {
            throw new RoommateException('Null student object for roommate 1.');
        }

        // Check that the two user names aren't the same
        if ($roommate1->getUsername() == $roommate2->getUsername()) {
            throw new RoommateException('Roommate user names must be unique.');
        }

        // Use SOAP for the following checks
        // Make that both roommate have some sort of soap info
        $name = $roommate1->getLastName();
        if (empty($name)) {
            throw new RoommateException('No banner information for first roommate.');
        }

        $name = $roommate2->getLastName();
        if (empty($name)) {
            throw new RoommateException('No banner information for second roommate.');
        }

        // Make sure the genders match
        if ($roommate1->getGender() != $roommate2->getGender()) {
            throw new RoommateException('Roommate genders do not match.');
        }

        // Check if either has a confirmed roommate
        if (HMS_Roommate::has_confirmed_roommate($roommate1->getUsername(), $term)) {
            throw new RoommateException('The first roommate already has a confirmed roommate.');
        }

        if (HMS_Roommate::has_confirmed_roommate($roommate2->getUsername(), $term)) {
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

        // Setup searching on the requestor and requestee columns
        $pager->setSearch('requestor', 'requestee');

        return $pager->get();
    }
}
?>
