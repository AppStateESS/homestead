<?php

class AjaxSetRlcAssignmentCommand extends Command {
    public function getRequestVars(){
        return array('action'=>'AjaxSetRlcAssignment');
    }

    public function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'approve_rlc_applications')){
            echo json_encode(array('success' => false,
                              'message' => 'You do not have permission to approve/deny RLC applications.'
            ));
            exit;
        }

        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

        $app = HMS_RLC_Application::getApplicationById($context->get('applicationId'));
        $student = StudentFactory::getStudentByUsername($app->username, $app->term);

        $assign = new HMS_RLC_Assignment();
        $assign->rlc_id         = $context->get('rlcId');
        $assign->gender         = $student->getGender();
        $assign->assigned_by    = UserStatus::getUsername();
        $assign->application_id = $app->id;
        $assign->state          = 'new';
        $assign->save();

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($app->username, 28, Current_User::getUsername(), 'Application Denied');

        echo json_encode(array("success" => true,
                          'message' => 'Successfully assigned student'
        ));
        exit;
    }
}
