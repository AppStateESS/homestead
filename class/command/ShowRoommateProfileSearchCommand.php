<?php

class ShowRoommateProfileSearchCommand extends Command {
    
    public function getRequestVars()
    {
        return array('action'=>'ShowRoommateProfileSearch');
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'RoommateProfileSearchForm.php');
        $view = new RoommateProfileSearchForm();
        
        $context->setContent($view->show());
    }
}
?>