<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationPage2View.php');

class ShowRlcApplicationPage2ViewCommand extends Command
{

    private $term;

    public function getRequestVars()
    {
        return array('action'=>'ShowRlcApplicationPage2View', 'term' => $this->term);
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function execute(CommandContext $context)
    {

        $term = $context->get('term');

        if(!isset($term) || is_null($term) || empty($term)) {
            throw new InvalidArgumentException('Missing term.');
        }

        $errorCmd = CommandFactory::getCommand('ShowRlcApplicationPage1View');
        $errorCmd->setTerm($term);

        // Check input from the previous page
        $first = $context->get('rlc_first_choice');
        $second = $context->get('rlc_second_choice');
        $third = $context->get('rlc_third_choice');

        if($first == -1) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "You must choose a community as your 'first choice'.");
            $errorCmd->redirect();
        }

        if($first == $second || ($second != -1 && $third != -1 && $second == $third) || ($first == $third)) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You cannot choose the same community twice.');
            $errorCmd->redirect();
        }

        $whySpecific = $context->get('why_specific_communities');
        $strengths = $context->get('strengths_weaknesses');

        // Check lengths of questions responses. Must be > 0, but < HMS_RLC_Application::RLC_RESPONSE_LIMIT
        if(!isset($whySpecific) || is_null($whySpecific) || empty($whySpecific)) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must respond to the question regarding your interest in the communities you chose.');
            $errorCmd->redirect();
        }

        if(strlen($whySpecific) > HMS_RLC_Application::RLC_RESPONSE_LIMIT) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your respose to the question regarding your community choices is too long. Please limit your response to ' . HMS_RLC_Application::RLC_RESPONSE_LIMIT .  ' characters (including spaces and punctuation).');
            $errorCmd->redirect();
        }

        if(!isset($strengths) || is_null($strengths) || empty($strengths)) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must respond to the question regarding your strengths and weaknesses.');
            $errorCmd->redirect();
        }

        if(strlen($strengths) > HMS_RLC_Application::RLC_RESPONSE_LIMIT) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your respose to the question regarding your strengths and weaknesses is too long. Please limit your response to ' . HMS_RLC_Application::RLC_RESPONSE_LIMIT .  ' characters (including spaces and punctuation).');
            $errorCmd->redirect();
        }

        $view = new RlcApplicationPage2View($context);

        $context->setContent($view->show());
    }
}

?>
