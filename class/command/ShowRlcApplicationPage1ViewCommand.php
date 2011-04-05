<?php
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'RlcApplicationPage1View.php');

class ShowRlcApplicationPage1ViewCommand extends Command {

    private $term;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getRequestVars(){
        return array('action'=>'ShowRlcApplicationPage1View', 'term' => $this->term);
    }

    public function execute(CommandContext $context){

        $term = $context->get('term');

        if(!isset($term) || is_null($term) || empty($term)){
            throw new IllegalArgumentException('Missing term.');
        }

        $view = new RlcApplicationPage1View($context);

        $context->setContent($view->show());
    }
}

?>
