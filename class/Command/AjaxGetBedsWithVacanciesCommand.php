<?php

namespace Homestead\Command;

 use \Homestead\Room;

class AjaxGetBedsWithVacanciesCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'AjaxGetBedsWithVacancies');
    }

    public function execute(CommandContext $context)
    {
        //TODO check for a floorId

        $room = new Room($context->get('roomId'));

        $beds = $room->getBedsWithVacancies();

        $json_beds = array();
        $json_beds[0] = 'Select ...';

        foreach ($beds as $bed){
            unset($text);

            $text = strtoupper($bed->bedroom_label) . $bed->bed_letter;

            /*
            if($bed->ra_bed == 1){
                $text .= ' (RA)';
            }*/

            $json_beds[$bed->id] = $text;
        }

        $context->setContent(json_encode($json_beds));
    }
}
