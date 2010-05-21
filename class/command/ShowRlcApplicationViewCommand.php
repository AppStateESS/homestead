<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationView.php');

class ShowRlcApplicationViewCommand extends Command {

    private $term;

    public function setTerm($term){
        $this->term = $term;
    }

    public function getRequestVars(){
        return array('action'=>'ShowRlcApplicationView', 'term'=>$this->term);
    }

    public function execute(CommandContext $context){
        $view = new RlcApplicationView($context->get('term'));

        $context->setContent($view->show());
    }
}

?>
