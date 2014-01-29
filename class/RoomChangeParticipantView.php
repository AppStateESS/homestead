<?php
PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HMS_Bed.php');


/**
 * View class that represents a single participant in the
 * RoomChangeManageView
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeParticipantView extends View {

    private $participant; // The single partticpant this view is for
    private $request; // The parent request that this particpant is a part of
    private $participants; // Array of all participants involved in this request
    private $student;

    /**
     *
     * @param RoomChangeParticipant $participant The RoomChangeParticipant this view represents
     * @param RoomChangeRequest $request
     * @param array<RoomChangeParticipant> $participants All participants on this request
     */
    public function __construct(RoomChangeParticipant $participant, RoomChangeRequest $request, Array $participants)
    {
        $this->participant = $participant;
        $this->request = $request;
        $this->participants = $participants;

        $this->student = StudentFactory::getStudentByBannerId($this->participant->getBannerId(), Term::getSelectedTerm());
    }

    public function show()
    {
        $tpl = array();

        // Student info
        $tpl['NAME'] = $this->student->getProfileLink();
        $tpl['BANNER_ID'] = $this->student->getBannerId();

        // Participant ID
        // $tpl['PARTICIPANT_ID'] = $this->participant->getId();

        $tpl['CELL_PHONE'] = $this->participant->getCellPhone();

        // Hall Preferences
        $pref1 = $this->participant->getHallPref1();
        $pref2 = $this->participant->getHallPref2();

        if (!is_null($pref1)) {
            $hall1 = new HMS_Residence_Hall($pref1);
            $hallName = $hall1->getHallName();

            // Check if there's also a second hall preference
            if (!is_null($pref2)) {
                $hall2 = new HMS_Residence_Hall($pref2);
                $hallName .= ', ' . $hall2->getHallName();
            }

            $tpl['HALL_PREF'] = $hallName;
        }

        // From bed
        $fromBed = new HMS_Bed($this->participant->getFromBed());
        $tpl['FROM_ROOM'] = $fromBed->where_am_i();

        // To bed
        $toBedId = $this->participant->getToBed();
        if (isset($toBedId)) {
            // If there's already a bed set, show the selected bed
            $toBed = new HMS_Bed($toBedId);
            $tpl['TO_ROOM'] = $toBed->where_am_i();
        }


        /**
         * **
         * Show approval buttons based on participant's current state
         */
        $particpantState = $this->participant->getState();

        $form = new PHPWS_Form('participant_form');

        if ($particpantState instanceof ParticipantStateNew) {
            // Particpant is in new state, waiting on this student'a approval

            // If the student is logged in, or the user is an admin, show the approve button
            if(UserStatus::getUsername() == $this->student->getUsername()
                || Current_User::allow('hms', 'admin_approve_room_change')) {

                $approveCmd = CommandFactory::getCommand('RoomChangeStudentApprove');
                $approveCmd->setParticipantId($this->participant->getId());
                $approveCmd->setRequestId($this->request->getId());
                $approveCmd->initForm($form);

                $form->mergeTemplate($tpl);
                $tpl = $form->getTemplate();

                $tpl['APPROVE_BTN'] = ''; // dummy tag for approve button
            }

        } else if ($particpantState instanceof ParticipantStateStudentApproved) {
            // Participant is in StudentApproved state

            // Get current list of RDs for this participant
            $rds = $this->participant->getCurrentRdList();

                // If current user is an RD for the "from bed" or an admin
            if (in_array(UserStatus::getUsername(), $rds) || Current_User::allow('hms', 'admin_approve_room_change')) {

                if (!isset($toBedId) && count($this->participants) == 1) {
                    /*
                     * If there's only one particpant and the toBed is not already set,
                     * and the currnent user if the participants current RD, then show the bed selector
                     *
                     * Limit to 1 participant since room selection is for room "switch" requests only, not swaps.
                     * For swaps, the destination bed is already known and is not editable.
                     */
                    // Show the "select a bed" dialog, values are loaded via AJAX
                    $form->addDropBox('bed_select', array(
                            '-1' => 'Loading...'
                    ));
                    $form->addHidden('gender', $this->student->getGender());
                }

                $approveCmd = CommandFactory::getCommand('RoomChangeCurrRdApprove');
                $approveCmd->setParticipantId($this->participant->getId());
                $approveCmd->setRequestId($this->request->getId());
                $approveCmd->initForm($form);

                $form->mergeTemplate($tpl);
                $tpl = $form->getTemplate();

                $tpl['APPROVE_BTN'] = ''; // dummy tag for approve button
            }
        } else if ($particpantState instanceof ParticipantStateCurrRdApproved) {
            // Current RD has approved, Future RD needs to approve
            // If current user if future RD or admin, show the approve button

            // Get list of future RDs for "to bed"
            $rds = $this->participant->getFutureRdList();

            // Only future RDs and admins can approve
            if (in_array(UserStatus::getUsername(), $rds) || Current_User::allow('hms', 'admin_approve_room_change')) {

                $approveCmd = CommandFactory::getCommand('RoomChangeFutureRdApprove');
                $approveCmd->setParticipantId($this->participant->getId());
                $approveCmd->setRequestId($this->request->getId());
                $approveCmd->initForm($form);

                $form->mergeTemplate($tpl);
                $tpl = $form->getTemplate();

                $tpl['APPROVE_BTN'] = '';
            }
        }

        // Show the edit link for to room if request type is a "switch", user has permissions, and status allows it
        // TODO

        /*** Participant History ***/
        $states = RoomChangeParticipantStateFactory::getStateHistory($this->participant);

        if (!is_null($states)) {
            $stateRows = array();
            foreach ($states as $historyState) {
                $stateRows[] = array(
                        'STATE_NAME' => $historyState->getFriendlyName(),
                        'EFFECTIVE_DATE' => date('M j, Y g:ia', $historyState->getEffectiveDate()),
                        'COMMITTED_BY' => $historyState->getCommittedBy()
                );
            }
        }

        $tpl['history_rows'] = $stateRows;

        return PHPWS_Template::process($tpl, 'hms', 'admin/roomChangeParticipantView.tpl');
    }
}

?>
