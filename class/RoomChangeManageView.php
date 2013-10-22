<?php

class RoomChangeManageView extends View {

    private $request;

    private $participants;

    public function __construct(RoomChangeRequest $request)
    {
        $this->request = $request;

        // Load each of the participants
        $this->participants = $this->request->getParticipants();
    }

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantView.php');


        // Get a username list of all participants
        $participantUsernames = $this->request->getParticipantUsernames();

        // Get a list of all potential approvers
        $potentialApprovers = $this->request->getAllPotentialApprovers();

        // TODO Check permissions
        // Only admins, curr/future RDs, and participants can even see this
        if(!Current_User::allow('hms', 'admin_approve_room_change') &&
            !in_array(UserStatus::getUsername(), $participantUsernames) &&
            !in_array(UserStatus::getUsername(), $potentialApprovers))
        {
            throw new PermissionException('You do not have permissions to view that room change request.');
        }

        $tpl = array();

        $tpl['REQUEST_ID'] = $this->request->getId(); // Used in hidden values

        $tpl['REQUEST_STATUS'] = $this->request->getState()->getName();

        $requestState = $this->request->getState();

        if ($requestState instanceof RoomChangeStatePending) {
            // Room change is still pending approval, not all
            // participants may be ready

            // For current/future RDs, Show hold, cancel, and deny buttons
            if (in_array(Current_User::getUsername(), $potentialApprovers)) {
                $tpl['HOLD_BTN'] = '';
                $tpl['CANCEL_BTN'] = '';
                $tpl['DENY_BTN'] = '';
            }

            // For admins, always show hold, cancel, deny
            if (Current_User::allow('hms', 'admin_approve_room_change')) {
                $tpl['HOLD_BTN'] = '';
                $tpl['CANCEL_BTN'] = '';
                $tpl['DENY_BTN'] = '';
            }

            // For participants, show cancel button
            if(in_array(UserStatus::getUsername(), $participantUsernames)) {
                $tpl['CANCEL_BTN'] = '';
            }

            // If all participants are approved, and user has permission, show housing approve button
            if($this->allParticipantsApproved() && Current_User::allow('hms', 'admin_approve_room_change')){
                $tpl['APPROVE_BTN'] = '';
            }

        } else if($requestState instanceof RoomChangeStateApproved) {
            // Request has been approved by Housing Assignments, only Housing Assignments can still cancel

            // Show Cancel button
            if (Current_User::allow('hms', 'admin_approve_room_change')) {
                $tpl['CANCEL_BTN'] = '';
            }

        } else if($requestState instanceof RoomChangeStateComplete) {
            // Room change is complete, nothing to do here?

        } else if($requestState instanceof RoomChangeStateHold) {
            // Room Change is held

            // Show deny, cancel buttons for Current/future RDs
            if (in_array(Current_User::getUsername(), $this->request->getAllPotentialApprovers())) {
                $tpl['CANCEL_BTN'] = '';
                $tpl['DENY_BTN'] = '';
            }

            // For admins, always show, cancel, deny
            if (Current_User::allow('hms', 'admin_approve_room_change')) {
                $tpl['CANCEL_BTN'] = '';
                $tpl['DENY_BTN'] = '';
            }

            // For participants, show cancel button
            if(in_array(UserStatus::getUsername(), $this->request->getParticipantUsernames())) {
                $tpl['CANCEL_BTN'] = '';
            }

            // If all participants are approved, and user has permission, show housing approve button
            if($this->allParticipantsApproved() && Current_User::allow('hms', 'admin_approve_room_change')){
                $tpl['APPROVE_BTN'] = '';
            }

        } else if($requestState instanceof RoomChangeStateCancelled) {
            // Show cancellation/denial reason
            //TODO

        } else if($requestState instanceof RoomChangeStateDenied) {
            // Show cancellation/denial reason
            // TODO
        }

        // Make a ParticipantView for each participant and add it to the row repeat
        foreach ($this->participants as $participant) {
            $participantView = new RoomChangeParticipantView($participant, $this->request, $this->participants);
            $tpl['PARTICIPANT'][]['ROW'] = $participantView->show();
        }

        $tpl['REQUEST_REASON'] = $this->request->getReason();

        if ($this->request->isDenied()) {
            $tpl['DENIED_REASON_PUBLIC'] = $this->request->getDeniedReasonPublic();
            $tpl['DENIED_REASON_PRIVATE'] = $this->request->getDeniedReasonPrivate();
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/roomChangeManageView.tpl');
    }

    /**
     * Returns true if every participant in this request is in the ParticipantStateFutureRdApproved state.
     *
     * @see RoomChangeParticipantState
     * @return boolean
     */
    private function allParticipantsApproved()
    {
        $participants = $this->request->getParticipants();

        // Loop over each participant on this request
        // Immedietly return false if any participant is not in the FutureRdApproved state
        foreach ($participants as $p) {
            if(!($p->getState() instanceof ParticipantStateFutureRdApproved))
            {
                return false;
            }
        }

        // We got this far, so all participants must have been approved
        return true;
    }
}

?>