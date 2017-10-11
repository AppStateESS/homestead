<?php
PHPWS_Core::initModClass('hms', 'RoomChangeParticipantState.php');


class RoomChangeParticipantStateFactory {

    public static function getCurrentStateForParticipant(RoomChangeParticipant $participant)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_participant WHERE participant_id = :participantId";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'participantId' => $participant->getId()
        ));

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $className = 'ParticipantState' . $result['state_name'];

        return new $className($participant, $result['effective_date'], $result['effective_until_date'], $result['committed_by']);
    }

    public static function getStateHistory(RoomChangeParticipant $participant)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_participant_state WHERE participant_id = :participantId ORDER BY effective_date ASC";
        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'participantId' => $participant->getId()
        ));

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If no results, just return here
        if (sizeof($results) <= 0) {
            return null;
        }

        // Create a ParticipantState object for each result
        $states = array();
        foreach ($results as $row) {
            $className = 'ParticipantState' . $row['state_name'];
            $states[] = new $className($participant, $row['effective_date'], $row['effective_until_date'], $row['committed_by']);
        }

        return $states;
    }

    public static function getRCPStateByCurrentState($stateName)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_participant_state
                    WHERE state_name = :stateName and effective_until_date IS NULL";

        $stmt = $db->prepare($query);

        $params = array("stateName" => $stateName);

        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If no results, just return here
        if (sizeof($results) <= 0) {
            return null;
        }

        // Create a ParticipantState object for each result
        $states = array();
        foreach ($results as $row) {
            $className = 'ParticipantState' . $row['state_name'];
            $participant = RoomChangeParticipantFactory::getParticipantById($row['participant_id']);
            $states[] = new $className($participant, $row['effective_date'], $row['effective_until_date'], $row['committed_by']);
        }

        return $states;
    }
}
