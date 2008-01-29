<?php

/**
 * The HMS Deadlines class.
 * Handles getting/saving deadlines.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Deadlines {

    var $id; // This is only here to make loadObject and saveObject work as expected
    var $term;

    var $student_login_begin_timestamp;
    var $student_login_end_timestamp;
    var $submit_application_begin_timestamp;
    var $submit_application_end_timestamp;
    var $edit_application_end_timestamp;
    var $edit_profile_begin_timestamp;
    var $edit_profile_end_timestamp;
    var $search_profiles_begin_timestamp;
    var $search_profiles_end_timestamp;
    var $submit_rlc_application_end_timestamp;
    var $view_assignment_begin_timestamp;
    var $view_assignment_end_timestamp;

    var $updated_by;
    var $updated_on;

    function HMS_Deadlines($term = NULL){
        
        if(!isset($term)){
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $term = HMS_Term::get_current_term();
        }

        $this->term = $term;
        $db = new PHPWS_DB('hms_deadlines');
        $db->addWhere('term', $this->term);
        $result = $db->loadObject($this);
        if(!$result  || PHPWS_Error::logIfError($result)){
            $this->id = 0;
        }
    }

    /**
     * Function to be called statically
     * Returns an associative array with each deadline name
     * (column name) as a key.
     * Can be called statically.
     */
    function get_deadlines($term = NULL)
    {

        if(!isset($term)){
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $term = HMS_Ter::get_current_term();
        }

        $db = &new PHPWS_DB('hms_deadlines');

        # These are necessary to avoid pulling the 
        # id, term, etc as deadlines
        $db->addColumn('student_login_begin_timestamp');
        $db->addColumn('student_login_end_timestamp');
        $db->addColumn('submit_application_begin_timestamp');
        $db->addColumn('submit_application_end_timestamp');
        $db->addColumn('edit_application_end_timestamp');
        $db->addColumn('edit_profile_begin_timestamp');
        $db->addColumn('edit_profile_end_timestamp');
        $db->addColumn('search_profiles_begin_timestamp');
        $db->addColumn('search_profiles_end_timestamp');
        $db->addColumn('submit_rlc_application_end_timestamp');
        $db->addColumn('view_assignment_begin_timestamp');
        $db->addColumn('view_assignment_end_timestamp');
        
        $db->addWhere('term', $term);
        $deadlines = $db->select('row');

        if(PEAR::isError($deadlines)){
            PHPWS_Error::log($deadlines);
        }

        return $deadlines;
    }

    /**
     * Checks if a given deadlines has passed yet.
     * Expects a deadline name as defined by the deadline table.
     * 
     * Optionally takes a deadlines array (like that returned from 'get_deadlines()' to use
     * so you can call 'get_deadlines()' once and then pass the result to this function for repeated checks.
     * (avoids repetative database queries).
     * 
     * Returns TRUE if the deadline has passed, false otherwise.
     * Returns a PEAR error object in case of a DB error.
     * Can be called statically.
     */
    function check_deadline_past($deadline_name, $deadlines = NULL, $term = null)
    {

        if(!isset($term)){
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $term = HMS_Term::get_current_term();
        }

        if(!isset($deadlines)){
            $deadlines = HMS_Deadlines::get_deadlines($term);
        }

        if (PEAR::isError($deadlines)){
            return $deadlines;
        }

        # TODO: Check if $deadline_name is valid here??

        if($deadlines[$deadline_name] <= mktime()){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Checks if the current time is within two deadlines.
     * Expects two deadline names as defined by the deadline table.
     * 
     * Optionally takes a deadlines array (like that returned from 'get_deadlines()' to use
     * so you can call 'get_deadlines()' once and then pass the result to this function for repeated checks.
     * (avoids repetative database queries).
     * 
     * Returns TRUE if the current time is within the start and end deadlines, FALSE otherwise.
     * Returns a PEAR error object in case of a DB error.
     *
     * Can be called statically.
     */
    function check_within_deadlines($deadline_name_start, $deadline_name_end, $deadlines = NULL, $term = NULL)
    {
        if(!isset($term)){
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $term = HMS_Term::get_current_term();
        }

        # If we weren't passed in the deadlines, then get them now
        if(!isset($deadlines)){
            $deadlines = HMS_Deadlines::get_deadlines();
        }

        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        # TODO: Check if deadline names are valid here??
        # use 'array_key_exists'

        $curr_time = mktime();

        if($deadlines[$deadline_name_start] <= $curr_time && $deadlines[$deadline_name_end] >= $curr_time){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Returns a deadline as text (i.e. 'Wednesday, September 12th, 2007').
     * 
     * Optionally takes a deadlines array (like that returned from 'get_deadlines()' to use
     * so you can call 'get_deadlines()' once and then pass the result to this function for repeated checks.
     * (avoids repetative database queries).
     *
     * Also optionally takes a date format, so you can get the date back in any format you'd like.
     * 
     */
    function get_deadline_as_date($deadline_name, $deadlines = NULL, $date_format = NULL, $term = NULL)
    {

        if(!isset($term)){
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');
            $term = HMS_Term::get_current_term();
        }
        
        # If we weren't passed in the deadlines, then get them now
        if(!isset($deadlines)){
            $deadlines = HMS_Deadlines::get_deadlines();
        }

        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        # TODO: check if deadline names are valid here??

        if(isset($date_format)){
            return date($date_format, $deadlines[$deadline_name]);
        }else{
            return date('l, F jS, Y', $deadlines[$deadline_name]);
        }
    }

    /**
     * Function for saving deadlines. Uses the member variables.
     * All deadlines (member vars) must be set to something before
     * calling this function. Returns TRUE on success, or PEAR DB
     * error otherwise.
     */
    function save()
    {
        if(!(Current_User::authorized('hms', 'edit_deadlines') || Current_User::authorized('hms', 'admin'))) {
            exit('You do not have permission to edit deadlines.');
        }

        $this->updated_on = mktime();
        $this->updated_by = Current_User::getId();
        
        $db = new PHPWS_DB('hms_deadlines');
        $result = $db->saveObject($this);
        if(!$result || PHPWS_Error::logIfError($result)){
            return false;
        }

        return true;
    }

    function main(){
        
        switch($_REQUEST['op']){
            case 'show_edit_deadlines':
                return HMS_Deadlines::show_edit_deadlines();
                break;
            case 'save_deadlines':
                return HMS_Deadlines::save_deadlines();
                break;
            default:
                echo "Unknown deadlines op: {$_REQUEST['op']}";
                break;
        }
    }

    /*********************
     * Static UI Methods *
     *********************/

    function show_edit_deadlines($success_message = NULL, $error_message = NULL)
    {
        if(!(Current_User::allow('hms', 'edit_deadlines') || Current_User::allow('hms', 'admin'))) {
            exit('You do not have permission to edit deadlines.');
        }
       
        PHPWS_Core::initModClass('hms', 'HMS_Term.php'); 
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        
        $deadlines = new HMS_Deadlines(HMS_Term::get_selected_term());
        if(!$deadlines || PHPWS_Error::logIfError($deadlines)){
            $tpl['ERROR_MSG'] = 'There was an error loading the deadlines.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/deadlines.tpl');
        }

        $months = HMS_Util::get_months(); 
        $days   = HMS_Util::get_days();
        $years  = HMS_Util::get_years_2yr();

        $deadlines_array = HMS_Deadlines::get_deadlines(HMS_Term::get_selected_term());

        # Check if the year of each deadline is in the years array
        foreach($deadlines_array as $deadline){
            $year = date('Y', $deadline);
            if(!array_key_exists($year,$years)){
                # The year doesn't exist, so add it
                $years[$year] = $year;
            }
        }
        
        $form = &new PHPWS_Form;
        $form->addDropBox('student_login_begin_month', $months);
        $form->addDropBox('student_login_begin_day', $days);
        $form->addDropBox('student_login_begin_year', $years);
        
        $form->addDropBox('student_login_end_month', $months);
        $form->addDropBox('student_login_end_day', $days);
        $form->addDropBox('student_login_end_year', $years);
    
        $form->addDropBox('submit_application_begin_month', $months);
        $form->addDropBox('submit_application_begin_day', $days);
        $form->addDropBox('submit_application_begin_year', $years);
    
        $form->addDropBox('submit_application_end_month', $months);
        $form->addDropBox('submit_application_end_day', $days);
        $form->addDropBox('submit_application_end_year', $years);

        $form->addDropBox('edit_application_end_month', $months);
        $form->addDropBox('edit_application_end_day', $days);
        $form->addDropBox('edit_application_end_year', $years);

        $form->addDropBox('edit_profile_begin_month', $months);
        $form->addDropBox('edit_profile_begin_day', $days);
        $form->addDropBox('edit_profile_begin_year', $years);

        $form->addDropBox('edit_profile_end_month', $months);
        $form->addDropBox('edit_profile_end_day', $days);
        $form->addDropBox('edit_profile_end_year', $years);

        $form->addDropBox('search_profiles_begin_month', $months);
        $form->addDropBox('search_profiles_begin_day', $days);
        $form->addDropBox('search_profiles_begin_year', $years);
    
        $form->addDropBox('search_profiles_end_month', $months);
        $form->addDropBox('search_profiles_end_day', $days);
        $form->addDropBox('search_profiles_end_year', $years);
   
        $form->addDropBox('submit_rlc_application_end_month', $months);
        $form->addDropBox('submit_rlc_application_end_day', $days);
        $form->addDropBox('submit_rlc_application_end_year', $years);

        $form->addDropBox('view_assignment_begin_month', $months);
        $form->addDropBox('view_assignment_begin_day', $days);
        $form->addDropBox('view_assignment_begin_year', $years);
       
        $form->addDropBox('view_assignment_end_month', $months);
        $form->addDropBox('view_assignment_end_day', $days);
        $form->addDropBox('view_assignment_end_year', $years);
    
        $form->setMatch('student_login_begin_day', date('j',$deadlines->student_login_begin_timestamp));
        $form->setMatch('student_login_begin_month', date('n',$deadlines->student_login_begin_timestamp));
        $form->setMatch('student_login_begin_year', date('Y',$deadlines->student_login_begin_timestamp));
        
        $form->setMatch('student_login_end_day', date('j',$deadlines->student_login_end_timestamp));
        $form->setMatch('student_login_end_month', date('n',$deadlines->student_login_end_timestamp));
        $form->setMatch('student_login_end_year', date('Y',$deadlines->student_login_end_timestamp));
        
        $form->setMatch('submit_application_begin_day', date('j', $deadlines->submit_application_begin_timestamp));
        $form->setMatch('submit_application_begin_month', date('n', $deadlines->submit_application_begin_timestamp));
        $form->setMatch('submit_application_begin_year', date('Y', $deadlines->submit_application_begin_timestamp));

        $form->setMatch('submit_application_end_day', date('j', $deadlines->submit_application_end_timestamp));
        $form->setMatch('submit_application_end_month', date('n', $deadlines->submit_application_end_timestamp));
        $form->setMatch('submit_application_end_year', date('Y', $deadlines->submit_application_end_timestamp));
       
        $form->setMatch('edit_application_end_day', date('j', $deadlines->edit_application_end_timestamp));
        $form->setMatch('edit_application_end_month', date('n', $deadlines->edit_application_end_timestamp));
        $form->setMatch('edit_application_end_year', date('Y', $deadlines->edit_application_end_timestamp));
        
        $form->setMatch('edit_profile_begin_day', date('j', $deadlines->edit_profile_begin_timestamp));
        $form->setMatch('edit_profile_begin_month', date('n', $deadlines->edit_profile_begin_timestamp));
        $form->setMatch('edit_profile_begin_year', date('Y', $deadlines->edit_profile_begin_timestamp));
        $form->setMatch('edit_profile_end_day', date('j', $deadlines->edit_profile_end_timestamp));
        $form->setMatch('edit_profile_end_month', date('n', $deadlines->edit_profile_end_timestamp));
        $form->setMatch('edit_profile_end_year', date('Y', $deadlines->edit_profile_end_timestamp));
        
        $form->setMatch('search_profiles_begin_day', date('j', $deadlines->search_profiles_begin_timestamp));
        $form->setMatch('search_profiles_begin_month', date('n', $deadlines->search_profiles_begin_timestamp));
        $form->setMatch('search_profiles_begin_year', date('Y', $deadlines->search_profiles_begin_timestamp));
        $form->setMatch('search_profiles_end_day', date('j', $deadlines->search_profiles_end_timestamp));
        $form->setMatch('search_profiles_end_month', date('n', $deadlines->search_profiles_end_timestamp));
        $form->setMatch('search_profiles_end_year', date('Y', $deadlines->search_profiles_end_timestamp));
        
        $form->setMatch('submit_rlc_application_end_day', date('j', $deadlines->submit_rlc_application_end_timestamp));
        $form->setMatch('submit_rlc_application_end_month', date('n', $deadlines->submit_rlc_application_end_timestamp));
        $form->setMatch('submit_rlc_application_end_year', date('Y', $deadlines->submit_rlc_application_end_timestamp));
        
        $form->setMatch('view_assignment_begin_day', date('j', $deadlines->view_assignment_begin_timestamp));
        $form->setMatch('view_assignment_begin_month', date('n', $deadlines->view_assignment_begin_timestamp));
        $form->setMatch('view_assignment_begin_year', date('Y', $deadlines->view_assignment_begin_timestamp));
        $form->setMatch('view_assignment_end_day', date('j', $deadlines->view_assignment_end_timestamp));
        $form->setMatch('view_assignment_end_month', date('n', $deadlines->view_assignment_end_timestamp));
        $form->setMatch('view_assignment_end_year', date('Y', $deadlines->view_assignment_end_timestamp));
        
        $form->addHidden('module', 'hms');
        $form->addHidden('type', 'deadlines');
        $form->addHidden('op', 'save_deadlines');
        $form->addHidden('term', $deadlines->term);
        $form->addSubmit('submit', _('Save Deadlines'));
        $tpl = $form->getTemplate();

        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $tpl['TITLE'] = 'Edit Deadlines';
        $tpl['TITLE_CLASS'] = HMS_Util::get_title_class();
        
        if(isset($error_message)) {
            $tpl['ERROR_MSG'] = $error_message;
        }

        if(isset($success_message)){
            $tpl['SUCCESS_MSG'] = $success_message;
        }
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/deadlines.tpl');
    }

    /******************
     * Static Methods *
     ******************/

    function save_deadlines()
    {
        if(!(Current_User::authorized('hms', 'edit_deadlines') || Current_User::authorized('hms', 'admin'))) {
            exit('You do not have permission to edit deadlines.');
        }

        $deadlines = new HMS_Deadlines($_REQUEST['term']);

        $deadlines->student_login_begin_timestamp = mktime(0, 0, 0,
            $_REQUEST['student_login_begin_month'],
            $_REQUEST['student_login_begin_day'],
            $_REQUEST['student_login_begin_year']);
        
        $deadlines->student_login_end_timestamp = mktime(0, 0, 0,
            $_REQUEST['student_login_end_month'],
            $_REQUEST['student_login_end_day'],
            $_REQUEST['student_login_end_year']);
        
        $deadlines->submit_application_begin_timestamp = mktime(0, 0, 0,
            $_REQUEST['submit_application_begin_month'],
            $_REQUEST['submit_application_begin_day'],
            $_REQUEST['submit_application_begin_year']);
        
        $deadlines->submit_application_end_timestamp = mktime(0, 0, 0,
            $_REQUEST['submit_application_end_month'],
            $_REQUEST['submit_application_end_day'],
            $_REQUEST['submit_application_end_year']);

        $deadlines->edit_application_end_timestamp = mktime(0, 0, 0,
            $_REQUEST['edit_application_end_month'],
            $_REQUEST['edit_application_end_day'],
            $_REQUEST['edit_application_end_year']);
        
        $deadlines->edit_profile_begin_timestamp = mktime(0, 0, 0,
            $_REQUEST['edit_profile_begin_month'],
            $_REQUEST['edit_profile_begin_day'],
            $_REQUEST['edit_profile_begin_year']);
        
        $deadlines->edit_profile_end_timestamp = mktime(0, 0, 0,
            $_REQUEST['edit_profile_end_month'],
            $_REQUEST['edit_profile_end_day'],
            $_REQUEST['edit_profile_end_year']);
        
        $deadlines->search_profiles_begin_timestamp = mktime(0, 0, 0,
            $_REQUEST['search_profiles_begin_month'],
            $_REQUEST['search_profiles_begin_day'],
            $_REQUEST['search_profiles_begin_year']);
        
        $deadlines->search_profiles_end_timestamp = mktime(0, 0, 0,
            $_REQUEST['search_profiles_end_month'],
            $_REQUEST['search_profiles_end_day'],
            $_REQUEST['search_profiles_end_year']);
        
        $deadlines->submit_rlc_application_end_timestamp = mktime(0, 0, 0,
            $_REQUEST['submit_rlc_application_end_month'],
            $_REQUEST['submit_rlc_application_end_day'],
            $_REQUEST['submit_rlc_application_end_year']);

        $deadlines->view_assignment_begin_timestamp = mktime(0, 0, 0,
            $_REQUEST['view_assignment_begin_month'],
            $_REQUEST['view_assignment_begin_day'],
            $_REQUEST['view_assignment_begin_year']);

        $deadlines->view_assignment_end_timestamp = mktime(0, 0, 0,
            $_REQUEST['view_assignment_end_month'],
            $_REQUEST['view_assignment_end_day'],
            $_REQUEST['view_assignment_end_year']);

        $result = $deadlines->save();

        if(!$result || PHPWS_Error::logIfError($result)){
            return HMS_Deadlines::show_edit_deadlines(null, 'There was an error saving the deadlines.');
        }else{
            return HMS_Deadlines::show_edit_deadlines('Deadlines updated successfully.');
        }
    }
}

?>
