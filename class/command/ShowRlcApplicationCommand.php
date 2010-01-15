<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationReView.php');

class ShowRlcApplicationViewCommand extends Command {
    
    public function getRequestVars(){
        return array('action'=>'ShowRlcApplicationView');
    }

    public function execute(CommandContext $context){
        $view = new RlcApplicationReView();

        $context->setContent($view->show());
    }
}
?>