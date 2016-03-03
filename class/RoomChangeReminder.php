<?php

/**
 * Sends reminder emails about room changes to the person ussually RA that the
 * roomchange is waiting on.
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Chris Detsch
 */

class RoomChangeReminder
{
    public static function execute()
    {
        PHPWS_Core::initModClass('hms', 'Term.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantStateFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeRequestFactory.php');
        PHPWS_Core::initModClass('hms', 'RoomChangeParticipantStateFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Email.php');
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'UserStatus.php');
        $term = Term::getSelectedTerm();

        $studentApproved = RoomChangeParticipantStateFactory::getRCPStateByCurrentState('StudentApproved');
        $currRdApproved = RoomChangeParticipantStateFactory::getRCPStateByCurrentState('CurrRdApproved');

        $toRDs = array();

        if($studentApproved)
        {

            foreach($studentApproved as $studentApprovedState)
            {
                $participant = RoomChangeParticipantFactory::getParticipantById($studentApprovedState->getParticipantId());
                $roomChangeRequest = RoomChangeRequestFactory::getRequestById($participant->getId());

                $currentRds = $participant->getCurrentRdList();

                foreach ($currentRds as $rd)
                {
                    $toRDs[$rd][] = $roomChangeRequest;
                }
            }
        }

        if($currRdApproved)
        {
            foreach ($currRdApproved as $currRdApprovedState)
            {
                $participant = RoomChangeParticipantFactory::getParticipantById($currRdApprovedState->getParticipantId());
                $roomChangeRequest = RoomChangeRequestFactory::getRequestById($participant->getId());

                $rds = $participant->getFutureRdList();

                foreach ($rds as $rd)
                {
                    $toRDs[$rd][] = $roomChangeRequest;
                }
            }
        }

        if($toRDs)
        {
            $rds = array_keys($toRDs);

            foreach ($rds as $rd)
            {
                $roomChanges = $toRDs[$rd];
                HMS_Email::sendRDRoomChangeReminders($rd, $roomChanges);
            }
        }

        $inProcess = RoomChangeParticipantStateFactory::getRCPStateByCurrentState('InProcess');

        if($inProcess)
        {
            foreach ($inProcess as $inProcessState)
            {
                $effectiveDate = $inProcessState->getEffectiveDate();
                $threeDaysLater = strtotime('+3 days', $effectiveDate);
                $now = time();
                if($now <= $effectiveDate)
                {
                    $participant = RoomChangeParticipantFactory::getParticipantById($inProcessState->getParticipantId());
                    $roomChangeRequest = RoomChangeRequestFactory::getRequestById($participant->getId());

                    HMS_Email::sendRoomChangeCheckOutReminders($roomChangeRequest);
                }
            }
        }

    }

}
