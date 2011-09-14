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
        $term = $context->get('term');
        if(!isset($term) || is_null($term) || empty($term)){
            throw new InvalidArgumentException('Missing term.');
        }

        $view = new RlcApplicationView($term);

        $context->setContent($view->show());
    }
}

?>
