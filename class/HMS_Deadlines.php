<?php

/**
 * The HMS Deadlines class.
 * Handles getting/saving deadlines.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Deadlines {

    var $slbt = NULL; # student login begin timestamp
    var $slet = NULL; # student login end timestamp
    var $sabt = NULL; # submit application begin timestamp
    var $saet = NULL; # submit application end timestamp
    var $eaet = NULL; # edit application end timestamp
    var $spbt = NULL; # search profiles begin timestamp
    var $spet = NULL; # search profiles end timestamp
    var $sret = NULL; # submit rlc application end timestamp
    var $vabt = NULL; # view assignment begin timestamp
    var $vaet = NULL; # view assignment end timestamp

    /**
     * Function to be called statically
     * Returns an associative array with each deadline name
     * (column name) as a key.
     */
    function get_deadlines()
    {
        $db = &new PHPWS_DB('hms_deadlines');
        $deadlines = $db->select('row');

        if(PEAR::isError($deadlines)){
            PHPWS_Error::log($deadlines);
        }

        return $deadlines;
    }

    /**
     * Checks if a given deadlines has passed yet.
     * Expects a deadline name as defined by the deadline table.
     * Returns TRUE if the deadline has passed, false otherwise.
     * Returns a PEAR error object in case of a DB error.
     */
    function check_deadline_past($deadline_name)
    {

        $deadlines = HMS_Deadlines::get_deadlines();

        if (PEAR::isError($deadlines)){
            return $deadlines;
        }

        # Check if $deadline_name is valid here??

        if($deadlines[$deadline_name] >= mktime()){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Function for saving deadlines. Uses the member variables.
     * All deadlines (member vars) must be set to something before
     * calling this function. Returns TRUE on success, or PEAR DB
     * error otherwise.
     */
    function save_deadlines()
    {
        if(!(Current_User::authorized('hms', 'edit_deadlines') || Current_User::authorized('hms', 'admin'))) {
            exit('You are a bad person that can not edit deadlines.');
        }
        
        $db = &new PHPWS_DB('hms_deadlines');
        $db->addColumn('student_login_begin_timestamp');
        $results = $db->select();
        unset($db);

        $db = &new PHPWS_DB('hms_deadlines');
        $db->addValue('student_login_begin_timestamp', $this->slbt);
        $db->addValue('student_login_end_timestamp', $this->slet);
        $db->addValue('submit_application_begin_timestamp', $this->sabt);
        $db->addValue('submit_application_end_timestamp', $this->saet);
        $db->addValue('edit_application_end_timestamp', $this->eaet);
        $db->addValue('search_profiles_begin_timestamp', $this->spbt);
        $db->addValue('search_profiles_end_timestamp', $this->spet);
        $db->addValue('submit_rlc_application_end_timestamp', $this->sret);
        $db->addValue('view_assignment_begin_timestamp', $this->vabt);
        $db->addValue('view_assignment_end_timestamp', $this->vaet);
        $db->addValue('updated_on',mktime());
        $db->addValue('updated_by', Current_User::getId());

        if($results == NULL) {
            $result = $db->insert();
        } else {
            $result = $db->update();
        }

        if(PEAR::isError($result)) {
            PHPWS_Error::log($result);
            return $error;
        } else {
            return TRUE;
        }
    }

    /*********************************
     * Get Deadline Functions
     * Each function returns the timestamp corresponding
     * to the function name, or a PEAR error objects on DB error.
     *********************************/

    function get_student_login_begin_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['student_login_begin_timestamp'];
    }

    function get_student_login_end_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['student_login_end_timestamp'];
    }

    function get_submit_application_begin_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['submit_application_begin_timestamp'];
    }

    function get_subumit_application_end_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }
        
        return $deadlines['submit_application_end_timestamp'];
    }

    function get_edit_application_end_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['edit_application_end_timestamp'];
    }

    function get_search_profiles_begin_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['search_profiles_begin_timestamp'];
    }

    function get_search_profiles_end_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['search_profiles_end_timestamp'];
    }

    function get_submit_rlc_application_end_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['submit_rlc_application_end_timestamp'];
    }

    function get_view_assignment_begin_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['view_assignment_begin_timestamp'];
    }

    function get_view_assignment_end_timestamp()
    {
        $deadlines = HMS_Deadlines::get_deadlines();
        if(PEAR::isError($deadlines)){
            return $deadlines;
        }

        return $deadlines['view_assignment_end_timestamp'];
    }

    /**
     * Functions to set deadlines.
     * Take a month, day, year and places the resulting
     * timestamp in the corresponding member variable.
     */

    function set_student_login_begin_mdy($month, $day, $year){
        $this->slbt = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_student_login_end_mdy($month, $day, $year){
        $this->slet = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_submit_application_begin_mdy($month, $day, $year){
        $this->sabt = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_submit_application_end_mdy($month, $day, $year){
        $this->saet = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_edit_application_mdy($month, $day, $year){
        $this->eaet = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_search_profiles_begin_mdy($month, $day, $year){
        $this->spbt = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_search_profiles_end_mdy($month, $day, $year){
        $this->spet = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_submit_rlc_application_end_mdy($month, $day, $year){
        $this->sret = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_view_assignment_begin_mdy($month, $day, $year){
        $this->vabt = mktime(0, 0, 0, $month, $day, $year);
    }
    
    function set_view_assignment_end_mdy($month, $day, $year){
        $this->vaet = mktime(0, 0, 0, $month, $day, $year);
    }
    
    /**
     * Function for setting deadline timestamps.
     */ 

    function set_student_login_begin_timestamp($timestamp){
        $this->slbt = $timestamp;
    }
    
    function set_student_login_end_timestamp($timestamp){
        $this->slet = $timestamp;
    }
    
    function set_submit_application_begin_timestamp($timestamp){
        $this->sabt = $timestamp;
    }
    
    function set_submit_application_end_timestamp($timestamp){
        $this->saet = $timestamp;
    }
    
    function set_edit_application_end_timestamp($timestamp){
        $this->eaet = $timestamp;
    }
    
    function set_search_profiles_begin_timestamp($timestamp){
        $this->spbt = $timestamp;
    }
    
    function set_search_profiles_end_timestamp($timestamp){
        $this->spet = $timestamp;
    }
    
    function set_submit_rlc_application_end_timestamp($timestamp){
        $this->sert = $timestamp;
    }
    
    function set_view_assignment_begin_timestamp($timestamp){
        $this->vabt = $timestamp;
    }
    
    function set_view_assignment_end_timestamp($timestamp){
        $this->vaet = $timestamp;
    }
}

?>
