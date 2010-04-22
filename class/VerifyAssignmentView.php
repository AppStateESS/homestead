<?php

PHPWS_Core::initModClass('hms', 'View.php');

class VerifyAssignmentView extends View
{
    private $student;
    private $term;

    public function __construct($username)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        $student = StudentFactory::getStudentByUsername($username,Term::getCurrentTerm());
        $this->student = $student;
        $this->term = $student->getApplicationTerm();
    }

    
    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');

        $tpl = array();

        $assignment = HMS_Assignment::getAssignment($this->student->getUsername(), $this->term);
        if($assignment === NULL || $assignment == FALSE){
            $tpl['NO_ASSIGNMENT'] = "You do not currently have a housing assignment.";
        }else{
            $tpl['ASSIGNMENT'] = $assignment->where_am_i() . '<br />';

            # Determine the student's type and figure out their movein time
            $type = $this->student->getType();

            if($type == TYPE_CONTINUING){
                $movein_time_id = $assignment->get_rt_movein_time_id();
            }elseif($type == TYPE_TRANSFER){
                $movein_time_id = $assignment->get_t_movein_time_id();
            }else{
                $movein_time_id = $assignment->get_f_movein_time_id();
            }
            
            if($movein_time_id == NULL){
                $tpl['MOVE_IN_TIME'] = 'To be determined<br />';
            }else{
                $movein_times = HMS_Movein_Time::get_movein_times_array($this->term);
                $tpl['MOVE_IN_TIME'] = $movein_times[$movein_time_id];
            }
        }

        //get the assignees to the room that the bed that the assignment is in
        $assignees = !is_null($assignment) ? $assignment->get_parent()->get_parent()->get_assignees() : NULL;
        $roommates = array();
        
        if(!is_null($assignees)){
            foreach($assignees as $roommate){
                if($roommate->getUsername() != $this->student->getUsername()){
                    $tpl['roommate'][]['ROOMMATE'] = $roommate->getFullName() . '(' . $roommate->getEmailLink() . ')';
                }
            }
        } else {
            $tpl['roommate'] = 'You do not have a roommate';
        }

        $rlc_assignment = HMS_RLC_Assignment::checkForAssignment($this->student->getUsername(), $this->term);
        if($rlc_assignment == NULL || $rlc_assignment === FALSE){
            $tpl['RLC'] = "You have not been accepted to an RLC.";
        }else{
            $rlc_list = HMS_Learning_Community::getRLCList();
            $tpl['RLC'] = 'You have been assigned to the ' . $rlc_list[$rlc_assignment['rlc_id']];
        }

        $tpl['MENU_LINK'] = PHPWS_Text::secureLink('Back to Main Menu', 'hms', array('type'=>'student', 'op'=>'show_main_menu'));
        
        return PHPWS_Template::process($tpl, 'hms', 'student/verify_assignment.tpl');
    }
}
?>
