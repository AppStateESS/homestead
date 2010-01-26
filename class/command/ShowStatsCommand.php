<?php

class ShowStatsCommand extends Command {
    
    public function getRequestVars(){
        return array('action'=>'ShowStats');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StatsView.php');

        $view = new StatsView();

        $context->setContent($view->show());
    }
}

?>
