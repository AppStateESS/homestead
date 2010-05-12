<?php

PHPWS_Core::initModClass('hms', 'FloorAssignmentView.php');
class ShowFloorAssignmentViewCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $view = new FloorAssignmentView($context->get('floor'));

        $context->setContent($view->show());
    }
}
?>
