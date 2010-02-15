<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationPage2View.php');

class ShowRlcApplicationPage2ViewCommand extends Command {
    private $requestVars = array('action'=>'ShowRlcApplicationPage2View');

    public function setRequestVars(Array $vars){
        $this->requestVars = $vars;
    }

    public function getRequestVars(){
        return $this->requestVars;
    }

    public function execute(CommandContext $context){

        $errorCmd = CommandFactory::getCommand('ShowRlcApplicationPage1View');

        // Check input from the previous page
        $first = $context->get('rlc_first_choice');
        $second = $context->get('rlc_second_choice');
        $third = $context->get('rlc_third_choice');

        if($first == -1){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "You must choose a community as your 'first choice'.");
            $errorCmd->redirect();
        }

        if($first == $second || ($second != -1 && $third != -1 && $second == $third) || ($first == $third)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You cannot choose the same community twice.');
            $errorCmd->redirect();
        }

        $whySpecific = $context->get('why_specific_communities');
        $strengths = $context->get('strengths_weaknesses');

        if(!isset($whySpecific) || is_null($whySpecific) || empty($whySpecific)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must respond to the question regarding your interest in the communities you chose.');
            $errorCmd->redirect();
        }

        if(!isset($strengths) || is_null($strengths) || empty($strengths)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must respond to the question regarding your strengths and weaknesses.');
            $errorCmd->redirect();
        }


        $view = new RlcApplicationPage2View($context);

        $context->setContent($view->show());
    }
}

?>
