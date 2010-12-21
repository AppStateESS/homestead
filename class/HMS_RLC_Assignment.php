<?php

/**
 * The HMS_RLC_Assignment class
 *
 */

PHPWS_Core::initModClass('hms','StudentFactory.php');

class HMS_RLC_Assignment{

    public $id;

    public $rlc_id;
    public $gender;
    public $assigned_by_user;
    public $application_id;

    public $username; # For the DBPager join stuff to work right
    public $term; // For dbPager

    /**
     * Constructor
     *
     */
    public function HMS_RLC_Assignment($id = NULL)
    {
        if(isset($id)){
            $this->id = $id;
        }else{
            return;
        }

        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_RLC_Assignment()','Caught error from init');
            return $result;
        }
    }

    public function init()
    {
        $db = new PHPWS_DB('hms_learning_community_assignment');

        $db->addWhere('id',$this->getId(), '=');

        $result = $db->select('row');

        if(PEAR::isError($result)) {
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
        if(!Current_User::allow('hms', 'remove_rlc_members') ){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
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
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return TRUE;
    }

    /**
     * Returns the HMS_Learning_Community object for the community in this assignment.
     */
    public function getRlc()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        return new HMS_Learning_Community($this->getRlcId());
    }

    /**
     * Convenience shortcut method to the name of the RLC for this assignment
     */
    public function getRlcName(){
        return $this->getRlc()->get_community_name();
    }

    /******************
     * Static methods *
     */

    /**
     * Check to see if an assignment already exists for the specified user.  Returns FALSE if no assignment
     * exists.  If an assignment does exist, a db object containing that row is returned.  In the case of a db
     * error, a PEAR error object is returned.
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
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException("Could not check for assignment - $username $term " . $result->toString());
        }

        if(sizeof($result) > 1) {
            return $result;
        } else {
            return FALSE;
        }
    }

    public static function getAssignmentById($id){
        $assignment = new HMS_RLC_Assignment();

        $db = new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('id', $id);

        $result = $db->loadObject($assignment);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if(is_null($assignment->id)){
            return null;
        }

        return $assignment;
    }

    public static function getAssignmentByUsername($username, $term){
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $app = HMS_RLC_Application::getApplicationByUsername($username, $term);

        if(is_null($app)){
            return null;
        }

        $assignment = new HMS_RLC_Assignment();
        $db = new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('application_id', $app->id);

        $result = $db->loadObject($assignment);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if(is_null($assignment->id)){
            return null;
        }

        return $assignment;
    }

    public function rlc_assignment_admin_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

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
        PHPWS_Core::initModClass('hms','HMS_Learning_Community.php');

        $rlc_list = HMS_Learning_Community::getRLCListAbbr();

        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $tags = array();

        $tags['NAME']      = $student->getFullNameProfileLink();
        $tags['FINAL_RLC'] = $rlc_list[$this->getRlcId()];
        $tags['ROOMMATE']  = '';

        $addr = $student->getAddress();
        $reflect = new ReflectionObject($addr);
        $address = array();

        foreach($reflect->getProperties() as $prop){
            $address[] = $addr->{$prop->getName()};
        }

        $tags['ADDRESS']   = implode(", ", $address);

        $phones = $student->getPhoneNumberList();
        if(isset($phones) && !empty($phones)){
            $tags['PHONE']     = $phones[0];
        }else{
            $tags['PHONE']     = '';
        }

        $tags['EMAIL']     = "{$this->username}@appstate.edu";

        return $tags;
    }

    /*
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
        $input  = $this->getAdminPagerTags();
        $output = array();

        $student       = StudentFactory::getStudentByUsername($this->username, $this->term);
        $input['NAME'] = $student->getFullName();

        foreach($input as $key=>$value){
            //upercase the first letter of every word, and remove underscores in the array key
            $output[ucwords(strtolower(preg_replace('/_/', ' ', $key)))] = $value;
        }

        return $output;
    }

    //TODO move this!!
    public function view_by_rlc_pager($rlc_id)
    {
        // Get the community name for the title
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addWhere('id', $rlc_id);
        $db->addColumn('community_name');
        $tags['TITLE'] = $db->select('one') . ' Assignments ' . Term::toString(Term::getSelectedTerm(), TRUE);

        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $pager = new DBPager('hms_learning_community_applications', 'HMS_RLC_Application');
        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'application_id', 'id');
        $pager->db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm());
        $pager->db->addWhere('hms_learning_community_assignment.rlc_id', $rlc_id);

        //$pager->joinResult('id','hms_learning_community_applications','hms_assignment_id','user_id', 'user_id');
        $pager->setModule('hms');
        $pager->setTemplate('admin/view_by_rlc_pager.tpl');
        $pager->setLink('index.php?module=hms&action=ViewByRlc&rlc='.$rlc_id);
        $pager->setEmptyMessage('There are no students assigned to this learning community.');
        $pager->addPageTags($tags);
        $pager->addRowTags('viewByRLCPagerTags');
        $pager->setReportRow('report_by_rlc_pager_tags');

        return $pager->get();
    }

    /***********************
     * Accessor / Mutators *
     */

    public function setId($id) {
        $this->id = $id;
    }

    public function getId($id) {
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

    public function getCourseOk() {
        return $this->course_ok;
    }

    public function setAssignedByUser($assigned_by_user) {
        $this->assigned_by_user = $assigned_by_user;
    }

    public function getAssignedByUser() {
        return $this->assigned_by_user;
    }

    public function setAssignedByInitials($assigned_by_initials) {
        $this->assigned_by_initials = $assigned_by_initials;
    }

    public function getAssignedByInitials() {
        return $this->assigned_by_initials;
    }
}

?>
