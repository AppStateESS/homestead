<?php

define('HMS_SIDE_STUDENT_NOT_STARTED', -1);
define('HMS_SIDE_STUDENT_MIN',          1);
define('HMS_SIDE_STUDENT_AGREE',        1);
define('HMS_SIDE_STUDENT_APPLY',        2);
define('HMS_SIDE_STUDENT_RLC',          3);
define('HMS_SIDE_STUDENT_PROFILE',      4);
define('HMS_SIDE_STUDENT_ROOMMATE',     5);
define('HMS_SIDE_STUDENT_VERIFY',       6);
define('HMS_SIDE_STUDENT_MAX',          6);

class HMS_Side_Thingie {

    var $step;
    var $curr_timestamp;
    var $steps_text = array(HMS_SIDE_STUDENT_AGREE    => "Terms & Conditions",
                            HMS_SIDE_STUDENT_APPLY    => "Application",
                            HMS_SIDE_STUDENT_RLC      => "Learning Community Application (optional)",
                            HMS_SIDE_STUDENT_PROFILE  => "Roomate Profile (optional)",
                            HMS_SIDE_STUDENT_ROOMMATE => "Choose a Roomate (optional)",
                            HMS_SIDE_STUDENT_VERIFY   => "Verify Status");
    var $steps_styles;
    var $deadlines;
    var $entry_term;

    function HMS_Side_Thingie($step)
    {

        $this->step = $step;

        PHPWS_Core::initModClass('hms','HMS_Application.php');
        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms','HMS_Student_Profile.php');
        PHPWS_Core::initModClass('hms','HMS_Deadlines.php');
        
        # Get the deadlines for future use
        $this->deadlines = &new HMS_Deadlines();

        if(PEAR::isError($this->deadlines)){
            PHPWS_Error::log($this->deadlines,'hms','HMS_Side_Thingie::show()','DB error while looking up deadlines.');
            
            $template = array();
            $template['ERROR'] = "Database error in getting deadlines!";
            $page = PHPWS_Template::process($template, 'hms', 'misc/side_thingie.tpl');
            Layout::add($page, 'hms', 'default');
            return;
        }

        PHPWS_Core::initModClass('hms','HMS_Entry_Term.php');
        $this->entry_term = HMS_Entry_Term::get_entry_term($_SESSION['asu_username']);
    }

    function show()
    {
        
        $this->curr_timestamp = mktime();

        $template = array();
        $template['TITLE'] = _('Application Progress');

        # Check for an application on file, set dates/styles if an application is not found
        $this->set_apply_agree();

        # Check for an RLC application on file, set dates/styles if a RLC application is not found
        $this->set_rlc();

        # Check for a profile, show dates accordingly
        $this->set_profile();

        # Check for a roomate, show dates accordingly
        $this->set_roomate();

        # Always show as available.
        $this->set_verify();
        
        for($i = HMS_SIDE_STUDENT_MIN;$i <= HMS_SIDE_STUDENT_MAX; $i++) {
            if(isset($this->steps_text[$i])){
                $template['progress'][$i - HMS_SIDE_STUDENT_MIN][$this->steps_styles[$i]] = $this->steps_text[$i];
            }
        }

        $page = PHPWS_Template::process($template, 'hms', 'misc/side_thingie.tpl');
        Layout::add($page, 'hms', 'default');
    }

    function set_apply_agree()
    {

        # If this is the step we're on, then set style accordingly
        if($this->step == HMS_SIDE_STUDENT_AGREE){
            $this->steps_styles[HMS_SIDE_STUDENT_AGREE] = 'STEP_CURRENT';
            $this->steps_styles[HMS_SIDE_STUDENT_APPLY] = 'STEP_TOGO';
        }

        if($this->step == HMS_SIDE_STUDENT_APPLY){
            $this->steps_styles[HMS_SIDE_STUDENT_AGREE] = 'STEP_COMPLETED';
            $this->steps_styles[HMS_SIDE_STUDENT_APPLY] = 'STEP_CURRENT';
        }

        # Check if the student has an application on file already. If so, set agreed/applied steps to completed and we're done here.
        if(HMS_Application::check_for_application($_SESSION['asu_username']) !== FALSE){
            $this->steps_styles[HMS_SIDE_STUDENT_AGREE] = 'STEP_COMPLETED';
            $this->steps_styles[HMS_SIDE_STUDENT_APPLY] = 'STEP_COMPLETED';
            return;
        }
            
        # If the student does not have an application on file... check apply dates, set dates/styles accordingly
        if($this->curr_timestamp < $this->deadlines->get_submit_application_begin_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_AGREE] .= " (available ". date('n/j/y',$this->deadlines->get_submit_application_begin_timestamp()) .")";
            $this->steps_text[HMS_SIDE_STUDENT_APPLY] .= " (available ". date('n/j/y',$this->deadlines->get_submit_application_begin_timestamp()) .")";
            $this->steps_styles[HMS_SIDE_STUDENT_AGREE] = 'STEP_NOTYET';
            $this->steps_styles[HMS_SIDE_STUDENT_APPLY] = 'STEP_NOTYET';
            return;
        }else if($this->curr_timestamp > $this->deadlines->get_submit_application_begin_timestamp() && $this->curr_timestamp < $this->deadlines->get_submit_application_end_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_AGREE] .= " (complete by ". date('n/j/y',$this->deadlines->get_submit_application_end_timestamp()) .")";
            $this->steps_text[HMS_SIDE_STUDENT_APPLY] .= " (complete by ". date('n/j/y',$this->deadlines->get_submit_application_end_timestamp()) .")";
            return;
        }else if($this->curr_timestamp > $this->deadlines->get_submit_application_end_timestamp()){
            $this->steps_styles[HMS_SIDE_STUDENT_AGREE] = 'STEP_MISSED';
            $this->steps_styles[HMS_SIDE_STUDENT_APPLY] = 'STEP_MISSED';
            return;
        }

        return;
    }

    function set_rlc()
    {
        if($this->entry_term != TERM_FALL){
            unset($this->steps_text[HMS_SIDE_STUDENT_RLC]);
            return;
        }
        
        # If this is the step we're on, then set style accordingly
        $on_this_step = FALSE;
        if($this->step == HMS_SIDE_STUDENT_RLC){
            $this->steps_styles[HMS_SIDE_STUDENT_RLC] = 'STEP_CURRENT';
            $on_this_step = TRUE;
        }
        
        # Check to see if the student has a RLC application on file already. If so, set styles to completed and we're done.
        if(HMS_RLC_Application::check_for_application($_SESSION['asu_username']) !== FALSE){
            $this->steps_styles[HMS_SIDE_STUDENT_RLC] = 'STEP_COMPLETED';
            return;
        }

        # If the student does not have an application on file... check apply dates, set dates/styles accordingly
        if($this->curr_timestamp < $this->deadlines->get_submit_application_begin_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_RLC] .= " (available ". date('n/j/y',$this->deadlines->get_submit_application_begin_timestamp()) .")";
            $this->steps_styles[HMS_SIDE_STUDENT_RLC] = 'STEP_NOTYET';
            return;
        }else if($this->curr_timestamp > $this->deadlines->get_submit_application_begin_timestamp() && $this->curr_timestamp < $this->deadlines->get_submit_rlc_application_end_timestamp()){
            # We are within deadlines, check to see if we're actually on this step
            if($on_this_step){
                # We're on this step currently, so don't add a link, just add the text
                $this->steps_text[HMS_SIDE_STUDENT_RLC] .= " (complete by ". date('n/j/y',$this->deadlines->get_submit_rlc_application_end_timestamp()) . ")";
            }else{
                $this->steps_text[HMS_SIDE_STUDENT_RLC] = PHPWS_Text::secureLink($this->steps_text[HMS_SIDE_STUDENT_RLC] . " (complete by ". date('n/j/y',$this->deadlines->get_submit_rlc_application_end_timestamp()) . ")", 'hms', array('type'=>'student', 'op'=>'show_rlc_application_form'));
                $this->steps_styles[HMS_SIDE_STUDENT_RLC] = 'STEP_TOGO';
            }
            return;
        }else if($this->curr_timestamp > $this->deadlines->get_submit_rlc_application_end_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_RLC] .= "(skipped)";
            $this->steps_styles[HMS_SIDE_STUDENT_RLC] = 'STEP_OPT_MISSED';
            return;
        }
    }

    function set_profile()
    {
        if($this->entry_term != TERM_FALL){
            unset($this->steps_text[HMS_SIDE_STUDENT_PROFILE]);
            return;
        }
        
        # If this is the step we're on, then set style accordingly
        $on_this_step = FALSE;
        if($this->step == HMS_SIDE_STUDENT_PROFILE){
            $this->steps_styles[HMS_SIDE_STUDENT_PROFILE] = 'STEP_CURRENT';
            $on_this_step = TRUE;
        }
        
        #Check to see if the student has a profile in the database already. If so, show this step as completed and return.
        
        if(HMS_Student_Profile::check_for_profile($_SESSION['asu_username'])){
            $this->steps_styles[HMS_SIDE_STUDENT_PROFILE] = 'STEP_COMPLETED';
            return;
        }
        
        # If the student does not have a proflie on file... check dates, set dates/styles accordingly
        if($this->curr_timestamp < $this->deadlines->get_edit_profile_begin_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_PROFILE] .= " (available ". date('n/j/y',$this->deadlines->get_edit_profile_begin_timestamp()) .")";
            $this->steps_styles[HMS_SIDE_STUDENT_PROFILE] = 'STEP_NOTYET';
            return;
        }else if($this->curr_timestamp > $this->deadlines->get_edit_profile_begin_timestamp() && $this->curr_timestamp < $this->deadlines->get_edit_profile_end_timestamp()){
            # We are within deadlines, check to see if we're actually on this step
            if($on_this_step){
                # We're on this step currently, so don't add a link, just add the text
                $this->steps_text[HMS_SIDE_STUDENT_PROFILE] .=  " (complete by ". date('n/j/y',$this->deadlines->get_edit_profile_end_timestamp()) . ")";
            }else{
                # We're not on this step, so add the link and text
                $this->steps_text[HMS_SIDE_STUDENT_PROFILE] = PHPWS_Text::secureLink($this->steps_text[HMS_SIDE_STUDENT_PROFILE] . " (complete by ". date('n/j/y',$this->deadlines->get_edit_profile_end_timestamp()) . ")", 'hms', array('type'=>'student', 'op'=>'show_profile_form'));
                $this->steps_styles[HMS_SIDE_STUDENT_PROFILE] = 'STEP_TOGO';
            }
            return;
        }else if($this->curr_timestamp > $this->deadlines->get_edit_profile_end_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_PROFILE] .= "(skipped)";
            $this->steps_styles[HMS_SIDE_STUDENT_PROFILE] = 'STEP_OPT_MISSED';
            return;
        }
        
    }

    function set_roomate()
    {
        if($this->entry_term != TERM_FALL){
            unset($this->steps_text[HMS_SIDE_STUDENT_ROOMMATE]);
            return;
        }
        
        # If this is the step we're on, then set style accordingly
        $on_this_step = FALSE;
        if($this->step == HMS_SIDE_STUDENT_ROOMMATE){
            $this->steps_styles[HMS_SIDE_STUDENT_ROOMMATE] = 'STEP_CURRENT';
            $on_this_step = TRUE;
        }

        PHPWS_Core::initModClass('hms','HMS_Roommate.php');
        PHPWS_Core::initModClass('hms','HMS_Roommate_Approval.php');

        # If the user has roommates confirmed or has request pending approval, then call this step completed
        if(HMS_Roommate::has_roommates($_SESSION['asu_username']) || HMS_Roommate_Approval::has_requested_someone($_SESSION['asu_username'])){
            $this->steps_styles[HMS_SIDE_STUDENT_ROOMMATE] = 'STEP_COMPLETED';
            return;
        }
        
        if($this->curr_timestamp < $this->deadlines->get_submit_application_begin_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_ROOMMATE] .= " (complete by ". date('n/j/y',$this->deadlines->get_submit_application_begin_timestamp()) .")";
            $this->steps_styles[HMS_SIDE_STUDENT_ROOMMATE] .= 'STEP_NOTYET';
            return;
        }else if($this->curr_timestamp > $this->deadlines->get_submit_application_begin_timestamp() && $this->curr_timestamp < $this->deadlines->get_search_profiles_end_timestamp()){
            # We are within deadlines, check to see if we're actually on this step
            if($on_this_step){
                # We're on this step currently, so don't add a link, just add the text
                $this->steps_text[HMS_SIDE_STUDENT_ROOMMATE] .= " (complete by ". date('n/j/y',$this->deadlines->get_search_profiles_end_timestamp()) . ")";
            }else{
                # We're not on this step, so add the link and text
                $this->steps_text[HMS_SIDE_STUDENT_ROOMMATE] = PHPWS_Text::secureLink($this->steps_text[HMS_SIDE_STUDENT_ROOMMATE] . " (complete by ". date('n/j/y',$this->deadlines->get_search_profiles_end_timestamp()) . ")", 'hms', array('type'=>'student', 'op'=>'get_roommate_username'));
                $this->steps_styles[HMS_SIDE_STUDENT_ROOMMATE] = 'STEP_TOGO';
            }
            return;
        }else if($this->curr_timestamp > $this->deadlines->get_search_profiles_end_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_ROOMMATE] .= "(skipped)";
            $this->steps_styles[HMS_SIDE_STUDENT_ROOMMATE] = 'STEP_OPT_MISSED';
            return;
        }
    }

    function set_verify()
    {
        # If this is the step we're on, then set style accordingly
        $on_this_step = FALSE;
        if($this->step == HMS_SIDE_STUDENT_ROOMMATE){
            $this->steps_styles[HMS_SIDE_STUDENT_ROOMMATE] = 'STEP_CURRENT';
            $on_this_step = TRUE;
        }

        # Check deadlines and set accordingly
        if($this->curr_timestamp < $this->deadlines->get_view_assignment_begin_timestamp()){
            $this->steps_text[HMS_SIDE_STUDENT_VERIFY] .= " (available " . date('n/j/y',$this->deadlines->get_view_assignment_begin_timestamp()) . ")";
            $this->steps_styles[HMS_SIDE_STUDENT_VERIFY] = 'STEP_NOTYET';
            return;
        }else{
            # We are past the starting deadline, check to see if we're actually on this step
            if($on_this_step){
                # We're on this step, so don't add a link, just the text
                $this->steps_text[HMS_SIDE_STUDENT_VERIFY] .= $this->steps_text[HMS_SIDE_STUDENT_VERIFY];
            }else{
                # We're not on this step, so add the link and text
                $this->steps_text[HMS_SIDE_STUDENT_VERIFY] = PHPWS_Text::secureLink($this->steps_text[HMS_SIDE_STUDENT_VERIFY], 'hms', array('type'=>'student', 'op'=>'show_verify_assignment'));
            }
            $this->steps_styles[HMS_SIDE_STUDENT_VERIFY] = 'STEP_TOGO';
            return;
        }
    }
}
