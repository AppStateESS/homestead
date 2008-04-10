<?php

/**
 * The HMS_RLC_Assignment class
 *
 */

class HMS_RLC_Assignment{

    var $id;

    var $rlc_id;
    var $course_ok;
    var $assigned_by_user;
    var $assigned_by_initals;

    var $user_id; # For the DBPager join stuff to work right
    var $hms_assignment_id;
    var $lc_application_term;

    /**
     * Constructor
     *
     */
    function HMS_RLC_Assignment($id = NULL)
    {
        if(isset($user_id)){
            $this->setUserID($id);
        }else{
            return;
        }

        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result,'hms','HMS_RLC_Assignment()','Caught error from init'); 
            return $result;
        }
    }

    function init()
    {
        $db = &new PHPWS_DB('hms_learning_community_assignment');

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

        $this->setAsuUsername($result['asu_username']);
        $this->setRlcId($result['rlc_id']);
        $this->setCourseOk($result['course_ok'] == 1 ? 'Y' : 'N');
        $this->setAssignedByUser($result['assigned_by_user']);
        $this->setAssignedByInitials($result['assigned_by_initials']);

        return $result;
    }

    /**
     * Check to see if an assignment already exists for the specified user.  Returns FALSE if no assignment
     * exists.  If an assignment does exist, a db object containing that row is returned.  In the case of a db
     * error, a PEAR error object is returned.
     */
    function check_for_assignment($asu_username = NULL, $application_term = NULL)
    {
        $db = &new PHPWS_DB('hms_learning_community_assignment');

        $db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'id', 'hms_assignment_id');

        if(isset($asu_username)) {
            $db->addWhere('hms_learning_community_applications.user_id',$asu_username,'ILIKE');
        } else {
            $db->addWhere('hms_learning_community_applications.user_id',$_SESSION['asu_username'],'ILIKE');
        }

        if(isset($application_term)) {
            $db->addWhere('hms_learning_community_applications.term', $application_term);
        } else {
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $db->addWhere('hms_learning_community_applications.term', HMS_Term::get_current_term());
        }

        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)) {
            return $result;
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
    function save()
    {
        $db = &new PHPWS_DB('hms_learning_community_assignment');

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

    function rlc_assignment_admin_pager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('hms','HMS_Term.php');

        $tags = array();

        $tags['TITLE'] = "View Final RLC Assignments " . HMS_Term::term_to_text(HMS_Term::get_selected_term(), TRUE);

/*        $tags['PRINT_RECORDS'] = "// TODO: Print Records";
        $tags['EXPORT'] = "// TODO: Export Records";*/

        $pager = &new DBPager('hms_learning_community_assignment','HMS_RLC_Assignment');
      
        $pager->db->addWhere('hms_learning_community_applications.hms_assignment_id','hms_learning_community_assignment.id','=');
        $pager->db->addJoin('LEFT OUTER', 'hms_learning_community_assignment', 'hms_learning_community_applications', 'id', 'hms_assignment_id');
        $pager->db->addWhere('hms_learning_community_applications.term', HMS_Term::get_selected_term()); 

        $pager->joinResult('id','hms_learning_community_applications','hms_assignment_id','user_id', 'user_id');
        $pager->setModule('hms');
        $pager->setTemplate('admin/display_final_rlc_assignments.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage('No RLC assignments have been made.');
        $pager->addPageTags($tags);
        $pager->addRowTags('getAdminPagerTags');
        return $pager->get();
    }

    function getAdminPagerTags()
    {
        PHPWS_Core::initModClass('hms','HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');

        $rlc_list = HMS_Learning_Community::getRLCListAbbr();

        $tags = array();
        
        $tags['NAME']      = '<a href="./index.php?module=hms&type=rlc&op=view_rlc_application&username='.$this->user_id.'" target="_blank">' . HMS_SOAP::get_full_name_inverted($this->user_id) . '</a>';
        $tags['FINAL_RLC'] = $rlc_list[$this->getRlcId()];
//        $tags['ROOMMATE']  = TODO: Roommate Stuff
        $tags['ADDRESS']   = HMS_SOAP::get_address_line($this->user_id);
        $tags['PHONE']     = HMS_SOAP::get_phone_number($this->user_id);
        $tags['EMAIL']     = "{$this->user_id}@appstate.edu";

        return $tags;
    }

    function setId($id) {
        $this->id = $id;
    }

    function getId($id) {
        return $this->id;
    }

    function setRlcId($rlc_id) {
        $this->rlc_id = $rlc_id;
    }

    function getRlcId() {
        return $this->rlc_id;
    }

    function setCourseOk($course_ok) {
        $this->course_ok = $course_ok;
    }

    function getCourseOk() {
        return $this->course_ok;
    }

    function setAssignedByUser($assigned_by_user) {
        $this->assigned_by_user = $assigned_by_user;
    }

    function getAssignedByUser() {
        return $this->assigned_by_user;
    }

    function setAssignedByInitials($assigned_by_initials) {
        $this->assigned_by_initials = $assigned_by_initials;
    }

    function getAssignedByInitials() {
        return $this->assigned_by_initials;
    }
}

?>
