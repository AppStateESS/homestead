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
        PHPWS_Core::initModClass('hms', 'BedFactory.php');

        $term = Term::getCurrentTerm();

        $bannerId = $context->get('bannerId');
        $hallId = $context->get('hallId');

        $errorCmd = CommandFactory::getCommand('ShowCheckoutStart');

        if (!isset($bannerId) || is_null($bannerId) || $bannerId == '') {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Missing student ID.');
            $errorCmd->redirect();
        }

        if (!isset($hallId)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Missing residence hall ID.');
            $errorCmd->redirect();
        }

        // If search string is all numeric, make sure it looks like a valid Banner ID
        if (is_numeric($bannerId) && preg_match("/[\d]{9}/", $bannerId) == false) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Imporperly formatted Banner ID.');
            $errorCmd->redirect();
        }
        // Try to lookup the student in Banner
        try {
            // If it's all numeric assume it's a student ID, otherwise assume it's a username
            if (is_numeric($bannerId) && strlen((string)$bannerId) == 9) {
                $student = StudentFactory::getStudentByBannerId($bannerId, $term);
            } else {
                $student = StudentFactory::getStudentByUsername($bannerId, $term);
            }
        } catch (StudentNotFoundException $e) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Could not locate a student with that Banner ID.');
            $errorCmd->redirect();
        }

        // Find the earliest checkin that matches hall the user selected
        $selectedHall = new HMS_Residence_Hall($hallId);
        $checkin = CheckinFactory::getPendingCheckoutForStudentByHall($student, $selectedHall);

        if(!isset($checkin)){
            NQ::simple('hms', hms\NotificationView::ERROR, "Sorry, we couldn't find a matching check-in at {$hall->getHallName()} for this student to check-out of.");
            $errorCmd->redirect();
        }

        // Get the bed by persistent id in the current term (pull the bed forward into the current term)
        $bed = BedFactory::getBedByPersistentId($checkin->getBedPersistentId(), $term);

        // Load the hall structure based on the current term
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $checkinHall = $floor->get_parent();

        // Check to make sure the hall ID for this bed in the current term matches the hall id that the user told us to use
        if($checkinHall->getId() != $hallId){
            NQ::simple('hms', hms\NotificationView::ERROR, "Sorry, we couldn't find a matching check-in at {$selectedHall->getHallName()} for this student to check-out of. They may have a pending check-out in {$checkinHall->getHallName()}");
            $errorCmd->redirect();
        }

        // Get the damages for this student's room
        $damages = RoomDamageFactory::getDamagesByRoom($room);

        PHPWS_Core::initModClass('hms', 'CheckoutFormView.php');
        $view = new CheckoutFormView($student, $selectedHall, $room, $bed, $damages, $checkin);

        $context->setContent($view->show());
    }
}
