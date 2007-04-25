<?php

/**
 * The HMS_RLC_Assignment class
 *
 */

class HMS_RLC_Assignment{

    var $id;

    var $asu_username;
    var $rlc_id;
    var $course_ok;
    var $assigned_by_user;
    var $assigned_by_initals;

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

        if(PEAR::isErrpr($result)) {
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

        $tags = array();

        $tags['TITLE'] = "View Final RLC Assignments";
/*        $tags['PRINT_RECORDS'] = "// TODO: Print Records";
        $tags['EXPORT'] = "// TODO: Export Records";*/

        $pager = &new DBPager('hms_learning_community_assignment','HMS_RLC_Assignment');
        $pager->db->addOrder('asu_username','ASC');
        $pager->db->addColumn('hms_learning_community_assignment.*');
        $pager->db->addColumn('hms_learning_community_applications.required_course', NULL, 'course_ok');

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
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');

        $rlc_list = HMS_RLC_Application::getRLCList();

        $tags = array();
        $asuid = $this->getAsuUsername();
        
        $tags['NAME']      = HMS_SOAP::get_full_name_inverted($asuid);
        $tags['FINAL_RLC'] = $rlc_list[$this->getRlcId()];
        $tags['COURSE_OK'] = $this->getCourseOk() == 1 ? 'Y' : 'N';
//        $tags['ROOMMATE']  = TODO: Roommate Stuff
        $tags['ADDRESS']   = HMS_SOAP::get_address_line($asuid);
        $tags['PHONE']     = HMS_SOAP::get_phone_number($asuid);
        $tags['EMAIL']     = "$asuid@appstate.edu";

        return $tags;
    }

    function setId($id) {
        $this->id = $id;
    }

    function getId($id) {
        return $this->id;
    }

    function setAsuUsername($asu_username) {
        $this->asu_username = $asu_username;
    }

    function getAsuUsername() {
        return $this->asu_username;
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
