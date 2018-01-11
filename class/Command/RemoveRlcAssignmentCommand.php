<?php

namespace Homestead\Command;

use \Homestead\HMS_Activity_Log;
use \Homestead\HMS_RLC_Assignment;
use \Homestead\Exception\PermissionException;

class RemoveRlcAssignmentCommand extends Command{

    private $assignmentId;

    public function setAssignmentId($id){
        $this->assignmentId = $id;
    }

    public function getRequestVars(){
        return array('action'=>'RemoveRlcAssignment', 'assignmentId'=>$this->assignmentId);
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'remove_rlc_members')){
            throw new PermissionException('You do not have permission to approve/deny RLC applications.');
        }

        $assignment = HMS_RLC_Assignment::getAssignmentById($context->get('assignmentId'));

        $rlcName = $assignment->getRlcName();

        if(!is_null($assignment)){
            $assignment->delete();
        }else{
            echo json_encode(array("message" => "Could not find an RLC assignment with that id.", "type" => "error"));
        }

        $rlcApp = $assignment->getApplication();

        HMS_Activity_Log::log_activity($rlcApp->getUsername(), ACTIVITY_RLC_UNASSIGN, \Current_User::getUsername(), "Removed from RLC: $rlcName");

        echo json_encode(array("message" => "Membership removed.", "type" => "success"));
        exit;
    }
}
