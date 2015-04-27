<?php

PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');

class SubmitRlcApplicationCommand extends Command
{

    public function getRequestVars()
    {
        return array('action'=>'SubmitRlcApplication');
    }

    public function execute(CommandContext $context)
    {

        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), Term::getCurrentTerm());

        $errorCmd = CommandFactory::getCommand('ShowRlcApplicationView');
        $errorCmd->setTerm($term);

        $choice1 = new HMS_Learning_Community($context->get('rlc_first_choice'));
        $choice2 = new HMS_Learning_Community($context->get('rlc_second_choice'));
        $choice3 = new HMS_Learning_Community($context->get('rlc_third_choice'));

        if(!$choice1->allowStudentType($student->getType())
        || ($choice2->id != -1 && !$choice2->allowStudentType($student->getType()))
        || ($choice3->id != -1 && !$choice3->allowStudentType($student->getType()))
        ){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, you cannot apply for the selected RLC. Please contact University Housing if you believe this to be in error.');
            $errorCmd->redirect();
        }

        // Check the lengths of the responses to the short answer questions
        $question0 = $context->get('rlc_question_0');
        $question1 = $context->get('rlc_question_1');
        $question2 = $context->get('rlc_question_2');
        $whySpecific = $context->get('why_specific_communities');
        $strengthsWeaknesses = $context->get('strengths_weaknesses');
        
        if(str_word_count($whySpecific) > HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT)
        {
        NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your respose to the question is too long. Please limit your response to ' . HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT .  ' words.');
            $errorCmd->redirect();
        }
        
        if(str_word_count($strengthsWeaknesses) > HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT)
        {
        NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your respose to the question is too long. Please limit your response to ' . HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT .  ' words.');
            $errorCmd->redirect();
        }

        if(str_word_count($question0) > HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your respose to the first question is too long. Please limit your response to ' . HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT .  ' words.');
            $errorCmd->redirect();
        }

        if(str_word_count($question1) > HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your respose to the second question is too long. Please limit your response to ' . HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT .  ' words.');
            $errorCmd->redirect();
        }

        if(str_word_count($question2) > HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your respose to the third question is too long. Please limit your response to ' . HMS_RLC_Application::RLC_RESPONSE_WORD_LIMIT .  ' words.');
            $errorCmd->redirect();
        }

        // Check for an existing application and delete it
        $oldApp = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $term);

        if(isset($oldApp) && $oldApp->id != NULL) {
            //TODO check if the student has already been assigned to an RLC via the old application

            // Delete the old application to make way for this one
            try {
                $oldApp->delete();
            } catch(Exception $e){
                NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, an error occured while attempting to replace your existing Residential Learning Community Application.  If this problem persists please contact University Housing.');
                $errorCmd->redirect();
            }
        }

        // Setup the new application
        $application = new HMS_RLC_Application();
        $application->setUsername($student->getUsername());
        $application->setDateSubmitted(time());
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

        try {
            $application->save();
        }catch(Exception $e) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, an error occured while attempting to submit your application.  If this problem persists please contact University Housing.');
            $errorCmd->redirect();
        }

        # Log that this happened
        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_SUBMITTED_RLC_APPLICATION, $student->getUsername());

        # Send the notification email
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::send_rlc_application_confirmation($student);

        # Show a success message and redirect
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your Residential Learning Community (RLC) application has been successfully submitted. You should receive a confirmation email (sent to your Appalachian State email account) soon. Notification of your acceptance into an RLC will also be sent to your Appalachian State email account.  Please continue to check your ASU email account regularly.  For more information on the RLC acceptance timeline or frequently asked questions, please visit <a href="http://housing.appstate.edu/rlc" target="_blank">housing.appstate.edu/rlc</a>.');
        $cmd = CommandFactory::getCommand('ShowStudentMenu');
        $cmd->redirect();
    }
}

?>
