<?php

namespace Homestead\command;

use \Homestead\Command;

class AjaxGetFloorsCommand extends Command {

    private $buildingId;

    public function getRequestVars(){
        return array('action'=>'AjaxGetFloors');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        //TODO check for a hallId

        $hall = new HMS_Residence_Hall($context->get('hallId'));

        $floors = $hall->get_floors();

        #test($floors, 1);

        $json_floors = array();
        $json_floors[0] = 'Select...';

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
