<?php

PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
class GetBedAssignmentInfoCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $bed = new HMS_Bed($context->get('bed_id'));
        if($bed->loadAssignment()){
            $output = array('username'=>'',
                         'fullname'=>'',
                         'profile_link'=>''
                        );
        } else {
            $student = StudentFactory::getStudentByUsername($bed->_cur_assignment->asu_username, Term::getSelectedTerm());
            $output = array('username'=>$student->getUsername(),
                         'fullname'=>$student->getFullName(),
                         'profile_link'=>$student->getFullNameProfileLink()
                        );
        }

        echo json_encode($output);
        exit;
    }
}
?>
