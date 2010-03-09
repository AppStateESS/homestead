<?php

/**
 * The HMS_RLC_Assignment class
 *
 */

class HMS_RLC_Assignment{

    var $id;

    var $rlc_id;
    var $assigned_by_user;

    var $user_id; # For the DBPager join stuff to work right
    var $hms_assignment_id;

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

        $tags['TITLE'] = "View Final RLC Assignments " . Term::toString(Term::getSelectedTerm(), TRUE);

/*        $tags['PRINT_RECORDS'] = "// TODO: Print Records";
        $tags['EXPORT'] = "// TODO: Export Records";*/

        $pager = &new DBPager('hms_learning_community_assignment','HMS_RLC_Assignment');
      
        //$pager->db->addWhere('hms_learning_community_applications.hms_assignment_id','hms_learning_community_assignment.id','=');
        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'id', 'hms_assignment_id');
        $pager->db->addWhere('hms_learning_community_applications.term', Term::getSelectedTerm()); 

        $pager->joinResult('id','hms_learning_community_applications','hms_assignment_id','user_id', 'user_id');
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
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');

        $rlc_list = HMS_Learning_Community::getRLCListAbbr();

        $tags = array();
        
        $tags['NAME']      = PHPWS_Text::secureLink(HMS_SOAP::get_full_name_inverted($this->user_id), 'hms', array('type'=>'rlc', 'op'=>'view_rlc_application', 'username'=>$this->user_id), 'blank');
        $tags['FINAL_RLC'] = $rlc_list[$this->getRlcId()];
//        $tags['ROOMMATE']  = TODO: Roommate Stuff
        $tags['ADDRESS']   = HMS_SOAP::get_address_line($this->user_id);
        $tags['PHONE']     = HMS_SOAP::get_phone_number($this->user_id);
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
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $tags['NAME'] = PHPWS_Text::secureLink(HMS_SOAP::get_full_name($this->user_id), 'hms', array('type'=>'student', 'op'=>'get_matching_students', 'username'=>$this->user_id));
        $tags['GENDER'] = HMS_SOAP::get_gender($this->user_id);
        $tags['USERNAME'] = $this->user_id;

        $actions[] = PHPWS_Text::secureLink('View Application', 'hms', array('type'=>'rlc', 'op'=>'view_rlc_application', 'username'=>$this->user_id));
        $actions[] = PHPWS_Text::secureLink('Remove', 'hms', array('type'=>'rlc', 'op'=>'confirm_remove_from_rlc', 'id'=>$this->id, 'rlc'=>$_REQUEST['rlc']));

        $tags['ACTION'] = implode(' | ', $actions);
        return $tags;
    }

    public function report_by_rlc_pager_tags()
    {
        $row['name']        = HMS_SOAP::get_full_name($this->user_id);
        $row['gender']      = HMS_SOAP::get_gender($this->user_id);
        $row['username']    = $this->user_id;

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
