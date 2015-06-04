<?php

class ShowFrontPageCommand extends Command {
    
    public function getRequestVars(){
        return array('action'=>'GetFrontPage');
    }
    
    public function execute(CommandContext $context)
    {
        
        PHPWS_Core::initModClass('hms', 'FrontPageView.php');
        
        $view = new FrontPageView();
        
        $context->setContent($view->show());
    }
}

