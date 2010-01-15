<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationPage1View.php');

class ShowRlcApplicationPage1ViewCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowRlcApplicationPage1View');
    }

    public function execute(CommandContext $context){
        $view = new RlcApplicationPage1View($context);

        $context->setContent($view->show());
    }
}

?>
