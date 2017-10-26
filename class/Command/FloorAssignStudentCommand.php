<?php

namespace Homestead\Command;

use \Homestead\StudentFactory;
use \Homestead\UserStatus;
use \Homestead\HMS_Assignment;
use \Homestead\Term;
use \Homestead\Exception\PermissionException;
use \Homestead\Exception\StudentNotFoundException;
use \Homestead\Exception\AssignmentException;

/*
 * Ajax command that returns error codes and does not allow reassignment.
 *
 */

class FloorAssignStudentCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'assignment_maintenance')){
            throw new PermissionException('You do not have permission to assign students.');
        }

        $term = Term::getSelectedTerm();
        try{
            if(preg_match('/^[0-9]{9}$/', $context->get('username'))){
                $student = StudentFactory::getStudentByBannerId($context->get('username'), $term);
            } else {
                $student = StudentFactory::getStudentByUsername(strtolower(trim($context->get('username'))), $term);
            }
        } catch(StudentNotFoundException $e){
            echo json_encode(array('success'=>false, 'message'=>$e->getMessage()));
            exit;
        } catch(\Exception $e){
            echo json_encode(array('success'=>false, 'message'=>$e->getMessage()));
            exit;
        }

        $bed = $context->get('bed');
        $plan = $context->get('mealplan');
        $reason = $context->get('assignmenttype');

        if(HMS_Assignment::checkForAssignment($student->getUsername(), $term)){
            echo json_encode(array('success'=>false,
                        'message'=>'Error: Student is already assigned elsewhere, please unassign this student first.'));
            exit;
        }
        try{
            HMS_Assignment::assignStudent($student, $term, NULL, $bed, $plan, '', false, $reason);
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
