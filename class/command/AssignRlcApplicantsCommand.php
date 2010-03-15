<?php

class AssignRlcApplicantsCommand extends Command {

    public function getRequestVars()
    {
        $vars = array('action'=>'AssignRlcApplicants');

        return $vars;
    }

    public function execute(CommandContext $context){
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to approve RLC applications.');
        }

        
        $errors = array();

        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms','StudentFactory.php');

        $app = new PHPWS_DB('hms_learning_community_applications');
        $ass = new PHPWS_DB('hms_learning_community_assignment');

        # Foreach rlc assignment made
        # $app_id is the 'id' column in the 'learning_community_applications' table, tells which student we're assigning
        # $rlc_id is the 'id' column in the 'learning_communitites' table, and refers to the RLC selected for the student
        foreach($_REQUEST['final_rlc'] as $app_id => $rlc_id){
            
            $app->reset();
            $ass->reset();
            
            # Lookup the student's RLC application (so we can have their username)
            $app->addWhere('id', $app_id);
            $application = $app->select('row');
            
            $student = StudentFactory::getStudentByUsername($application['user_id'], Term::getSelectedTerm());
           
            # Insert a new assignment in the 'learning_community_assignment' table
            $ass->addValue('rlc_id',            $rlc_id);
            $ass->addValue('gender',            $student->getGender());
            $ass->addValue('assigned_by',       UserStatus::getUsername());
            $ass_id = $ass->insert();

            # Log the assignment
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($application['user_id'], ACTIVITY_ASSIGN_TO_RLC, UserStatus::getUsername(), "New Assignment");

            # Update the RLC application with the assignment id
            $app->reset();
            $app->addValue('hms_assignment_id', $ass_id);
            $app->addWhere('id', $app_id);
            $app->update();
        }
        
        $context->goBack();
    }
}
?>
