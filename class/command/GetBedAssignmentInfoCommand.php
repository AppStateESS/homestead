<?php

PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
class GetBedAssignmentInfoCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assign_by_floor')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to assign by floor!');
        }

        $bed = new HMS_Bed($context->get('bed_id'));
        $bed->term = Term::getSelectedTerm();
        if(!$bed->loadAssignment() || is_null($bed->_curr_assignment)){
            $output = array('username'=>'',
                         'fullname'=>'',
                         'profile_link'=>''
                        );
        } else {
            $student = StudentFactory::getStudentByUsername($bed->_curr_assignment->asu_username, Term::getSelectedTerm());
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
