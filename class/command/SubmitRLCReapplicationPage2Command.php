<?php

class SubmitRLCReapplicationPage2Command extends Command {
    
    
    private $vars;
    private $term;

    public function setTerm($term){
        $this->term = $term;
    }
    
    public function getRequestVars()
    {
        $reqVars = array();
        
        $reqVars['term'] = $this->term;
        $reqVars['action'] = 'SubmitRLCReapplicationPage2';
        return $reqVars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Assignment.php');
        
        session_write_close();
        session_start();

        $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
        
        if(!isset($_SESSION['RLC_REAPP'])){
            $menuCmd->redirect();
        }
        
        $reApp = $_SESSION['RLC_REAPP'];
        
        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        $errorCmd = CommandFactory::getCommand('ShowRlcReapplicationPageTwo');
        $errorCmd->setTerm($term);
        
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
        
        $question0 = $context->get('rlc_question_0');
        $question1 = $context->get('rlc_question_1');
        $question2 = $context->get('rlc_question_2');

        $reApp->rlc_question_0 = $question0;
        $reApp->rlc_question_1 = $question1;
        $reApp->rlc_question_2 = $question2;
        
        $_SESSION['RLC_REAPP'] = $reApp;
        
        $rlcChoice0 = $reApp->rlc_first_choice_id;
        $rlcChoice1 = $reApp->rlc_second_choice_id;
        $rlcChoice2 = $reApp->rlc_third_choice_id;
        
        if(isset($rlcChoice1) && (!isset($question1) || empty($question1))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please respond to all of the short answer questions.');
            $errorCmd->redirect();
        }
      
        if(isset($rlcChoice2) && (!isset($question2) || empty($question2))){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please respond to all of the short answer questions.');
            $errorCmd->redirect();
        }

        // Check response lengths
        $wordLimit = 500;
        if(str_word_count($question0) > $wordLimit){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your answer to question number one is too long. Please limit your response to 500 words or less.');
            $errorCmd->redirect();
        }

        if(isset($rlcChoice2) && str_word_count($question1) > $wordLimit){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your answer to question number two is too long. Please limit your response to 500 words or less.');
            $errorCmd->redirect();
        }

        if(isset($rlcChoice3) && str_word_count($question2) > $wordLimit){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Your answer to question number three is too long. Please limit your response to 500 words or less.');
            $errorCmd->redirect();
        }
        
        $reApp->setDateSubmitted(time());
        
        $reApp->setRLCQuestion0($question0);
        $reApp->setRLCQuestion1($question1);
        $reApp->setRLCQuestion2($question2);

        $reApp->setTerm($term);
        $reApp->setApplicationType(RLC_APP_RETURNING);
        
        $reApp->save();
        
        unset($_SESSION['RLC_REAPP']);
        
        // Redirect back to the main menu
        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Your Residential Learning Community Re-application was saved successfully.');
        $menuCmd->redirect();
    }
}
