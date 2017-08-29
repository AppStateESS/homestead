<?php

namespace Homestead;

use \Homestead\Exception\DatabaseException;
use \Homestead\Exception\StudentNotFoundException;
use \PHPWS_Error;
use \PHPWS_DB;

class WaitingListApplication extends HousingApplication {

    public $waitlist_reason;
    public $oncampus_reason;
    public $oncampus_other_reason;

    public function __construct($id = 0, $term = NULL, $banner_id = NULL, $username = NULL, $gender = NULL, $student_type = NULL, $application_term = NULL, $cell_phone = NULL, $meal_plan = NULL, $international = NULL, $waitlistReason = NULL, $oncampusReason = NULL, $oncampusOtherReason = NULL)
    {
        /**
         * If the id is non-zero, then we need to load the other member variables
         * of this object from the database
         */
        if($id != 0){
            $this->id = (int)$id;
            $this->load();
            return;
        }

        // Set this application type
        $this->application_type = 'offcampus_waiting_list';

        parent::__construct($term, $banner_id, $username, $gender, $student_type, $application_term, $cell_phone, $meal_plan, $international);

        $this->waitlist_reason = $waitlistReason;
        $this->oncampus_reason = $oncampusReason;
        $this->oncampus_other_reason = $oncampusOtherReason;
    }

    /**
     * Loads the LotteryApplication object with the corresponding id. Requires that $this->id be non-zero.
     */
    protected function load()
    {
        if($this->id == 0){
            return;
        }

        # Load the core application data using the parent class
        if(!parent::load()){
            return false;
        }

        # Load the application-specific data
        $db = new PHPWS_DB('hms_waitlist_application');

        if(PHPWS_Error::logIfError($db->loadObject($this))){
            $this->id = 0;
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    /**
     * Saves this object
     */
    public function save()
    {
        $is_new = $this->getId() == 0 ? true : false;

        # Save the core application data using the parent class
        if(!parent::save()){
            return false;
        }

        # Save the application-specific data
        $db = new PHPWS_DB('hms_waitlist_application');

        /* If this is a new object, call saveObject with the third parameter as 'false' so
         * the database class will insert the object with the ID set by the parent::save() call.
         * Otherwise, call save object as normal so that the database class will detect the ID and
         * update the object.
         */
        if($is_new){
            $result = $db->saveObject($this, false, false);
        }else{
            $result = $db->saveObject($this);
        }

        if(PHPWS_Error::logIfError($result)){
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_waitlist_application');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)){
            return $result;
        }

        if(!parent::delete()){
            return false;
        }

        return TRUE;
    }

    public function waitingListTags()
    {
        //test($this,1);

        $tags = array();

        try{
            $student = StudentFactory::getStudentByUsername($this->username, $this->term);
        }catch(StudentNotFoundException $e){
            $tags['NAME'] = 'UNKNOWN';
            $tags['USER']       = $this->username;
            $tags['BANNER_ID']  = $student->getBannerId();
            return $tags;
        }

        $tags['NAME']       = $student->getProfileLink();
        $tags['USER']       = $this->username;
        $tags['BANNER_ID']  = $student->getBannerId();
        $tags['CLASS']      = $student->getPrintableClass();

        if(isset($this->cell_phone) && !is_null($this->cell_phone) && $this->cell_phone != ''){
            $tags['PHONE']      = '('.substr($this->cell_phone, 0, 3).')';
            $tags['PHONE']      .= substr($this->cell_phone, 3, 3);
            $tags['PHONE']      .= '-'.substr($this->cell_phone, 6, 4);
        }

        $tags['GENDER']     = $student->getPrintableGender();

        $tags['APP_DATE']       = date("m/j/Y g:ia", $this->getCreatedOn());

        // TODO.. fix these - they should actually instanciate the command objects
        $assign_link = \PHPWS_Text::secureLink('[Assign]','hms', array('module'=>'hms', 'action'=>'ShowAssignStudent', 'username'=>$this->username));
        $remove_link = \PHPWS_Text::secureLink('[Remove]','hms', array('module'=>'hms', 'action'=>'OpenWaitingListRemove', 'username'=>$this->username));
        $tags['ACTION']     = "$assign_link $remove_link";

        return $tags;
    }

    public function waitingListCsvTags()
    {
        $tags = array();

        try{
            $student = StudentFactory::getStudentByUsername($this->username, $this->term);
        }catch(StudentNotFoundException $e){
            $tags['NAME'] = 'UNKNOWN';
            $tags['USER']       = $this->username;
            $tags['BANNER_ID']  = $student->getBannerId();
            return $tags;
        }

        $tags['NAME']       = $student->getFulLName();
        $tags['USER']       = $this->username;
        $tags['BANNER_ID']  = $student->getBannerId();
        $tags['CLASS']      = $student->getPrintableClass();
        $tags['GENDER']     = $student->getPrintableGender();
        $tags['APP_DATE']   = date("m/j/Y g:ia", $this->getCreatedOn());

        if(isset($this->cell_phone) && !is_null($this->cell_phone) && $this->cell_phone != ''){
            $tags['PHONE']      = '('.substr($this->cell_phone, 0, 3).')';
            $tags['PHONE']      .= substr($this->cell_phone, 3, 3);
            $tags['PHONE']      .= '-'.substr($this->cell_phone, 6, 4);
        }

        $tags['WAITINGLIST_REASON'] = $this->waitlist_reason;
        $tags['ONCAMPUS_REASON'] = $this->oncampus_reason;
        $tags['ONCAMPUS_OTHER_REASON'] = $this->oncampus_other_reason;

        return $tags;
    }

    /*********************
     *  Static functions *
     */

    public static function waitingListPager()
    {
        \PHPWS_Core::initCoreClass('DBPager.php');

        $term = \PHPWS_Settings::get('hms', 'lottery_term');

        $pager = new DBPager('hms_new_application', 'WaitingListApplication');

        $pager->db->addJoin('LEFT', 'hms_new_application', 'hms_waitlist_application', 'id', 'id');
        $pager->db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_assignment', 'username', 'asu_username AND hms_new_application.term = hms_assignment.term');

        $pager->joinResult('id', 'hms_waitlist_application', 'id', 'waitlist_reason', 'waitlist_reason');
        $pager->joinResult('id', 'hms_waitlist_application', 'id', 'oncampus_reason', 'oncampus_reason');
        $pager->joinResult('id', 'hms_waitlist_application', 'id', 'oncampus_other_reason', 'oncampus_other_reason');

        $pager->db->addWhere('hms_assignment.asu_username', 'NULL');
        $pager->db->addWhere('hms_new_application.term', $term);
        $pager->db->addWhere('hms_new_application.application_type', 'offcampus_waiting_list');
        $pager->db->addWhere('hms_new_application.cancelled', 0);

        $pager->db->addOrder('hms_new_application.created_on ASC');

        //$query = "select username from hms_new_application UNION ALL select asu_username from hms_assignment";
        //$pager->db->setSQLQuery($query);

        $pager->setModule('hms');
        $pager->setTemplate('admin/lottery_wait_list_pager.tpl');
        $pager->setEmptyMessage('No students found.');
        $pager->addPageTags(array('TITLE'=>'Open Waiting List - ' . Term::toString($term)));
        $pager->addRowTags('waitingListTags');
        $pager->setReportRow('waitingListCsvTags');
        $pager->setSearch('hms_new_application.username', 'hms_new_application.banner_id');

        return $pager->get();
    }

}
