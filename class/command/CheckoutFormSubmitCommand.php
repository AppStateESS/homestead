<?php
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');
PHPWS_Core::initModClass('hms', 'RoomDamageResponsibilityFactory.php');
PHPWS_Core::initModClass('hms', 'BedFactory.php');


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
        $bannerId = filter_input(INPUT_POST, 'bannerId', FILTER_VALIDATE_INT);
        $checkinId = filter_input(INPUT_POST, 'checkinId', FILTER_VALIDATE_INT);

        if (empty($bannerId)) {
            throw new InvalidArgumentException('Missing banner id.');
        }

        if (empty($checkinId)) {
            throw new InvalidArgumentException('Missing checkin id.');
        }

        // Check for key code
        //$keyCode = filter_input(INPUT_POST, 'keyCode',FILTER_SANITIZE_SPECIAL_CHARS);
        $keyCode = filter_input(INPUT_POST, 'keyCode',FILTER_VALIDATE_REGEXP, array('options'=>array('regexp'=>'/[^\W]/')));
        $keyReturned = filter_input(INPUT_POST, 'keyReturned', FILTER_VALIDATE_BOOLEAN);

        if (!isset($keyReturned) || !isset($keyCode)) {
            throw new InvalidArgumentException('Missing key return information.');
        }

        if ($keyReturned == "1" && empty($keyCode)) {
            throw new InvalidArgumentException('Missing key code.');
        }

        $properCheckout = filter_input(INPUT_POST, 'properCheckout', FILTER_VALIDATE_BOOLEAN);

        $term = Term::getSelectedTerm();
        $this->term = $term;

        // Lookup the student
        $student = StudentFactory::getStudentByBannerId($bannerId, $term);

        // Get the existing check-in
        $checkin = CheckinFactory::getCheckinById($checkinId);

        // Make sure we found a check-in
        if (is_null($checkin)) {
            /*
            NQ::simple('hms', hms\NotificationView::ERROR, "Sorry, we couldn't find a corresponding check-in for this check-out.");
            $errorCmd = CommandFactory::getCommand('ShowCheckoutForm');
            $errorCmd->setBannerId($bannerId);
            $errorCmd->setHallId($hallId);
            $errorCmd->redirect();
            */

            throw new Exception('Could not find a corresponding checkin.');
        }

        // Create the bed
        $bed = BedFactory::getBedByPersistentId($checkin->getBedPersistentId(), $term);

        // Get the room
        $room = $bed->get_parent();


        /*****
         * Add new damages
         */
        $newDamages = filter_input(INPUT_POST, 'newDamage', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);

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

            $improperNote = filter_input(INPUT_POST, 'improperNote', FILTER_SANITIZE_SPECIAL_CHARS);
            // Add damage for improper checkout
            // TODO: Find a better way to handle the magic number for dmg type
            $dmg = array('damage_type'=>105, 'side'=>'both', 'note'=>$improperNote, 'residents' => array(array('studentId'=> $student->getBannerId(), 'selected'=>true)));
            $this->addDamage($dmg, $room);

            // Add the improper checkout note
            $checkin->setImproperCheckoutNote($improperNote);
        }

        if ($keyReturned == "1") {
            $checkin->setKeyNotReturned(false);
        } else {
            $checkin->setKeyNotReturned(true);

            // Add a damage record for key not returned
            // TODO: Find a better way to handle the magic number for dmg type
            $dmg = array('damage_type'=>79, 'side'=>'both', 'note'=>'Key not returned.', 'residents' => array(array('studentId'=> $student->getBannerId(), 'selected'=>true)));
            $this->addDamage($dmg, $room);
        }

        // Save the check-in
        $checkin->save();

        // Add this to the activity log
        HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_CHECK_OUT, UserStatus::getUsername(), $bed->where_am_i());

        // Generate the RIC

        PHPWS_Core::initModClass('hms', 'InfoCard.php');
        PHPWS_Core::initModClass('hms', 'InfoCardPdfView.php');
        $infoCard = new InfoCard($checkin);

        /*
         * Info card removed per #869
        $infoCardView = new InfoCardPdfView();
        $infoCardView->addInfoCard($infoCard);
        */

        // Send confirmation Email with the RIC form to the student
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        HMS_Email::sendCheckoutConfirmation($student, $infoCard);

        /***** Room Change Request Handling *******/

        // Check if this checkout was part of a room change request
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
        $request = RoomChangeRequestFactory::getRequestPendingCheckout($student, $term);

        if (!is_null($request)) {
            $participant = RoomChangeParticipantFactory::getParticipantByRequestStudent($request, $student);

            // Transition to StudentApproved state
            $participant->transitionTo(new ParticipantStateCheckedOut($participant, time(), null, UserStatus::getUsername()));

            // If all the participants are in CheckedOut state, then this room change is complete, so transition it
            if($request->allParticipantsInState('CheckedOut')) {
                $request->transitionTo(new RoomChangeStateComplete($request, time(), null, UserStatus::getUsername()));
            }
        }

        // Cleanup and redirect
        NQ::simple('hms', hms\NotificationView::SUCCESS, 'Checkout successful.');
        NQ::close();
        exit;
    }

    private function addDamage(Array $dmg, HMS_Room $room)
    {
        $damage = new RoomDamage($room, $this->term, $dmg['damage_type'], $dmg['side'], $dmg['note']);

        // Save the damage
        RoomDamageFactory::save($damage);

        // Determine the residents which were responsible
        // For each resident submitted
        foreach ($dmg['residents'] as $resident) {

            // If the resident was selected as being responsible for this damage
            if(isset($resident['selected']) && $resident['selected']){
                // Create the student
                $student = StudentFactory::getStudentByBannerId($resident['studentId'], $this->term);

                // Create the responsibility
                $resp = new RoomDamageResponsibility($student, $damage);
                RoomDamageResponsibilityFactory::save($resp);
            }
        }
    }
}
