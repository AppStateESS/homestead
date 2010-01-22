<?php

PHPWS_Core::initModClass('hms', 'HousingApplication.php');

class LotteryApplication extends HousingApplication {

    # Fields for the student's preferred roommates
    public $roommate1_username;
    public $roommate2_username;
    public $roommate3_username;

    # Fields for the preferred roommates' application terms
    public $roommate1_app_term;
    public $roommate2_app_term;
    public $roommate3_app_term;

    public $special_interest	= NULL;
    public $magic_winner        = 0;
    public $invite_expires_on   = NULL;

    public $waiting_list_hide   = 0;

    public function __construct($id = 0, $term = NULL, $banner_id = NULL, $username = NULL, $gender = NULL, $student_type = NULL, $application_term = NULL, $cell_phone = NULL, $meal_plan = NULL, $physical_disability = NULL, $psych_disability = NULL, $gender_need = NULL, $medical_need = NULL, Array $roommates = NULL, $specialInterest = NULL, $magicWinner = 0)
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

        parent::__construct($term, $banner_id, $username, $gender, $student_type, $application_term, $cell_phone, $meal_plan, $physical_disability, $psych_disability, $gender_need, $medical_need);
        
        if(isset($roommates[0])){
            $this->roommate1_username = $roommates[0]->getUsername();
            $this->roommate1_app_term = $roommates[0]->getApplicationTerm();
        }
        
        if(isset($roommates[1])){
            $this->roommate2_username = $roommates[1]->getUsername();
            $this->roommate2_app_term = $roommates[1]->getApplicationTerm();
        }
        
        if(isset($roommates[2])){
            $this->roommate3_username = $roommates[2]->getUsername();
            $this->roommate3_app_term = $roommates[2]->getApplicationTerm();
        }
        
        $this->special_interest = $specialInterest;
        $this->magic_winner = $magicWinner;
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
        $db = new PHPWS_DB('hms_lottery_application');

        if(PHPWS_Error::logIfError($db->loadObject($this))){
            $this->id = 0;
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
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
        $db = new PHPWS_DB('hms_lottery_application');

        /* If this is a new object, call saveObject with the third parameter as 'false' so
         * the database class will insert the object with the ID set by the parent::save() call.
         * Otherwise, call save object as normal so that the database class will detect the ID and
         * update the object.
         */
        if($is_new){
            $result = $db->saveObject($this, false, false);
            if(PHPWS_Error::logIfError($result)){
                PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
                throw new DatabaseException($result->toString());
            }
        }else{
            $result = $db->saveObject($this);
            if(PHPWS_Error::logIfError($result)){
                PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
                throw new DatabaseException($result->toString());
            }
        }

        return true;
    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_lottery_application');
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

    public function isWinner()
    {
        if($this->magic_winner == 1 || (!is_null($this->invite_expires_on) && $this->invite_expires_on >= time())){
            return true;
        }else{
            return false;
        }
    }

    public function getRowTags(){
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $student = StudentFactory::getStudentByUsername($this->username, $this->term);
        $template['ASU_USERNAME']        = $student->getFullNameProfileLink();
        $template['PHYSICAL_DISABILITY'] = $this->physical_disability == 1 ? 'Yes' : 'No';
        $template['PSYCH_DISABILITY']    = $this->psych_disability    == 1 ? 'Yes' : 'No';
        $template['MEDICAL_NEED']        = $this->medical_need        == 1 ? 'Yes' : 'No';
        $template['GENDER_NEED']         = $this->gender_need         == 1 ? 'Yes' : 'No';

        $form = new PHPWS_Form('clear_disabilities');
        $form->addHidden('da_clear', $this->asu_username);
        $form->addHidden('type',     'lottery');
        $form->addHidden('op',       'view_lottery_needs');
        $form->addSubmit('clear',    'Clear Disabilities');

        $tpl = $form->getTemplate();
        $template = array_merge($template, $tpl);

        return $template;
    }

    public function specialInterestTags()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $tags = array();

        $tags['NAME']       = $student->getFullNameProfileLink();
        $tags['USER']       = $this->username;
        $tags['BANNER_ID']  = $student->getBannerId();
        $tags['ROOMMATE1']  = StudentFactory::getStudentByUsername($this->roommate1_username, $this->term)->getProfileLink();
        $tags['ROOMMATE2']  = StudentFactory::getStudentByUsername($this->roommate2_username, $this->term)->getProfileLink();
        $tags['ROOMMATE3']  = StudentFactory::getStudentByUsername($this->roommate3_username, $this->term)->getProfileLink();
        $tags['ACTION']     = PHPWS_Text::secureLink('Remove', 'hms', array('action'=>'RemoveSpecialInterest', 'asu_username'=>$this->username, 'group'=>$this->special_interest, 'id'=>$this->id));

        return $tags;
    }

    public function specialInterestCsvRow()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $student = StudentFactory::getStudentByUsername($this->username, $this->term);
        $row = array();

        $tags['NAME']       = $student->getFullName();
        $tags['USER']       = $this->username;
        $tags['BANNER_ID']  = $student->getBannerId();
        $tags['ROOMMATE1']  = StudentFactory::getStudentByUsername($this->roommate1_username, $this->term)->getName();
        $tags['ROOMMATE2']  = StudentFactory::getStudentByUsername($this->roommate2_username, $this->term)->getName();
        $tags['ROOMMATE3']  = StudentFactory::getStudentByUsername($this->roommate3_username, $this->term)->getName();

        return $tags;
    }

    public function specialInterestPager($group, $term)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('hms_lottery_application', 'LotteryApplication');
        $pager->setModule('hms');
        $pager->addRowTags('specialInterestTags');

        $pager->db->addColumn('hms_new_application.*');
        $pager->db->addJoin('left outer', 'hms_lottery_application', 'hms_new_application', 'id', 'id');
        $pager->addWhere('hms_new_application.term', $term);
        $pager->addWhere('hms_lottery_application.special_interest', $group);

        $pager->setTemplate('admin/special_interest_pager.tpl');
        $pager->setEmptyMessage('No students found.');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->setReportRow('specialInterestCsvRow');

        return $pager->get();
    }

    public function waitingListPager()
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $term = PHPWS_Settings::get('hms', 'lottery_term');

        $pager = new DBPager('hms_new_application', 'LotteryApplication');
        $pager->db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_lottery_application', 'id', 'id');
        $pager->db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_assignment', 'username', 'asu_username AND hms_new_application.term = hms_assignment.term');
        $pager->db->addWhere('hms_assignment.asu_username', 'NULL');
        $pager->db->addWhere('hms_new_application.term', $term);
        $pager->db->addWhere('hms_lottery_application.special_interest', 'NULL');
        $pager->db->addWhere('physical_disability', 0);
        $pager->db->addWhere('psych_disability', 0);
        $pager->db->addWhere('medical_need', 0);
        $pager->db->addWhere('gender_need', 0);
        $pager->db->addWhere('hms_lottery_application.waiting_list_hide', 0);

        $pager->setModule('hms');
        $pager->setTemplate('admin/lottery_wait_list_pager.tpl');
        $pager->setEmptyMessage('No students found.');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('waitingListTags');
        $pager->setReportRow('waitingListCsvTags');
        $pager->setSearch('hms_new_application.username', 'hms_new_application.banner_id');

        return $pager->get();
    }

    public function waitingListTags()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $tags = array();

        $tags['NAME']       = $student->getFullNameProfileLink();
        $tags['USER']       = $this->username;
        $tags['BANNER_ID']  = $student->getBannerId();
        $tags['CLASS']      = $student->getPrintableClass();
        
        if(isset($this->cell_phone) && !is_null($this->cell_phone) && $this->cell_phone != ''){
            $tags['PHONE']      = '('.substr($this->cell_phone, 0, 3).')';
            $tags['PHONE']      .= substr($this->cell_phone, 3, 3);
            $tags['PHONE']      .= '-'.substr($this->cell_phone, 6, 4);
        }

        $tags['GENDER']     = $student->getPrintableGender();


        $assign_link = PHPWS_Text::secureLink('[Assign]','hms', array('module'=>'hms', 'action'=>'ShowAssignStudent', 'username'=>$this->username)); 
        $remove_link = PHPWS_Text::secureLink('[Remove]','hms', array('module'=>'hms', 'action'=>'WaitingListRemove', 'username'=>$this->username));
        $tags['ACTION']     = "$assign_link $remove_link";

        return $tags;
    }

    public function waitingListCsvTags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $tags = array();

        $tags['NAME']       = $stduent->getFullNameProfileLink();
        $tags['USER']       = $this->username;
        $tags['BANNER_ID']  = $student->getBannerId();
        $tags['CLASS']      = $student->getPrintableClass();
        $tags['GENDER']     = $student->getPrintableGender();

        if(isset($this->cell_phone) && !is_null($this->cell_phone) && $this->cell_phone != ''){
            $tags['PHONE']      = '('.substr($this->cell_phone, 0, 3).')';
            $tags['PHONE']      .= substr($this->cell_phone, 3, 3);
            $tags['PHONE']      .= '-'.substr($this->cell_phone, 6, 4);
        }

        return $tags;
    }
}
?>
