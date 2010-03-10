<?php

class ShowRoommateProfileCommand extends Command {
    
    private $username;
    private $term;
    
    public function setUsername($user){
        $this->username = $user;
    }
    
    public function setTerm($term){
        $this->term = $term;
    }
    
    public function getRequestVars(){
        return array('action'=>'ShowRoommateProfile', 'term'=>$this->term, 'username'=>$this->username);
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'RoommateProfile.php');
        PHPWS_Core::initModClass('hms', 'RoommateProfileView.php');
        
        $student = StudentFactory::getStudentByUsername($context->get('username'), $context->get('term'));
        $profile = RoommateProfile::getProfile($context->get('username'), $context->get('term'));
        
        $view = new RoommateProfileView($student, $profile);
        $context->setContent($view->show());
    }
}

?>