<?php
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
PHPWS_Core::initModClass('hms', 'Checkin.php');
PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');


class CheckinFormSubmitCommand extends Command {

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
                'action' => 'CheckinFormSubmit',
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

        $bannerId = $context->get('bannerId');
        $hallId = $context->get('hallId');

        // Check for key code
        $keyCode = $context->get('key_code');

        if (!isset($keyCode) || $keyCode == '') {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please enter a key code.');
            $errorCmd = CommandFactory::getCommand('ShowCheckinForm');
            $errorCmd->setBannerId($bannerId);
            $errorCmd->setHallId($hallId);
            $errorCmd->redirect();
        }

        $term = Term::getSelectedTerm();

        // Lookup the student
        $student = StudentFactory::getStudentByBannerId($bannerId, $term);

        // Get the student's current assignment
        $assignment = HMS_Assignment::getAssignmentByBannerId($bannerId, $term);
        $bed = $assignment->get_parent();

        // Get the currently logged in user
        $currUser = Current_User::getUsername();

        // Check for an existing Check-in
        $checkin = CheckinFactory::getCheckinByBed($student, $bed, $term);

        // If there's not already a checkin for this bed, create a new one
        if (is_null($checkin)) {
            $checkin = new Checkin($student, $bed, $term, $currUser, $keyCode);
        } else {
            // Otherwise, update the existing checkin
            $updatedCheckin = new Checkin($student, $bed, $term, $currUser, $keyCode);
            $updatedCheckin->substitueForExistingCheckin($checkin); // Use the old checkin to replace this one
            $checkin = $updatedCheckin;
            // $checkin->setCheckoutBy($currUser);
            // $checkin->setCheckinDate(time());
            // $checkin->setKeyCode($keyCode);
        }

        $checkin->save();

        // Add this to the activity log
        HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_CHECK_IN, UserStatus::getUsername(), $assignment->where_am_i());


        // Generate the RIC
        PHPWS_Core::initModClass('hms', 'InfoCard.php');
        PHPWS_Core::initModClass('hms', 'InfoCardPdfView.php');
        $infoCard = new InfoCard($checkin);

        $infoCardView = new InfoCardPdfView();
        $infoCardView->addInfoCard($infoCard);

        // Send confirmation Email with the RIC form to the student
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::sendCheckinConfirmation($student, $infoCard, $infoCardView);

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Checkin successful.');

        // Redirect to success page with option to print check-in document.
        $cmd = CommandFactory::getCommand('ShowCheckinDocument');
        $cmd->setBannerId($student->getBannerId());
        $cmd->setCheckinId($checkin->getId());
        $cmd->redirect();
    }
}
?>