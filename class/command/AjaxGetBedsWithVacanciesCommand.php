<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetBedsWithVacanciesCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'AjaxGetBedsWithVacancies');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        //TODO check for a floorId

        $room = new HMS_Room($context->get('roomId'));

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

?>