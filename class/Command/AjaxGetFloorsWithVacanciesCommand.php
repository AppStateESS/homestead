<?php

namespace Homestead\Command;

use \Homestead\ResidenceHall;
use \Homestead\HMS_Util;

class AjaxGetFloorsWithVacanciesCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'AjaxGetFloorsWithVacancies');
    }

    public function execute(CommandContext $context)
    {
        //TODO check for a hallId

        $hall = new ResidenceHall($context->get('hallId'));

        $floors = $hall->getFloorsWithVacancies();

        $json_floors = array();
        $json_floors[0] = 'Select ...';

        foreach ($floors as $floor){
            unset($text);

            $text = $floor->floor_number;

            if($hall->gender_type == COED && $floor->gender_type != COED){
                $text .= (' (' . HMS_Util::formatGender($floor->gender_type) . ')');
            }

            $json_floors[$floor->id] = $text;
        }

        $context->setContent(json_encode($json_floors));
    }
}
