<?php

PHPWS_Core::initModClass('hms', 'Command.php');

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

        $floorsResult = $hall->getFloors();

        $floors = array();
        $i = 0;

        foreach ($floorsResult as $floor)
        {
          if(!empty($floor->get_rooms()))
          {
            $floors[$i]['floor_number'] = $floor->getFloorNumber();
            $floors[$i]['floor_id'] = $floor->getId();
            $i++;
          }
        }

        $context->setContent(json_encode($floors));
    }
}
