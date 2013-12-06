<?php

class GetRoomDamagesCommand extends Command {

    public function getRequestVars()
    {
        return array('action' => 'GetRoomDamagesCommand');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $term = Term::getCurrentTerm();

        $bedId = $context->get('bed_id');

        $bed = new HMS_Bed($bedId);
        $room = $bed->get_parent();

        // Get the damages for this student's room
        $damages = RoomDamageFactory::getDamagesByRoom($room);

        if($damages == null)
        {
            $context->setContent(json_encode(array()));
            return;
        }

        $context->setContent(json_encode($damages));
    }
}

?>
