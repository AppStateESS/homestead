<?php

class ShowCancelHousingApplicationCommand extends Command {
    
    private $housingApp;
    
    public function setHousingApp(HousingApplication $app){
        $this->housingApp = $app;
    }
    
    public function getRequestVars()
    {
        return array('action'=>'ShowCancelHousingApplication', 'applicationId'=>$this->housingApp->getId());
    }
    
    public function getLink($text, $target = null, $cssClass = null, $title = null)
    {
        $uri = $this->getURI();
        return "<a href=\"$uri\" class=\"cancelAppLink\" onClick=\"return false;\">$text</a>";
    }
    
    public function execute(CommandContext $context)
    {
        $applicationId = $context->get('applicationId');
        
        if(!isset($applicationId)){
            throw new InvalidArgumentException('Missing application id.');
        }
        
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        $application = HousingApplicationFactory::getApplicationById($applicationId);
        
        PHPWS_Core::initModClass('hms', 'HousingApplicationCancelView.php');
        $view = new HousingApplicationCancelView($application);
        
        echo $view->show();
        exit;
    }
}


?>