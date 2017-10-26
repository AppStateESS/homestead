<?php

namespace Homestead\Command;

use \Homestead\HMS_RLC_Assignment;
use \Homestead\Exception\PermissionException;

class AjaxSetRlcAssignmentStatusCommand extends Command{



    public function getRequestVars(){
        return array('action'=>'RemoveRlcAssignment');
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'add_rlc_members')){
            throw new PermissionException('You do not have permission to change Rlc Assignments.');
        }

        $newState = $_REQUEST['status'];

        $assignment = HMS_RLC_Assignment::getAssignmentById($context->get('assignmentId'));

        $assignment->setState($newState);

        $assignment->save();

        echo json_encode(array("message" => "Assignment status updated", "type" => "success"));
        exit;
    }
}
