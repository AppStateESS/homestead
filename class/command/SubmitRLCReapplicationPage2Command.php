<?php

class SubmitRLCReapplicationPage2Command extends Command {
    
    
    private $vars;
    private $term;

    public function setVars(Array $vars){
        $this->vars = $vars;
    }

    public function setTerm($term){
        $this->term = $term;
    }
    
    public function getRequestVars()
    {
        $reqVars = $this->vars;
        $reqVars['term'] = $this->term;
        unset($reqVars['rlc_question_0']);
        unset($reqVars['rlc_question_1']);
        unset($reqVars['rlc_question_2']);
        unset($reqVars['module']);
        
        $reqVars['action'] = 'SubmitRLCReapplicationPage2';
        return $reqVars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        
        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $errorCmd = CommandFactory::getCommand('ShowRlcReapplicationPageTwo');
        $errorCmd->setTerm($term);
        $errorCmd->setVars($_REQUEST);
        
        $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
        
        // Double check the the student is eligible
        $housingApp = HousingApplication::getApplicationByUser($student->getUsername(), $term);
        if(!$housingApp instanceof LotteryApplication){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You are not eligible to re-apply for a Learning Community.');
            $menuCmd->redirect();
        }
        
        // Make sure the user doesn't already have an application on file for this term
        $app = HMS_RLC_Application::checkForApplication($student->getUsername(), $term);
        if($app !== FALSE){
            NQ::simple('hms', HMS_NOTIFICATION_WARNING, 'You have already re-applied for a Learning Community for that term.');
            $menuCmd->redirect();
        }
        
        # Look up any existing RLC assignment (for the current term, should be the Spring term)
        $rlcAssignment = HMS_RLC_Assignment::getAssignmentByUsername($student->getUsername(), Term::getPrevTerm(Term::getCurrentTerm()));
        
        $rlcOpt        = $context->get('rlc_opt');
        $rlcChoice1    = $context->get('rlc_choice_1');
        $rlcChoice2    = $context->get('rlc_choice_2');
        $rlcChoice3    = $context->get('rlc_choice_3');
        $why           = $context->get('why_this_rlc');
        $contribute    = $context->get('contribute_gain');
        
        $question0 = $context->get('rlc_question_0');
        $question1 = $context->get('rlc_question_1');
        $question2 = $context->get('rlc_question_2');
        
        // Sanity Checking
        if(!isset($rlcChoice1) || $rlcChoice1 == 'select'){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must choose a community as your "first choice".');
            $errorCmd->redirect();
        }
        
        if((isset($rlcChoice2) && $rlcChoice1 == $rlcChoice2) || (isset($rlcChoice2) && isset($rlcChoice3) && $rlcChoice2 == $rlcChoice3) || (isset($rlcChoice3) && $rlcChoice1 == $rlcChoice3)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You cannot choose the same community twice.');
            $errorCmd->redirect();
        }
        
        // Check the short answer questions
        if(empty($why) || empty($contribute)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please respond to both of the short answer questions.');
            $errorCmd->redirect();
        }
       
        if(!isset($question0) || empty($question0)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please respond to all of the short answer questions.');
            $errorCmd->redirect();
        }
        
        if($rlcChoice2 != 'none' && (!isset($question1) || empty($question1))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please respond to all of the short answer questions.');
            $errorCmd->redirect();
        }
      
        if($rlcChoice3 != 'none' && (!isset($question2) || empty($question2))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please respond to all of the short answer questions.');
            $errorCmd->redirect();
        }

        // Check response lengths
        $wordLimit = 500;
        if(str_word_count($question0) > $wordLimit){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your answer to question number one is too long. Please limit your response to 500 words or less.');
            $errorCmd->redirect();
        }

        if($rlcChoice2 != 'none' && str_word_count($question1) > $wordLimit){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your answer to question number two is too long. Please limit your response to 500 words or less.');
            $errorCmd->redirect();
        }

        if($rlcChoice3 != 'none' && str_word_count($question2) > $wordLimit){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your answer to question number three is too long. Please limit your response to 500 words or less.');
            $errorCmd->redirect();
        }
        
        // Create the application, populate the values and save it
        $app = new HMS_RLC_Application();
        
        $app->setUsername($student->getUsername());
        $app->setDateSubmitted(time());
        $app->setFirstChoice($rlcChoice1);
        $app->setSecondChoice($rlcChoice2);
        $app->setThirdChoice($rlcChoice3);
        
        $app->setWhySpecificCommunities($why);
        $app->setStrengthsWeaknesses($contribute);
       
        $app->setRLCQuestion0($question0);
        $app->setRLCQuestion1($question1);
        $app->setRLCQuestion2($question2);

        $app->setTerm($term);
        $app->setApplicationType(RLC_APP_RETURNING);
        
        $app->save();
        
        // Redirect back to the main menu
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your Residential Learning Community Re-application was saved successfully.');
        $menuCmd->redirect();
    }
}
