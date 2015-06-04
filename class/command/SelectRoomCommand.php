<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 */

class SelectRoomCommand extends Command {

    private $onSelectCmd;
    private $title;

    public function setOnSelectCmd(Command $cmd){
        $this->onSelectCmd = $cmd;
    }

    public function setTitle($text = 'Select a room'){
        $this->title = $text;
    }

    function getRequestVars()
    {
        $vars = array('action'=>'SelectRoom', 'title'=>$this->title);
         
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
        PHPWS_Core::initModClass('hms', 'SelectRoomView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $term = Term::getSelectedTerm();
        $halls = HMS_Residence_Hall::getHallsDropDownValues($term);
         
        $onSelectCmd = CommandFactory::getCommand($context->get('onSelectAction'));
        $onSelectCmd->setRoomId($context->get('room'));
         
        $roomView = new SelectRoomView($onSelectCmd, $halls, $context->get('title'), $term);
        $context->setContent($roomView->show());
    }
}


