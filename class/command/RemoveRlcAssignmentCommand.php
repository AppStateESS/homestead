<?php

class RemoveRlcAssignmentCommand extends Command{
    
    private $assignmentId;
    
    public function setAssignmentId($id)
    {
        $this->assignmentId = $id;
    }
    
    public function getRequestVars(){
        return array('action'=>'RemoveRlcAssignment', 'assignmentId'=>$this->assignmentId);
    }
    
    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve/deny RLC applications.');
        }
        
        
        $context->setContent('todo: remove');
    }
}