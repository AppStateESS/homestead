<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class SelectBedCommand extends Command {

    private $onSelectCmd;
    private $title;

    public function setOnSelectCmd(Command $cmd){
        $this->onSelectCmd = $cmd;
    }

    public function setTitle($text = 'Select a bed'){
        $this->title = $text;
    }

    function getRequestVars()
    {
        $vars = array('action'=>'SelectBed', 'title'=>$this->title);
        	
        if(!isset($this->onSelectCmd)){
            return $vars;
        }
        	
        // Get the action to do on select
        $onSelectVars = $this->onSelectCmd->getRequestVars();
        $onSelectAction = $onSelectVars['action'];

        // Unset it so it doesn't conflict
        unset($onSelectVars['action']);

        // Reset it under a different name
        $onSelectVars['onSelectAction'] = $onSelectAction;

        return array_merge($vars, $onSelectVars);
    }

    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'SelectBedView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $term = Term::getSelectedTerm();
        $halls = HMS_Residence_Hall::getHallsDropDownValues($term);
         
        $onSelectCmd = CommandFactory::getCommand($context->get('onSelectAction'));
        $onSelectCmd->setBedId($context->get('bed'));
         
        $bedView = new SelectBedView($onSelectCmd, $halls, $context->get('title'), $term);
        $context->setContent($bedView->show());
    }
}

?>
