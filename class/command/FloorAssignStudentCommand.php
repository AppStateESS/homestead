<?

/*
 * Ajax command that returns error codes and does not allow reassignment.
 *
 */

class FloorAssignStudentCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'assignment_maintenance')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to assign students.');
        }

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $term = Term::getSelectedTerm();
        $student = StudentFactory::getStudentByUsername(strtolower(trim($context->get('username'))), $term);
        $bed = $context->get('bed');
        $plan = $context->get('meal_plan');

        if(HMS_Assignment::checkForAssignment($student->getUsername(), $term)){
            echo json_encode(array('success'=>false,
                                   'message'=>'Error: Student is already assigned elsewhere, please unassign this student first.'));
            exit;
        }
        try{
            $assign_result = HMS_Assignment::assignStudent($student, $term, NULL, $bed, $plan, '');
        }catch(AssignmentException $e){
            echo json_encode(array('success'=>false,
                                   'message'=>$e->getMessage()));
            exit;
        }
        echo json_encode(array('success'=>true,
                               'message'=>'Student assigned!'));
        exit;
    }
}
?>