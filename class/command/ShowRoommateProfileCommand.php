<?php

class ShowRoommateProfileCommand extends Command {
    
    //private $username;
    private $term;
    private $bannerid;
    
    /*public function setUsername($user){
        $this->username = $user;
    }*/
    
    public function setTerm($term){
        $this->term = $term;
    }

    public function setBannerID($bannerid){
        $this->bannerid = $bannerid;
    }
    
    public function getRequestVars(){
        return array('action'=>'ShowRoommateProfile', 'term'=>$this->term, 'bannerid'=>$this->bannerid);
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'RoommateProfile.php');
        PHPWS_Core::initModClass('hms', 'RoommateProfileView.php');

        $student = StudentFactory::getStudentByBannerID($context->get('bannerid'), $context->get('term'));
        $profile = RoommateProfileFactory::getProfile($context->get('bannerid'), $context->get('term'));
        
        $view = new RoommateProfileView($student, $profile);
        $context->setContent($view->show());
    }
}

?>