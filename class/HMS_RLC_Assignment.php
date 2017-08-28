<?php

namespace Homestead;

use \Homestead\exception\PermissionException;
use \Homestead\exception\DatabaseException;
use \PHPWS_Error;
use \PHPWS_DB;

/**
 * The HMS_RLC_Assignment class
 *
 * @author jbooker
 * @package HMS
 */

class HMS_RLC_Assignment {

    public $id;

    public $rlc_id;
    public $gender;
    public $assigned_by_user;
    public $application_id;

    public $state;           // db text field for state name
    public $assignmentState; // An RlcAssignmentState object

    public $username; // For the DBPager join stuff to work right
    public $term; // For dbPager

    /**
     * Constructor
     *
     */
    public function __construct($id = NULL)
    {
        if(isset($id)){
            $this->id = $id;
        }else{
            return;
        }

        $result = $this->init();
        if(\PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_RLC_Assignment()','Caught error from init');
            return $result;
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('hms_learning_community_assignment');

        $db->addWhere('id',$this->getId(), '=');

        $result = $db->select('row');

        if(\PEAR::isError($result)) {
            PHPWS_Error::log($result,'hms','init',"id:{$id}");
            return $result;
        }

        if(sizeof($result) < 1) {
            return FALSE;
        }

        if($result == FALSE || $result == NULL) return;

        $this->user_id = $result['asu_username'];
        $this->rlc_id  = $result['rlc_id'];
        $this->assigned_by_user = $result['assigned_by_user'];

        return $result;
    }

    public function delete()
    {
        if(!\Current_User::allow('hms', 'remove_rlc_members') ){
            throw new PermissionException('You do not have permission to remove RLC members.');
        }

        if(!isset($this->id)) {
            return FALSE;
        }

        $db = new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('id',$this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
        }

        $this->id = 0;

        return TRUE;
    }

    /**
     * Saves the current Assignment object to the database.
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_learning_community_assignment');

        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return TRUE;
    }

    /**
     * Returns the HMS_Learning_Community object for the community in this assignment.
     */
    public function getRlc()
    {
        return new HMS_Learning_Community($this->getRlcId());
    }

    /**
     * Convenience shortcut method to the name of the RLC for this assignment
     */
    public function getRlcName(){
        return $this->getRlc()->get_community_name();
    }

    public function getApplication()
    {
        $application = new HMS_RLC_Application($this->getApplicationId());

        if(!isset($application)){
            throw \Exception('Could not load RLC application.');
        }

        return $application;
    }

    public function changeState(RlcAssignmentState $newState)
    {
        // Save the new state's name, catching any exceptions
        $this->state = $newState->getStateName();
        try{
            $this->save();
        }catch(\Exception $e){
            throw $e;
        }

        // If we made it this far, then do the onEnter stuff
        $newState->onEnter();
    }

    /******************
     * Static methods *
     */

    /**
     * Check to see if an assignment already exists for the specified user.  Returns FALSE if no assignment
     * exists.  If an assignment does exist, a db object containing that row is returned.  In the case of a db
     * error, a \PEAR error object is returned.
     * TODO: Deprecate this and/or move to RlcMembershipFactory
     * @see RlcMembershipFactory
     *
     */
    public static function checkForAssignment($username, $term)
    {
        $db = new PHPWS_DB('hms_learning_community_applications');
        $db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'application_id', 'id');
        $db->addWhere('hms_learning_community_assignment.id', null, 'IS NOT');
        $db->addWhere('hms_learning_community_applications.username',$username,'ILIKE');
        $db->addWhere('hms_learning_community_applications.term', $term);

        $db->addColumn('hms_learning_community_assignment.*');
        $db->addColumn('hms_learning_community_applications.*');

        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException("Could not check for assignment - $username $term " . $result->toString());
        }

        if(sizeof($result) > 1) {
            return $result;
        } else {
            return FALSE;
        }
    }

    /**
     * TODO: Deprecate this and/or move to RlcMembershipFactory
     * @see RlcMembershipFactory
     *
     * @param unknown $id
     * @throws DatabaseException
     * @return NULL|HMS_RLC_Assignment
     */
    public static function getAssignmentById($id){
        $assignment = new HMS_RLC_Assignment();

        $db = new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('id', $id);

        $result = $db->loadObject($assignment);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if(is_null($assignment->id)){
            return null;
        }

        return $assignment;
    }

    /**
     * TODO: Deprecate this and/or move to RlcMembershipFactory
     * @see RlcMembershipFactory
     *
     * @param unknown $username
     * @param unknown $term
     * @throws DatabaseException
     * @return NULL|HMS_RLC_Assignment
     */
    public static function getAssignmentByUsername($username, $term){
        $app = HMS_RLC_Application::getApplicationByUsername($username, $term);

        if(is_null($app)){
            return null;
        }

        $assignment = new HMS_RLC_Assignment();
        $db = new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('application_id', $app->id);

        $result = $db->loadObject($assignment);

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        if(is_null($assignment->id)){
            return null;
        }

        return $assignment;
    }

    public function rlc_assignment_admin_pager()
    {
        \PHPWS_Core::initCoreClass('DBPager.php');

        $tags = array();

        test('ooh hia!',1);

        $tags['TITLE'] = "View Final RLC Assignments " . Term::toString(Term::getSelectedTerm(), TRUE);

        $pager = new DBPager('hms_learning_community_assignment','HMS_RLC_Assignment');

        //$pager->db->addWhere('hms_learning_community_applications.hms_assignment_id','hms_learning_community_assignment.id','=');
        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'id', 'hms_assignment_id');
        $pager->db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());

        //$pager->joinResult('id','hms_learning_community_applications','hms_assignment_id','user_id', 'user_id');
        $pager->joinResult('id','hms_learning_community_applications','hms_assignment_id','term');
        $pager->setModule('hms');
        $pager->setTemplate('admin/display_final_rlc_assignments.tpl');
        $pager->setLink('index.php?module=hms&type=rlc&op=assign_applicants_to_rlcs');
        $pager->setEmptyMessage('No RLC assignments have been made.');
        $pager->addPageTags($tags);
        $pager->addRowTags('getAdminPagerTags');

        return $pager->get();
    }

    public function getAdminPagerTags()
    {
        $rlc_list = HMS_Learning_Community::getRLCListAbbr();

        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $tags = array();

        $tags['NAME']      = $student->getProfileLink();
        $tags['FINAL_RLC'] = $rlc_list[$this->getRlcId()];

        // Not sure why this line was here but was always empty, so I commented it out for now
        //$tags['ROOMMATE']  = '';

        $addr = $student->getAddress();
        if($addr !== FALSE && !is_null($addr)){
            $tags['ADDRESS'] = $student->getAddressLine();
        }

        $phones = $student->getPhoneNumberList();
        if(isset($phones) && !empty($phones)){
            $tags['PHONE']     = $phones[0];
        }else{
            $tags['PHONE']     = '';
        }

        $tags['EMAIL']     = "{$this->username}@appstate.edu";

        return $tags;
    }

    /**
     * getAdminCsvRow
     *
     *  This function converts the output of the adminPagerTags function
     * into something that the db pager's csv reporter understands.  It
     * replaces the html name link with a plain text one to avoid it being
     * squelched in the output and changes the case of the array indices
     * so that the column names look like the html report.
     */
    public function getAdminCsvRow()
    {
        $row = array();

        // Get the RLC Application
        $rlcApp = $this->getApplication();

        // Get list of RLC names
        $rlcList = HMS_Learning_Community::getRLCListAbbr();

        // Get the student object
        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        // Get Housing App object
        $housingApp = HousingApplicationFactory::getAppByStudent($student, $rlcApp->getTerm());

        // Student info
        $row['name']         = $student->getFullName();
        $row['banner_id']    = $student->getBannerId();
        $row['email']        = $student->getUsername();
        $row['gender']       = $student->getPrintableGender();

        // RLC info
        $row['rlc']    = $rlcList[$this->getRlcId()];

        // Address columns
        $addressObj = $student->getAddress();
        if (isset($addressObj) && !is_null($addressObj)) {
            $address = (Array) $addressObj;
            unset($address['county']); // Remove the county column, don't want it
            unset($address['atyp_code']); // Remove the address type column
            $row += $address;
        } else {
            // Provide empty columns so the alignment of the csv file doesn't get screwed up
            $row['line1'] = '';
            $row['line2'] = '';
            $row['city'] = '';
            $row['state'] = '';
            $row['zip'] = '';
        }

        // Phone number
        if ($housingApp instanceof HousingApplication) {
            $cellPhone = $housingApp->getCellPhone();
            if (isset($cellPhone) && $cellPhone != '') {
                $row['cell_phone'] = $cellPhone;
            } else {
                // Provide empty columns so the alignment of the csv file doesn't get screwed up
                $row['cell_phone'] = '';
            }
        } else {
            $row['cell_phone'] = '';
        }

        return $row;
    }

    /***********************
     * Accessor / Mutators *
    */

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setRlcId($rlc_id) {
        $this->rlc_id = $rlc_id;
    }

    public function getRlcId() {
        return $this->rlc_id;
    }

    public function setCourseOk($course_ok) {
        $this->course_ok = $course_ok;
    }

    /**
     * @depricated?
     */
    public function getCourseOk() {
        return $this->course_ok;
    }

    public function setAssignedByUser($assigned_by_user) {
        $this->assigned_by_user = $assigned_by_user;
    }

    public function getAssignedByUser() {
        return $this->assigned_by_user;
    }

    public function getApplicationId(){
        return $this->application_id;
    }

    /**
     * @depricated?
     */
    public function setAssignedByInitials($assigned_by_initials) {
        $this->assigned_by_initials = $assigned_by_initials;
    }

    public function getAssignedByInitials() {
        return $this->assigned_by_initials;
    }

    public function getStateName(){
        return $this->state;
    }

    public function setState($newState)
    {
        $this->state = $newState;
    }
}

class RlcMembershipRestored extends HMS_RLC_Assignment {
    public function __construct(){}
}
