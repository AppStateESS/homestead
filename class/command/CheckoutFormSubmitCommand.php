<?php
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');


class CheckoutFormSubmitCommand extends Command {

    private $bannerId;

    private $checkinId;

    public function setBannerId($bannerId)
    {
        $this->bannerId = $bannerId;
    }

    public function setCheckinId($checkinId)
    {
        $this->checkinId = $checkinId;
    }

    public function getRequestVars()
    {
        return array (
                'action'    => 'CheckoutFormSubmit',
                'bannerId'  => $this->bannerId,
                'checkinId' => $this->checkinId
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
        $checkinId = $context->get('checkinId');

        // Check for key code
        $keyCode = $context->get('key_code');
        $keyNotReturned = $context->get('key_not_returned');

        if (!isset($keyNotReturned) && (!isset($keyCode) || $keyCode == '')) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please enter a key code.');
            $errorCmd = CommandFactory::getCommand('ShowCheckoutForm');
            $errorCmd->setBannerId($bannerId);
            $errorCmd->setHallId($hallId);
            $errorCmd->redirect();
        }

        $improperCheckout = $context->get('improper_checkout');

        $term = Term::getCurrentTerm();

        // Lookup the student
        $student = StudentFactory::getStudentByBannerId($bannerId, $term);

        // Create the actual check-in and save it
        $currUser = Current_User::getUsername();

        // Get the existing check-in
        $checkin = CheckinFactory::getCheckinById($checkinId);

        // Make sure we found a check-in
        if (is_null($checkin)) {
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, we couldn't find a corresponding check-in for this check-out.");
            $errorCmd = CommandFactory::getCommand('ShowCheckoutForm');
            $errorCmd->setBannerId($bannerId);
            $errorCmd->setHallId($hallId);
            $errorCmd->redirect();
        }

        // Set checkout date and user
        $checkin->setCheckoutDate(time());
        $checkin->setCheckoutBy($currUser);

        // Improper checkout handling
        if (isset($improperCheckout)) {
            $checkin->setImproperCheckout(true);
        } else {
            $checkin->setImproperCheckout(false);
        }

        if (isset($keyNotReturned)) {
            $checkin->setKeyNotReturned(true);
        } else {
            $checkin->setKeyNotReturned(false);
        }

        // Save the check-in
        $checkin->save();

        // Create the bed
        $bed = new HMS_Bed($checkin->getBedId());

        // Add this to the activity log
        HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_CHECK_OUT, UserStatus::getUsername(), $bed->where_am_i());

        // Generate the RIC
        PHPWS_Core::initModClass('hms', 'InfoCard.php');
        PHPWS_Core::initModClass('hms', 'InfoCardPdfView.php');
        $infoCard = new InfoCard($checkin);

        $infoCardView = new InfoCardPdfView();
        $infoCardView->addInfoCard($infoCard);

        // Send confirmation Email with the RIC form to the student
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::sendCheckoutConfirmation($student, $infoCard, $infoCardView);

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Checkout successful.');

        // Redirect to start of checkout process
        //$cmd = CommandFactory::getCommand('ShowCheckoutStart');
        $cmd = CommandFactory::getCommand('ShowCheckoutDocument');
        $cmd->setCheckinId($checkin->getId());
        $cmd->redirect();
    }
}
?>
