<?php

/**
 * Lottery Application - Model to represent a lottery re-application
 * for continuing students.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package hms
 */

PHPWS_Core::initModClass('hms', 'HousingApplication.php');

class LotteryApplication extends HousingApplication {

    public $magic_winner        = 0;
    public $invite_expires_on   = NULL;

    public $waiting_list_hide   = 0;

    // This variable is set to the name of the special interest group
    // *IF AND ONLY IF* and student is approved for that group
    public $special_interest	= NULL;

    // These are preferences input by the student on the application form.
    // They don't necessarily mean a student has been approved by a group.
    public $sorority_pref;
    public $tf_pref;
    public $wg_pref;
    public $honors_pref;
    public $rlc_interest;

    public function __construct($id = 0, $term = NULL, $banner_id = NULL, $username = NULL, $gender = NULL, $student_type = NULL, $application_term = NULL, $cell_phone = NULL, $meal_plan = NULL, $physical_disability = NULL, $psych_disability = NULL, $gender_need = NULL, $medical_need = NULL, $international = NULL, $specialInterest = NULL, $magicWinner = 0, $sororityPref = NULL, $tfPref = NULL, $wgPref = NULL, $honorsPref = NULL, $rlcInterest = NULL)
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

        $this->application_type = 'lottery';

        parent::__construct($term, $banner_id, $username, $gender, $student_type, $application_term, $cell_phone, $meal_plan, $physical_disability, $psych_disability, $gender_need, $medical_need, $international);

        $this->special_interest = $specialInterest;
        $this->magic_winner = $magicWinner;

        $this->sorority_pref  = $sororityPref;
        $this->tf_pref        = $tfPref;
        $this->wg_pref        = $wgPref;
        $this->honors_pref    = $honorsPref;
        $this->rlc_interest   = $rlcInterest;
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
        }else{
            $result = $db->saveObject($this);
        }

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
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
        if(!is_null($this->invite_expires_on) && $this->invite_expires_on >= time()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @return integer position of this LotteryApplication in the on-campus waiting list.
     */
    public function getWaitListPosition()
    {
        $term = $this->getTerm();

        # Get the list of user names still on the waiting list, sorted by ID (first come, first served)
        $sql = "SELECT username FROM hms_new_application JOIN hms_lottery_application ON hms_new_application.id = hms_lottery_application.id
                LEFT OUTER JOIN (SELECT asu_username FROM hms_assignment WHERE hms_assignment.term=$term) as foo ON hms_new_application.username = foo.asu_username
                WHERE foo.asu_username IS NULL
                AND hms_new_application.term = $term
                AND special_interest IS NULL
                AND waiting_list_hide = 0
                ORDER BY application_term DESC, hms_new_application.id ASC";

        $applications = PHPWS_DB::getCol($sql);

        if(PHPWS_Error::logIfError($applications)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($applications->toString());
        }

        $position = array_search($this->getUsername(), $applications);

        if($position === FALSE){
            return 'unknown';
        }

        // Fix the off-by-one indexing
        return $position + 1;
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



        $acceptCmd = CommandFactory::getCommand('AcceptSpecialInterest');
        $acceptCmd->setId($this->id);
        $acceptCmd->setGroup($_REQUEST['group']); // TODO: find a better way of doing this

        $tags['ACTION']     = $acceptCmd->getLink('Accept');

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

        return $tags;
    }

    public function specialInterestPager($group, $term)
    {
        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = new DBPager('hms_new_application', 'LotteryApplication');
        $pager->setModule('hms');
        $pager->addRowTags('specialInterestTags');

        $pager->db->addJoin('left outer', 'hms_new_application', 'hms_lottery_application', 'id', 'id');

        $pager->addWhere('hms_new_application.term', $term);
        $pager->db->addWhere('hms_lottery_application.special_interest', 'NULL');

        if($group == 'honors'){
            $pager->addWhere('hms_lottery_application.honors_pref', 1);
        }else if($group == 'watauga_global'){
            $pager->addWhere('hms_lottery_application.wg_pref', 1);
        }else if($group == 'teaching'){
            $pager->addWhere('hms_lottery_application.tf_pref', 1);
        }else if(substr($group, 0, 8) == 'sorority'){ // starts with 'sorority'
            $pager->addWhere('hms_lottery_application.sorority_pref', $group);
        }else if($group == 'special_needs'){
            $pager->addWhere('hms_new_application.physical_disability', 1, '=', 'OR', 'blah');
            $pager->addWhere('hms_new_application.psych_disability', 1, '=', 'OR', 'blah');
            $pager->addWhere('hms_new_application.medical_need', 1, '=', 'OR', 'blah');
            $pager->addWhere('hms_new_application.gender_need', 1, '=', 'OR', 'blah');
        }else{
            // bad group
            test($group,1);
            throw new InvalidArgumentException('Invalid special interest group specified.');
        }


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
        $pager->db->addWhere('hms_lottery_application.waiting_list_hide', 0);

        // Order by class, then by application ID in order to keep a fixed order
        // This accounts for the 'you are x of y students' message on the student's menu
        $pager->db->addOrder(array('application_term DESC', 'hms_new_application.id ASC'));

        $pager->setModule('hms');
        $pager->setTemplate('admin/lottery_wait_list_pager.tpl');
        $pager->setEmptyMessage('No students found.');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addPageTags(array('TITLE'=>'Re-application Waiting List - ' . Term::toString($term)));
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
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $student = StudentFactory::getStudentByUsername($this->username, $this->term);

        $tags = array();

        $tags['NAME']       = $student->getFulLName();
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
