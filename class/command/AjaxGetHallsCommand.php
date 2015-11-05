<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetHallsCommand extends Command {

    private $floorId;

    public function getRequestVars(){
        return array('action'=>'AjaxGetHalls');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');

        $term = Term::getSelectedTerm();

        $hallsResults = ResidenceHallFactory::getHallsForTerm($term);

        $halls = array();

        $i = 0;

        foreach ($hallsResults as $hall)
        {
          $halls[$i]['hall_name'] = $hall->getHallName();
          $halls[$i]['hall_id'] = $hall->getId();
          $i++;
        }

        $context->setContent(json_encode($halls));
    }
}
