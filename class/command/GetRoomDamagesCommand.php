<?php

class GetRoomDamagesCommand extends Command {

    public function getRequestVars()
    {
        return array('action' => 'GetRoomDamagesCommand');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $term = Term::getCurrentTerm();

        $bannerId = $context->get('bannerId');
        $hallId = $context->get('hallId');

        if (!isset($bannerId) || is_null($bannerId) || $bannerId == '') {
            // 500
        }

        if (!isset($hallId)) {
            // 500
        }

        // Make sure it looks like a valid Banner ID
        if (preg_match("/[\d]{9}/", $bannerId) == false) {
            // 500 - Improperly formatted Banner Id
        }

        // Try to lookup the student in Banner
        try {
            $student = StudentFactory::getStudentByBannerId($bannerId, $term);
        } catch (StudentNotFoundException $e) {
            // 500 - Student couldn't be found
        }

        // Find the earliest checkin that matches hall the user selected
        $hall = new HMS_Residence_Hall($hallId);
        $checkin = CheckinFactory::getPendingCheckoutForStudentByHall($student, $hall);

        if(!isset($checkin)){
            // 500 - Couldn't find a matching checkin
        }

        $bed = new HMS_Bed($checkin->getBedId());
        $room = $bed->get_parent();

        // Get the damages for this student's room
        $damages = RoomDamageFactory::getDamagesByRoom($room);

        $context->setContent(json_encode($damages));
    }
}

?>