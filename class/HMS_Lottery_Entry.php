<?php

/**
 * The HMS_Lottery_Entry class
 * Records when students enter the lottery
 *
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class HMS_Lottery_Entry {

    var $id;

    var $asu_username;
    var $term;
    var $created_on;
    var $application_term;
    var $gender;

    # Fields for the student's preferred roommates
    var $roommate1_username;
    var $roommate2_username;
    var $roommate3_username;

    # Fields for the preferred roommates' application terms
    var $roommate1_app_term;
    var $roommate2_app_term;
    var $roommate3_app_term;

    var $phone_number           = NULL;
    var $special_interest       = NULL;

    # Disability flags
    var $physical_disability    = 0;
    var $psych_disability       = 0;
    var $medical_need           = 0;
    var $gender_need            = 0;
    var $magic_winner           = 0;

    # Lottery invite timestamp
    var $invite_expires_on;

    public function HMS_Lottery_Entry($asu_username = NULL, $term = NULL)
    {
        if(isset($asu_username)){
            $this->asu_username = $asu_username;
        }else{
            return;
        }

        $this->term = $term;

        $db = new PHPWS_DB('hms_lottery_entry');
        $db->addWhere('asu_username', $this->asu_username);
        $db->addWhere('term', $this->term);
        $result = $db->loadObject($this);
        if(!$result || PHPWS_Error::logIfError($result)){
            $this->id = 0;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_lottery_entry');

        if(!$this->id || is_null($this->id)){
            $this->created_on = mktime();
        }

        $result = $db->saveObject($this);
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }
        return true;
    }

    public function delete()
    {
        $db = new PHPWS_DB('hms_lottery_entry');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)){
            return $result;
        }
        return TRUE;
    }

    /*
     * Create and add a new entry to the database while validating the input.
     *
     * @param string asu_username - The username of the student to add to the lottery
     * @param int application_term - A valid application term
     * @param array roommates - Array of roommates to invite with the student
     * @param int term - A valid HMS_Term
     * @param boolean physical_disability
     * @param boolean psych_disability
     * @param boolean medical_need
     * @param boolean gender_need
     *
     * @return boolean success - Returns true or error message
     */
    public function add_entry($asu_username, $physical_disability = false, 
                        $psych_disability = false, $medical_need = false, 
                        $gender_need = false, $term = null, $application_term = null)
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');
        $result = HMS_Lottery_Entry::check_for_entry($asu_username, $term);
        
        if($result != FALSE && !PEAR::isError($result)){
            return 'Student is already entered in the lottery.';
        }
        
        if(is_null($application_term))
            $application_term = HMS_SOAP::get_application_term($asu_username);

        if(!isset($application_term) || is_null($application_term)){
            return 'Application term is required.';
        }

        $gender = HMS_SOAP::get_gender($asu_username, TRUE);
        
        if($gender === FALSE || !isset($gender) || is_null($gender)){
            return 'Failed to look up the student\'s gender.';
        }

        $entry = &new HMS_Lottery_Entry();

        $entry->asu_username     = $asu_username;
        $entry->term             = is_numeric($term) ? $term : PHPWS_Settings::get('hms', 'lottery_term');
        $entry->application_term = $application_term;
        $entry->gender           = $gender;

        $entry->physical_disability = $physical_disability ? 1 : 0;
        $entry->psych_disability    = $psych_disability    ? 1 : 0;
        $entry->medical_need        = $medical_need        ? 1 : 0;
        $entry->gender_need         = $gender_need         ? 1 : 0;

        $result = $entry->save();

        if(!$result){
            return 'Error saving entry.';
        }

        return true;
    }

    public function parse_entry($request)
    {
        if(isset($_REQUEST['asu_username']) && strlen($_REQUEST['asu_username']) > 0){
            $physical_disability = isset($_REQUEST['physical_disability']) ? true : false;
            $psych_disability    = isset($_REQUEST['psych_disability'])    ? true : false;
            $medical_need        = isset($_REQUEST['medical_need'])        ? true : false;
            $gender_need         = isset($_REQUEST['gender_need'])         ? true : false;
            $result = HMS_Lottery_Entry::add_entry($_REQUEST['asu_username'], $physical_disability, $psych_disability, $medical_need, $gender_need);
            
            return $result;
        }

        return 'You must provide the ASU Username of the student to add to the lottery';
    }

    /*************************
     * Static helper methods *
     *************************/
     
    public function check_for_entry($asu_username, $term, $winning_only = FALSE)
    {
        //test($asu_username);
        $db = &new PHPWS_DB('hms_lottery_entry');
        if(isset($asu_username)){
            $db->addWhere('asu_username', $asu_username, 'ILIKE');
        }

        if(isset($term)){
            $db->addWhere('term', $term);
        }else{
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $db->addWhere('term', HMS_Term::get_current_term());
        }

        # Check for only entries where the student has won
        if($winning_only){
            $db->addWhere('invite_expires_on', mktime(), '>');
        }

        $result = $db->select('row');
        //test($result,1);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result, 'hms', 'check_for_entry');
            return $result;
        }

        if(sizeof($result) > 1){
            return $result;
        }else{
            return FALSE;
        }
    }

    public function get_entry($asu_username, $term)
    {

        $entry = new HMS_Lottery_Entry();

        $db = &new PHPWS_DB('hms_lottery_entry');
        $db->addWhere('asu_username', $asu_username, 'ILIKE');
        $db->addWhere('term', $term);
        $db->setLimit(1);
        $result = $db->loadObject($entry);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return fase;
        }

        return $entry;
    }

    public function get_special_needs_interface()
    {
        $tpl = array();
        if(isset($_REQUEST) && isset($_REQUEST['da_clear'])){
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $lottery_entry = HMS_Lottery_Entry::get_entry($_REQUEST['da_clear'], PHPWS_Settings::get('hms', 'lottery_term'));
            $lottery_entry->physical_disability = 0;
            $lottery_entry->psych_disability    = 0;
            $lottery_entry->medical_need        = 0;
            $lottery_entry->gender_need         = 0;
            $result = $lottery_entry->save();

            if(PHPWS_Error::logIfError($result))
            {
                Layout::add('<br /><font color=red>Error clearing special needs</font><br />');
            }
        }

        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('hms_lottery_entry', 'HMS_Lottery_Entry');
        $pager->db->addWhere('term', PHPWS_Settings::get('hms', 'lottery_term'));
        $pager->db->addWhere('physical_disability', 1, '=', 'or', 'special_needs');
        $pager->db->addWhere('psych_disability', 1, '=', 'or', 'special_needs');
        $pager->db->addWhere('medical_need', 1, '=', 'or', 'special_needs');
        $pager->db->addWhere('gender_need', 1, '=', 'or', 'special_needs');
        $pager->setModule('hms');
        $pager->setTemplate('admin/special_needs.tpl');
        $pager->setEmptyMessage('No Students Found.');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('get_row_tags');

        return $pager->get();
    }

    public function get_row_tags(){
        PHPWS_Core::initModClass('hms', 'HMS_Student.php');
        $template['ASU_USERNAME']        = HMS_Student::get_link($this->asu_username, true);
        $template['PHYSICAL_DISABILITY'] = $this->physical_disability == 1 ? 'Yes' : 'No';
        $template['PSYCH_DISABILITY']    = $this->psych_disability    == 1 ? 'Yes' : 'No';
        $template['MEDICAL_NEED']        = $this->medical_need        == 1 ? 'Yes' : 'No';
        $template['GENDER_NEED']         = $this->gender_need         == 1 ? 'Yes' : 'No';

        $form = &new PHPWS_Form('clear_disabilities');
        $form->addHidden('da_clear', $this->asu_username);
        $form->addHidden('type',     'lottery');
        $form->addHidden('op',       'view_lottery_needs');
        $form->addSubmit('clear',    'Clear Disabilities');

        $tpl = $form->getTemplate();
        $template = array_merge($template, $tpl);

        return $template;
    }

    public function get_pager_by_group($group_name, $term)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = new DBPager('hms_lottery_entry', 'HMS_Lottery_Entry');
        $pager->db->addWhere('term', $term);
        $pager->db->addWhere('special_interest', $group_name);

        $pager->setModule('hms');
        $pager->setTemplate('admin/special_interest_pager.tpl');
        $pager->setEmptyMessage('No students found.');
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('special_interest_tags');

        return $pager->get();
    }

    public function special_interest_tags()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $tags = array();

        $tags['NAME']       = HMS_SOAP::get_name($this->asu_username);
        $tags['USER']       = $this->asu_username;
        $tags['BANNER_ID']  = HMS_SOAP::get_banner_id($this->asu_username);
        $tags['ACTION']     = PHPWS_Text::secureLink('Remove', 'hms', array('type'=>'lottery', 'op'=>'remove_special_interest', 'asu_username'=>$this->asu_username, 'group'=>$_REQUEST['group']));

        return $tags;
    }

}
?>
