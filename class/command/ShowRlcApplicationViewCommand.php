<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationView.php');

class ShowRlcApplicationViewCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ShowRlcApplicationView');
    }

    public function execute(CommandContext $context){
        $view = new RlcApplicationView();

        $context->setContent($view->show());
    }
}

?>
