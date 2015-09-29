<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetBedsCommand extends Command {

    private $roomId;

    public function getRequestVars(){
        return array('action'=>'AjaxGetBeds');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Room.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        //TODO check for a hallId

        $room = new HMS_Room($context->get('roomId'));

        $bedsResult = $room->get_beds();

        $beds = array();
        $i = 0;

        foreach ($bedsResult as $bed)
        {
            $beds[$i]['bed_letter'] = strtoupper($bed->getBedroomLabel()) . $bed->getLetter();
            $beds[$i]['bed_id'] = $bed->getId();
            $i++;
        }

        $context->setContent(json_encode($beds));
    }
}
