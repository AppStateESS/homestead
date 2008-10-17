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
    var $roommate4_username;

    # Disability flags
    var $physical_disability    = 0;
    var $psych_disability       = 0;
    var $medical_need           = 0;
    var $gender_need            = 0;

    # Lottery invite timestamp
    var $invite_expires_on;

    function HMS_Lottery_Entry($asu_username = NULL, $term = NULL)
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

    function save()
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

    function delete()
    {
        $db = new PHPWS_DB('hms_lottery_entry');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        if(!$result || PHPWS_Error::logIfError($result)){
            return $result;
        }
        return TRUE;
    }

    /*************************
     * Static helper methods *
     *************************/
     
    function check_for_entry($asu_username, $term, $winning_only = FALSE)
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

    function get_entry($asu_username, $term)
    {

        $entry = new HMS_Lottery_Entry();

        $db = &new PHPWS_DB('hms_lottery_entry');
        $db->addWhere('asu_username', $asu_username, 'ILIKE');
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

    function get_special_needs_interface()
    {
        if(isset($_REQUEST) && isset($_REQUEST['da_clear'])){
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $lottery_entry = HMS_Lottery_Entry::get_entry($_REQUEST['da_clear'], HMS_Term::get_current_term());
            $lottery_entry->physical_disability = 0;
            $lottery_entry->psych_disability    = 0;
            $lottery_entry->medical_need        = 0;
            $lottery_entry->gender_need         = 0;
            $lottery_entry->save();
        }
        PHPWS_Core::initCoreClass('DBPager.php');
        $pager = &new DBPager('hms_lottery_entry', 'HMS_Lottery_Entry');
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

    function get_row_tags(){
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
}
?>
