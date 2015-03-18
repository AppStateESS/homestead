<?php

class ShowRoommateProfileFormCommand extends Command {
    
    private $term;
    
    public function setTerm($term){
        $this->term = $term;
    }
    
    public function getRequestVars()
    {
        return array('action'=>'ShowRoommateProfileForm', 'term'=>$this->term);
    }
    
    public function execute(CommandContext $context)
    {
        // TODO make sure the application feature is really enabled
        PHPWS_Core::initModClass('hms','RoommateProfile.php');
        PHPWS_Core::initModClass('hms', 'RoommateProfileFormView.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        
        $term = $context->get('term');
        $student = StudentFactory::getStudentByUsername(UserStatus::getUsername(), $term);
        $banner = $student->getBannerID();

        $profile = RoommateProfileFactory::getProfile($banner, $term);
        
        $view = new RoommateProfileFormView($profile, $term);
        $context->setContent($view->show());
    }
}

?>