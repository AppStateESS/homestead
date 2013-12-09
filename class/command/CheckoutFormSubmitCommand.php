<?php
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');
PHPWS_Core::initModClass('hms', 'RoomDamageResponsibilityFactory.php');


class CheckoutFormSubmitCommand extends Command {

    private $term;

    public function getRequestVars()
    {
        // Handeled by Angular, so we don't need anything here
        return array ();
    }

    public function execute(CommandContext $context)
    {
        // Check permissions
        if (!Current_User::allow('hms', 'checkin')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to checkin students.');
        }

        // Grab data from JSON source
        $data = $context->getJsonData();

        $bannerId = $data['bannerId'];
        $checkinId = $data['checkinId'];

        if (!isset($bannerId) || $bannerId == '') {
            throw new InvalidArgumentException('Missing banner id.');
        }

        if (!isset($checkinId) || $checkinId == '') {
            throw new InvalidArgumentException('Missing checkin id.');
        }

        // Check for key code
        $keyCode = $data['keyCode'];
        $keyReturned = $data['keyReturned'];

        if (!isset($keyReturned) || !isset($keyCode)) {
            throw new InvalidArgumentException('Missing key code.');
        }

        if ($keyReturned == "0" && $keyCode == '') {
            throw new InvalidArgumentException('Missing key code 2.');
        }

        $properCheckout = $data['properCheckout'];

        $term = Term::getCurrentTerm();
        $this->term = $term;

        // Lookup the student
        $student = StudentFactory::getStudentByBannerId($bannerId, $term);

        // Get the existing check-in
        $checkin = CheckinFactory::getCheckinById($checkinId);

        // Make sure we found a check-in
        if (is_null($checkin)) {
            /*
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, we couldn't find a corresponding check-in for this check-out.");
            $errorCmd = CommandFactory::getCommand('ShowCheckoutForm');
            $errorCmd->setBannerId($bannerId);
            $errorCmd->setHallId($hallId);
            $errorCmd->redirect();
            */

            throw new Exception('Could not find a corresponding checkin.');
        }

        // Create the bed
        $bed = new HMS_Bed($checkin->getBedId());

        // Get the room
        $room = $bed->get_parent();


        /*****
         * Add new damages
         */

        $newDamages = $data['newDamages'];

        if (!empty($newDamages)) {
            foreach ($newDamages as $dmg) {
                $this->addDamage($dmg, $room);
            }
        }

        /******
         * Complete the Checkout
         */

        // Set checkout date and user
        $checkin->setCheckoutDate(time());
        $checkin->setCheckoutBy(Current_User::getUsername());

        // Set the checkout code code, if any
        $checkin->setCheckoutKeyCode($keyCode);

        // Improper checkout handling
        if ($properCheckout == "1") {
            $checkin->setImproperCheckout(false);
        } else {
            $checkin->setImproperCheckout(true);
        }

        if ($keyReturned == "1") {
            $checkin->setKeyNotReturned(false);
        } else {
            $checkin->setKeyNotReturned(true);
        }


        // Save the check-in
        $checkin->save(); //TODO uncomment this before production

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
        //$cmd->redirect();

        header('HTTP/1.1 201 Created');
        $path = $cmd->getURI();
        header("Location: $path");

    }

    private function addDamage(Array $dmg, HMS_Room $room)
    {
        // Create the damage
        $damage = new RoomDamage($room, $dmg['type'], $dmg['side'], $dmg['details']);

        // Save the damage
        RoomDamageFactory::save($damage);

        // Determine the residents which were responsible

        // For each resident submitted
        foreach ($dmg['residents'] as $resident) {

            // If the resident was selected as being responsible for this damage
            if($resident['selected']){
                // Create the student
                $student = StudentFactory::getStudentByBannerId($resident['studentId'], $this->term);

                // Create the responsibility
                $resp = new RoomDamageResponsibility($student, $damage);
                RoomDamageResponsibilityFactory::save($resp);
            }
        }
    }
}
?>
