<?php


class ShowCheckoutFormCommand extends Command {

    private $bannerId;

    private $hallId;

    public function setBannerId($bannerId)
    {
        $this->bannerId = $bannerId;
    }

    public function setHallId($hallId)
    {
        $this->hallId = $hallId;
    }

    public function getRequestVars()
    {
        return array (
                'action' => 'ShowCheckoutForm',
                'bannerId' => $this->bannerId,
                'hallId' => $this->hallId
        );
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');

        $term = Term::getCurrentTerm();

        $bannerId = $context->get('bannerId');
        $hallId = $context->get('hallId');

        $errorCmd = CommandFactory::getCommand('ShowCheckoutStart');

        if (!isset($bannerId) || is_null($bannerId) || $bannerId == '') {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Missing Banner ID.');
            $errorCmd->redirect();
        }

        if (!isset($hallId)) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Missing residence hall ID.');
            $errorCmd->redirect();
        }

        // Check the Banner ID
        if (preg_match("/[\d]{9}/", $bannerId) == false) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Imporperly formatted Banner ID.');
            $errorCmd->redirect();
        }

        // Try to lookup the student in Banner
        try {
            $student = StudentFactory::getStudentByBannerId($bannerId, $term);
        } catch (StudentNotFoundException $e) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not locate a student with that Banner ID.');
            $errorCmd->redirect();
        }

        // Find the earliest checkin that matches hall the user selected
        $hall = new HMS_Residence_Hall($hallId);
        $checkin = CheckinFactory::getPendingCheckoutForStudentByHall($student, $hall);

        if(!isset($checkin)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, we couldn't find a matching check-in at {$hall->getHallName()} for this student to check-out of.");
            $errorCmd->redirect();
        }

        $bed = new HMS_Bed($checkin->getBedId());
        $room = $bed->get_parent();

        // Get the damages for this student's room
        $damages = RoomDamageFactory::getDamagesByRoom($room);

        PHPWS_Core::initModClass('hms', 'CheckoutFormView.php');
        $view = new CheckoutFormView($student, $hall, $room, $bed, $damages, $checkin);

        $context->setContent($view->show());
    }
}

?>
