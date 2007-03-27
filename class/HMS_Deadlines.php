<?php

/**
 * The HMS Deadlines class.
 * Handles getting/saving deadlines.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Deadlines {

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
}

?>
