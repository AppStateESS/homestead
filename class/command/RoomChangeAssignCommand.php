<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');

class RoomChangeAssignCommand extends Command {

    public function getRequestVars(){
        $vars = array('action'=>'RoomChangeAssign');

        return $vars;
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
        try{
            if(preg_match('/^[0-9]{9}$/', $context->get('username'))){
                $student = StudentFactory::getStudentByBannerId($context->get('username'), $term);
            } else {
                $student = StudentFactory::getStudentByUsername(strtolower(trim($context->get('username'))), $term);
            }
        } catch(Exception $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, $e->getMessage());
            //redirect
        }

        $bed = $context->get('bed');
        $plan = $context->get('meal_plan');

        if(!HMS_Assignment::checkForAssignment($student->getUsername(), $term)){
            NQ::simple('hms', HMS_NOTFICATION_ERROR, 'Error: Student is not assigned anywhere, how are they "changing" rooms?');
            //redirect
        }

        try{
            $assign_result = HMS_Assignment::assignStudent($student, $term, NULL, $bed, $plan, '');
        }catch(AssignmentException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, $e->getMessage());
            //redirect
        }

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Student assigned!');
        //redirect
    }
}
?>