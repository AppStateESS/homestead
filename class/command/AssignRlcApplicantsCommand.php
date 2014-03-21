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


        PHPWS_Core::initModClass('hms','HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms','HMS_RLC_Assignment.php');
        PHPWS_Core::initModClass('hms','StudentFactory.php');

        # Foreach rlc assignment made
        # $app_id is the 'id' column in the 'learning_community_applications' table, tells which student we're assigning
        # $rlc_id is the 'id' column in the 'learning_communitites' table, and refers to the RLC selected for the student
        foreach($_REQUEST['final_rlc'] as $app_id => $rlc_id){

            if($rlc_id <= 0){
                continue;
            }

            $app = HMS_RLC_Application::getApplicationById($app_id);
            $student = StudentFactory::getStudentByUsername($app->username, $app->term);

            # Insert a new assignment in the 'learning_community_assignment' table
            $assign = new HMS_RLC_Assignment();
            $assign->rlc_id         = $rlc_id;
            $assign->gender         = $student->getGender();
            $assign->assigned_by    = UserStatus::getUsername();
            $assign->application_id = $app->id;
            $assign->state          = 'new';

            $assign->save();

            # Log the assignment
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($app->username, ACTIVITY_ASSIGN_TO_RLC, UserStatus::getUsername(), "New Assignment");
        }

        // Show a success message
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Successfully assigned RLC applicant(s).');

        $context->goBack();
    }
}
?>
