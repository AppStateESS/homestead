<?php

PHPWS_Core::initModClass('hms', 'RoomChangeParticipantView.php');

class RoomChangeManageView extends homestead\View {

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
        // Get a username list of all participants
        $participantUsernames = $this->request->getParticipantUsernames();

        // Get a list of all potential approvers
        $potentialApprovers = $this->request->getAllPotentialApprovers();

        // Check permissions
        // Only admins, curr/future RDs, and participants can even see this
        if(!Current_User::allow('hms', 'admin_approve_room_change') &&
            !in_array(UserStatus::getUsername(), $participantUsernames) &&
            !in_array(UserStatus::getUsername(), $potentialApprovers))
        {
            throw new PermissionException('You do not have permissions to view that room change request.');
        }

        $tpl = array();

        $tpl['REQUEST_ID'] = $this->request->getId(); // Used in hidden values
        $requestId = $this->request->getId();

        $tpl['REQUEST_STATUS'] = $this->request->getState()->getName();

        $tpl['last_updated_timestamp'] = $this->request->getLastUpdatedTimestamp();
        $tpl['last_updated_date'] = date("M j @ g:ia", $this->request->getLastUpdatedTimestamp());

        javascriptMod('hms', 'livestamp');

        $requestState = $this->request->getState();

        if ($requestState instanceof RoomChangeStatePending) {
            // Room change is still pending approval, not all
            // participants may be ready

            // For current/future RDs, Show hold, cancel, and deny buttons
            if (in_array(Current_User::getUsername(), $potentialApprovers)) {
                $tpl['REQUEST_ID_HOLD'] = $requestId;
                $tpl['REQUEST_ID_CANCEL'] = $requestId;
                $tpl['REQUEST_ID_CANCEL_BTN'] = $requestId;
                $tpl['REQUEST_ID_DENY'] = $requestId;
                $tpl['REQUEST_ID_DENY_BTN'] = $requestId;
            }

            // For admins, always show hold, cancel, deny
            if (Current_User::allow('hms', 'admin_approve_room_change')) {
                $tpl['REQUEST_ID_HOLD'] = $requestId;
                $tpl['REQUEST_ID_CANCEL'] = $requestId;
                $tpl['REQUEST_ID_CANCEL_BTN'] = $requestId;
                $tpl['REQUEST_ID_DENY'] = $requestId;
                $tpl['REQUEST_ID_DENY_BTN'] = $requestId;
            }

            // For participants, show cancel button
            if(in_array(UserStatus::getUsername(), $participantUsernames)) {
                $tpl['REQUEST_ID_CANCEL'] = $requestId;
                $tpl['REQUEST_ID_CANCEL_BTN'] = $requestId;
            }

            // If all participants are approved and checked into their current assignments, and user has permission, then show housing approve button
            if($this->allParticipantsApproved() && Current_User::allow('hms', 'admin_approve_room_change') && $this->request->allParticipantsCheckedIn()){
                $tpl['REQUEST_ID_APPROVE'] = $requestId;
            }

        } else if($requestState instanceof RoomChangeStateApproved) {
            // Request has been approved by Housing Assignments, only Housing Assignments can still cancel

            // Show Cancel button
            if (Current_User::allow('hms', 'admin_approve_room_change')) {
                $tpl['REQUEST_ID_CANCEL'] = $requestId;
                $tpl['REQUEST_ID_CANCEL_BTN'] = $requestId;
            }

        } else if($requestState instanceof RoomChangeStateComplete) {
            // Room change is complete, nothing to do here?

        } else if($requestState instanceof RoomChangeStateHold) {
            // Room Change is held

            // Show deny, cancel buttons for Current/future RDs
            if (in_array(Current_User::getUsername(), $this->request->getAllPotentialApprovers())) {
                $tpl['REQUEST_ID_CANCEL'] = $requestId;
                $tpl['REQUEST_ID_CANCEL_BTN'] = $requestId;
                $tpl['REQUEST_ID_DENY'] = $requestId;
                $tpl['REQUEST_ID_DENY_BTN'] = $requestId;
            }

            // For admins, always show, cancel, deny
            if (Current_User::allow('hms', 'admin_approve_room_change')) {
                $tpl['REQUEST_ID_CANCEL'] = $requestId;
                $tpl['REQUEST_ID_CANCEL_BTN'] = $requestId;
                $tpl['REQUEST_ID_DENY'] = $requestId;
                $tpl['REQUEST_ID_DENY_BTN'] = $requestId;
            }

            // For participants, show cancel button
            if(in_array(UserStatus::getUsername(), $this->request->getParticipantUsernames())) {
                $tpl['REQUEST_ID_CANCEL'] = $requestId;
                $tpl['REQUEST_ID_CANCEL_BTN'] = $requestId;
            }

            // If all participants are approved, and user has permission, show housing approve button
            if($this->allParticipantsApproved() && Current_User::allow('hms', 'admin_approve_room_change')){
                $tpl['REQUEST_ID_APPROVE'] = $requestId;
            }

        } else if($requestState instanceof RoomChangeStateCancelled) {
            // Show cancellation/denial reason
            $tpl['CANCELLED_REASON_PUBLIC'] = $this->request->getDeniedReasonPublic();

            // Show the private reason for admins / RDs
            if (Current_User::allow('hms', 'admin_approve_room_change') || in_array(UserStatus::getUsername(), $potentialApprovers)) {
                $tpl['CANCELLED_REASON_PRIVATE'] = $this->request->getDeniedReasonPrivate();
            }

        } else if($requestState instanceof RoomChangeStateDenied) {
            // Show cancellation/denial reason
            $tpl['DENIED_REASON_PUBLIC']  = $this->request->getDeniedReasonPublic();

            // Show the private reason for admins / RDs
            if (Current_User::allow('hms', 'admin_approve_room_change') || in_array(UserStatus::getUsername(), $potentialApprovers)) {
                $tpl['DENIED_REASON_PRIVATE'] = $this->request->getDeniedReasonPrivate();
            }
        }

        // Make a ParticipantView for each participant and add it to the row repeat
        foreach ($this->participants as $participant) {
            $participantView = new RoomChangeParticipantView($participant, $this->request, $this->participants);
            $tpl['PARTICIPANT'][]['ROW'] = $participantView->show();
        }

        $tpl['REQUEST_REASON'] = $this->request->getReason();


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
