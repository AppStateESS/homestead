<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class AssignByFloorCommand extends Command {

    private $onSelectCmd;
    private $title;

    public function setOnSelectCmd(Command $cmd){
        $this->onSelectCmd = $cmd;
    }

    public function setTitle($text = 'Select a floor'){
        $this->title = $text;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'AssignByFloor', 'title'=>$this->title);

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

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'AssignByFloorView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $term = Term::getSelectedTerm();
        $halls = HMS_Residence_Hall::getHallsDropDownValues($term);

        $onSelectCmd = CommandFactory::getCommand($context->get('onSelectAction'));
        $onSelectCmd->setFloorId($context->get('floor'));

        $floorView = new AssignByFloorView($onSelectCmd, $halls, $context->get('title'), $term);
        $context->setContent($floorView->show());
    }
}
