<?php

class ShowOffCampusWaitListApplicationCommand extends Command {

    private $term;
    private $agreedToTerms;

    public function setTerm($term){
        $this->term = $term;
    }

    public function setAgreedToTerms($terms){
        $this->agreedToTerms = $terms;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowOffCampusWaitListApplication', 'term'=>$this->term);
        
        if(isset($this->agreedToTerms)){
            $vars['agreedToTerms'] = $this->agreedToTerms;
        }
        
        return $vars;
    }

    public function execute(CommandContext $context){

        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        
        $term = $context->get('term');

        # Check if the student has already applied. If so, redirect to the student menu
        $result = HousingApplication::checkForApplication(UserStatus::getUsername(), $term);

        if($result == TRUE){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You have already enrolled on the on-campus housing Open Waiting List for this term.');
            $menuCmd = CommandFactory::getCommand('ShowStudentMenu');
            $menuCmd->redirect();
        }

        # Make sure the student agreed to the terms, if not, send them back to the terms & agreement command
        $agreedToTerms = $context->get('agreedToTerms');

        # If they haven't agreed, redirect to the agreement
        if(is_null($agreedToTerms) || !isset($agreedToTerms) || $agreedToTerms != 1){
            $onAgree = CommandFactory::getCommand('ShowOffCampusWaitListApplication');
            $onAgree->setTerm($term);

            $agreementCmd = CommandFactory::getCommand('ShowTermsAgreement');
            $agreementCmd->setTerm($term);
            $agreementCmd->setAgreedCommand($onAgree);
            $agreementCmd->redirect();
        }
        
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        
        PHPWS_Core::initModClass('hms', 'ReApplicationOffCampusFormView.php');
        $view = new ReApplicationOffCampusFormView($student, $term);
        
        $context->setContent($view->show());
    }
}

?>