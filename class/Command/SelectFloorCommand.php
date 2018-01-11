<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\ResidenceHall;
use \Homestead\CommandFactory;
use \Homestead\SelectFloorView;

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class SelectFloorCommand extends Command {

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
        $vars = array('action'=>'SelectFloor', 'title'=>$this->title);

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
        $term = Term::getSelectedTerm();
        $halls = ResidenceHall::getHallsDropDownValues($term);

        $onSelectCmd = CommandFactory::getCommand($context->get('onSelectAction'));
        $onSelectCmd->setFloorId($context->get('floor'));

        $floorView = new SelectFloorView($onSelectCmd, $halls, $context->get('title'), $term);
        $context->setContent($floorView->show());
    }
}
