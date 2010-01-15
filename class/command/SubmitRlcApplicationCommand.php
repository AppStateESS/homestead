<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');

class SubmitRlcApplicationCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'SubmitRlcApplication');
    }

    public function execute(CommandContext $context){
        $student = StudentFactory::getStudentByUsername(Current_User::getUsername(), Term::getCurrentTerm());

        $application = new HMS_RLC_Application();
        $application->setUserID(Current_User::getUsername());
        $application->setDateSubmitted(mktime());
        $application->setFirstChoice($context->get('rlc_first_choice'));
        $application->setSecondChoice($context->get('rlc_second_choice'));
        $application->setThirdChoice($context->get('rlc_third_choice'));
        $application->setWhySpecificCommunities($context->get('why_specific_communities'));
        $application->setStrengthsWeaknesses($context->get('strengths_weaknesses'));
        $application->setRLCQuestion0($context->get('rlc_question_0'));
        $application->setRLCQuestion1($context->get('rlc_question_1'));
        $application->setRLCQuestion2($context->get('rlc_question_2'));
        $application->setEntryTerm($student->getApplicationTerm());
        $result = $application->save();

        if(PEAR::isError($result)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Sorry, an error occured while attempting to submit your application.  If this problem persists please contact Housing and Residence Life.');
            $cmd = CommandFactory::getCommand('ShowRlcApplicationView');
            $cmd->redirect();
        } else {
            NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your application has been submitted');
            $cmd = CommandFactory::getCommand('ShowStudentMenu');
            $cmd->redirect();
        }
    }
}

?>
