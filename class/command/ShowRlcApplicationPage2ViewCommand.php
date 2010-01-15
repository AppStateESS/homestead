<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationPage2View.php');

class ShowRlcApplicationPage2ViewCommand extends Command {
  private $requestVars = array('action'=>'ShowRlcApplicationPage2View');

    public function setRequestVars(Array $vars){
        $this->requestVars = $vars;
    }

    public function getRequestVars(){
        return $this->requestVars;
    }

    public function execute(CommandContext $context){
        $view = new RlcApplicationPage2View($context);

        $context->setContent($view->show());
    }
}

?>
