<?php


class ShowCheckinFormCommand extends Command {

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
                'action' => 'ShowCheckinForm',
                'bannerId' => $this->bannerId,
                'hallId' => $this->hallId
        );
    }

    public function execute(CommandContext $context)
    {
        // Check permissions
        if (!Current_User::allow('hms', 'checkin')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to checkin students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');

        $term = Term::getSelectedTerm();

        $bannerId = $context->get('bannerId');
        $hallId = $context->get('hallId');

        $errorCmd = CommandFactory::getCommand('ShowCheckinStart');

        if (!isset($bannerId) || is_null($bannerId) || $bannerId == '') {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Missing Banner ID.');
            $errorCmd->redirect();
        }

        if (!isset($hallId)) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Missing residence hall ID.');
            $errorCmd->redirect();
        }

        // Check the Banner ID
        if (preg_match("/[\d]{9}/", $bannerId) == false) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Imporperly formatted Banner ID.');
            $errorCmd->redirect();
        }

        // Try to lookup the student in Banner
        try {
            $student = StudentFactory::getStudentByBannerId($bannerId, $term);
        } catch (StudentNotFoundException $e) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Could not locate a student with that Banner ID.');
            $errorCmd->redirect();
        }

        // Make sure the student is assigned in the current term
        $assignment = HMS_Assignment::getAssignmentByBannerId($bannerId, $term);
        if (!isset($assignment) || is_null($assignment)) {
            NQ::simple('hms', hms\NotificationView::ERROR, $student->getName() . ' is not assigned for ' . Term::toString($term) . '. Please contact the University Housing Assignments Office at 828-262-6111.');
            $errorCmd->redirect();
        }

        // Make sure the student's assignment matches the hall the user selected
        $bed = $assignment->get_parent();
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $hall = $floor->get_parent();

        if ($hallId != $hall->getId()) {
            NQ::simple('hms', hms\NotificationView::ERROR, 'Wrong hall! ' . $student->getName() . ' is assigned to ' . $assignment->where_am_i());
            $errorCmd->redirect();
        }

        // Load any existing check-in
        PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
        $checkin = CheckinFactory::getLastCheckinByBannerId($bannerId, $term);

        // var_dump($checkin);
        // exit();

        // If there is a checkin for the same bed, and the difference between the current time and the checkin time is
        // greater than 48 hours, then show an error.
        if (!is_null($checkin) && $checkin->getBedId() == $bed->getId() && (time() - $checkin->getCheckinDate()) > Checkin::CHECKIN_TIMEOUT && is_null($checkin->getCheckoutDate())) {
              NQ::simple('hms', hms\NotificationView::ERROR, $student->getName() . ' has already checked in to ' . $assignment->where_am_i());
              $errorCmd->redirect();
        }

        PHPWS_Core::initModClass('hms', 'CheckinFormView.php');
        $view = new CheckinFormView($student, $assignment, $hall, $floor, $room, $checkin);

        $context->setContent($view->show());
    }
}

