<?php

/**
 * The HMS_RLC_Assignment class
 *
 */

PHPWS_Core::initModClass('hms','StudentFactory.php');

class HMS_RLC_Assignment{

    public $id;

    public $rlc_id;
    public $assigned_by_user;

    public $user_id; # For the DBPager join stuff to work right
    public $term; // For dbPager
    
    public $hms_assignment_id;

    /**
     * Constructor
     *
     */
    public function HMS_RLC_Assignment($id = NULL)
    {
        /*
        if(isset($user_id)){
            $this->setUserID($id);
        }else{
            return;
        }
        */

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
        if( !Current_User::allow('hms', 'learning_community_maintenance') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        if(!isset($this->id)) {
            return FALSE;
        }

        $db = &new PHPWS_DB('hms_learning_community_assignment');
        $db->addWhere('id',$this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)) {
            return FALSE;
        }

        $this->id = 0;

        return TRUE;
    }

    /**
     * Check to see if an assignment already exists for the specified user.  Returns FALSE if no assignment
     * exists.  If an assignment does exist, a db object containing that row is returned.  In the case of a db
     * error, a PEAR error object is returned.
     */
    public function check_for_assignment($asu_username, $term)
    {
        $db = new PHPWS_DB('hms_learning_community_assignment');
        $db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'id', 'hms_assignment_id');
        $db->addWhere('hms_learning_community_applications.user_id',$asu_username,'ILIKE');
        $db->addWhere('hms_learning_community_applications.term', $term);

        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException("Could not check for assignment - $asu_username $term");
        }

        if(sizeof($result) > 1) {
            return $result;
        } else {
            return FALSE;
        }
    }

    /**
     * Saves the current Assignment object to the database.
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_learning_community_assignment');

        $db->addValue('asu_username',         $this->getAsuUsername());
        $db->addValue('rlc_id',               $this->getRlcId());
        $db->addValue('assigned_by_user',     $this->getAssignedByUser());
        $db->addValue('assigned_by_initials', $this->getAssignedByInitials());

        if(!$this->getId() || $this->getId() == NULL) {
            // do an insert
            $result = $db->insert();
        } else {
            // do an update
            $db->addWhere('id',$this->getId(), '=');
            $result = $db->update();
        }

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result, 'hms', 'save_rlc_assignment', 'Could not insert/update rlc assignment for user: '.$this->getId());
            return $result;
        } else {
            return TRUE;
        }
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
        
        $student = StudentFactory::getStudentByUsername($this->user_id, $this->term);
        
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
        
        $tags['EMAIL']     = "{$this->user_id}@appstate.edu";

        return $tags;
    }

    public function view_by_rlc_pager($rlc_id)
    {
        // Get the community name for the title
        $db = new PHPWS_DB('hms_learning_communities');
        $db->addWhere('id', $rlc_id);
        $db->addColumn('community_name');
        $tags['TITLE'] = $db->select('one') . ' Assignments ' . Term::toString(Term::getSelectedTerm(), TRUE);
       
        PHPWS_Core::initCoreClass('DBPager.php');
        
        $pager = new DBPager('hms_learning_community_assignment', 'HMS_RLC_Assignment');
        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'id', 'hms_assignment_id');
        $pager->db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm()); 
        $pager->db->addWhere('rlc_id', $rlc_id);
        
        $pager->joinResult('id','hms_learning_community_applications','hms_assignment_id','user_id', 'user_id');
        $pager->setModule('hms');
        $pager->setTemplate('admin/view_by_rlc_pager.tpl');
        $pager->setLink('index.php?module=hms&action=ViewByRlc&rlc='.$rlc_id);
        $pager->setEmptyMessage('There are no students assigned to this learning community.');
        $pager->addPageTags($tags);
        $pager->addRowTags('viewByRLCPagerTags');
        $pager->setReportRow('report_by_rlc_pager_tags');

        return $pager->get();
    }

    public function viewByRLCPagerTags()
    {
        $student = StudentFactory::getStudentByUsername($this->user_id, Term::getSelectedTerm());
        
        $tags['NAME'] = $student->getFulLNameProfileLink();
        $tags['GENDER'] = $student->getPrintableGender();
        $tags['USERNAME'] = $this->user_id;

        $viewCmd = CommandFactory::getCommand('ShowRlcApplicationReView');
        $viewCmd->setUsername($student->getUsername());
        
        $actions[] = $viewCmd->getLink('View Application');
        
        $rmCmd = CommandFactory::getCommand('RemoveRlcAssignment');
        $rmCmd->setAssignmentId($this->id);
        
        $actions[] = $rmCmd->getLink('Remove');

        $tags['ACTION'] = implode(' | ', $actions);
        return $tags;
    }

    public function report_by_rlc_pager_tags()
    {
        $student = StudentFactory::getStudentByUsername($this->user_id, Term::getSelectedTerm());
        
        $row['name']        = $student->getFullName();
        $row['gender']      = $student->getPrintableGender();
        $row['username']    = $student->getGender();

        return $row;
    }


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
