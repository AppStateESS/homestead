<?php

class ShowRoommateProfileCommand extends Command {
    
    //private $username;
    private $term;
    private $banner_id;
    
    /*public function setUsername($user){
        $this->username = $user;
    }*/
    
    public function setTerm($term){
        $this->term = $term;
    }

    public function setBannerID($bannerId){
        $this->banner_id = $bannerId;
    }
    
    public function getRequestVars(){
        return array('action'=>'ShowRoommateProfile', 'term'=>$this->term, 'banner_id'=>$this->banner_id);
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'RoommateProfile.php');
        PHPWS_Core::initModClass('hms', 'RoommateProfileView.php');

        $student = StudentFactory::getStudentByBannerID($context->get('banner_id'), $context->get('term'));
        $profile = RoommateProfileFactory::getProfile($context->get('banner_id'), $context->get('term'));
        
        $view = new RoommateProfileView($student, $profile);
        $context->setContent($view->show());
    }
}

?>