<?php

namespace Homestead\Command;

use Homestead\UserStatus;
use Homestead\Term;
use Homestead\AssignmentFactory;
use Homestead\StudentFactory;

class GetAssignmentsListCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'GetAssignmentsList');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()){
            throw new \Homestead\Exception\PermissionException('You do not have permission to access the assignment list.');
        }

        $term = Term::getSelectedTerm();

        $assignments = AssignmentFactory::getAssignmentsForTerm($term);

        $assignWithStudent = array();

        foreach($assignments as $row){
            $student = StudentFactory::getStudentByBannerId($row['banner_id'], $term);
            $row['first_name'] = $student->getFirstName();
            $row['last_name'] = $student->getLastName();
            $row['preferred_name'] = $student->getPreferredName();

            $assignWithStudent[] = $row;
        }

        echo json_encode($assignWithStudent);
        exit;
    }
}
