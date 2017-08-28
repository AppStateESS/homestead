<?php

namespace Homestead\UI;

use \Homestead\HMS_Term;
use \Homestead\HMS_SOAP;
use \Homestead\HMS_Assignment;
use \Homestead\HMS_Learning_Community;
use \Homestead\HMS_RLC_Assignment;
use \Homestead\HMS_Movein_Time;

/**
 * Student_UI.php
 * A class for consolidating the the methods for student UI/Interface handling.
 */

class HMS_Student_UI{

    public function show_verify_assignment()
    {
        $tpl = array();

        $assignment = HMS_Assignment::get_assignment($_SESSION['asu_username'], $_SESSION['application_term']);
        if($assignment === NULL || $assignment == FALSE){
            $tpl['NO_ASSIGNMENT'] = "You do not currently have a housing assignment.";
        }else{
            $tpl['ASSIGNMENT'] = $assignment->where_am_i() . '<br />';

            # Determine the student's type and figure out their movein time
            $type = HMS_SOAP::get_student_type($_SESSION['asu_username'], $_SESSION['application_term']);

            if($type == TYPE_CONTINUING){
                $movein_time_id = $assignment->get_rt_movein_time_id();
            }elseif($type == TYPE_TRANFER){
                $movein_time_id = $assignment->get_t_movein_time_id();
            }else{
                $movein_time_id = $assignment->get_f_movein_time_id();
            }

            if($movein_time_id == NULL){
                $tpl['MOVE_IN_TIME'] = 'To be determined<br />';
            }else{
                $movein_times = HMS_Movein_Time::get_movein_times_array($_SESSION['application_term']);
                $tpl['MOVE_IN_TIME'] = $movein_times[$movein_time_id];
            }
        }

        //get the assignees to the room that the bed that the assignment is in
        $assignees = !is_null($assignment) ? $assignment->get_parent()->get_parent()->get_assignees() : NULL;
        $roommates = array();
        if(!is_null($assignees)){
            foreach($assignees as $roommate){
                if($roommate->asu_username != $_SESSION['asu_username']){
                    $roommates[] = $roommate->asu_username;
                }
            }
        }

        if(empty($roommates)){
            $tpl['roommate'][]['ROOMMATE'] = 'You do not have a roommate.';
        } else {
            foreach($roommates as $roommate){
                $tpl['roommate'][]['ROOMMATE'] = '' . HMS_SOAP::get_name($roommate) . ' (<a href="mailto:' . $roommate . '@appstate.edu">'. $roommate . '@appstate.edu</a>)';
            }
        }

        $rlc_assignment = HMS_RLC_Assignment::check_for_assignment($_SESSION['asu_username'], $_SESSION['application_term']);
        if($rlc_assignment == NULL || $rlc_assignment === FALSE){
            $tpl['RLC'] = "You have not been accepted to an RLC.";
        }else{
            $rlc_list = HMS_Learning_Community::getRlcList();
            $tpl['RLC'] = 'You have been assigned to the ' . $rlc_list[$rlc_assignment['rlc_id']];
        }

        $tpl['MENU_LINK'] = \PHPWS_Text::secureLink('Back to Main Menu', 'hms', array('type'=>'student', 'op'=>'show_main_menu'));

        return \PHPWS_Template::process($tpl, 'hms', 'student/verify_assignment.tpl');
    }
}
