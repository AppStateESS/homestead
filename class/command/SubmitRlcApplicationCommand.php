<?php

PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');

class SubmitRlcApplicationCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRlcApplication');
    }

    public function execute(CommandContext $context){

        $term = $context->get('term');

        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());

        # Check for an existing application and delete it
        $oldApp = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $term);

        if($oldApp->id != NULL){
            $result = $oldApp->delete();
        }

        $choice1 = new HMS_Learning_Community($context->get('rlc_first_choice'));
        $choice2 = new HMS_Learning_Community($context->get('rlc_second_choice'));
        $choice3 = new HMS_Learning_Community($context->get('rlc_third_choice'));

        if(!$choice1->allowStudentType($student->getType())
           || ($choice2->id != -1 && !$choice2->allowStudentType($student->getType()))
           || ($choice3->id != -1 && !$choice3->allowStudentType($student->getType()))
        ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, you cannot apply for the selected RLC. Please contact University Housing if you believe this to be in error.');
            $cmd = CommandFactory::getCommand('ShowRlcApplicationView');
            $cmd->setTerm($term);
            $cmd->redirect();
        }

        $application = new HMS_RLC_Application();
        $application->setUsername($student->getUsername());
        $application->setDateSubmitted(mktime());
        $application->setFirstChoice($context->get('rlc_first_choice'));
        $application->setSecondChoice($choice2->id > 0 ? $choice2->id : NULL);
        $application->setThirdChoice($choice3->id > 0 ? $choice3->id : NULL);
        $application->setWhySpecificCommunities($context->get('why_specific_communities'));
        $application->setStrengthsWeaknesses($context->get('strengths_weaknesses'));
        $application->setRLCQuestion0($context->get('rlc_question_0'));
        $application->setRLCQuestion1(is_null($context->get('rlc_question_1')) ? '' : $context->get('rlc_question_1'));
        $application->setRLCQuestion2(is_null($context->get('rlc_question_2')) ? '' : $context->get('rlc_question_2'));
        $application->setEntryTerm($context->get('term'));
        $application->setApplicationType(RLC_APP_FRESHMEN);
        $result = $application->save();

        if(PEAR::isError($result)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, an error occured while attempting to submit your application.  If this problem persists please contact University Housing.');
            $cmd = CommandFactory::getCommand('ShowRlcApplicationView');
            $cmd->setTerm($term);
            $cmd->redirect();
        } else {
            # Log that this happened
            PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_SUBMITTED_RLC_APPLICATION, $student->getUsername());

            # Send the notification email
            PHPWS_Core::initModClass('hms', 'HMS_Email.php');
            HMS_Email::send_rlc_application_confirmation($student);

            # Show a success message and redirect
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your application has been submitted');
            $cmd = CommandFactory::getCommand('ShowStudentMenu');
            $cmd->redirect();
        }
    }
}

?>
