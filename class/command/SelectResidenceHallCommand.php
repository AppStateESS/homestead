<?php

/**
 * @author Jeremy Booker <jbooker AT tux DOT appstate DOT edu>
 * @package hms
 */

class SelectResidenceHallCommand extends Command {

    private $onSelectCmd;
    private $title;

    public function setOnSelectCmd(Command $cmd){
        $this->onSelectCmd = $cmd;
    }

    public function setTitle($text = 'Select residence hall'){
        $this->title = $text;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'SelectResidenceHall', 'title'=>$this->title);

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

    public function getLink($text, $target = null, $cssClass = null, $title = null)
    {
        return $this->onSelectCmd->getSubLink($text, $this->getRequestVars());
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'SelectHallView.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $term = Term::getSelectedTerm();
        $halls = HMS_Residence_Hall::get_halls_array($term);

        $onSelectCmd = CommandFactory::getCommand($context->get('onSelectAction'));

        $hallView = new SelectHallView($onSelectCmd, $halls, $context->get('title'), $term);
        $context->setContent($hallView->show());
    }
}
