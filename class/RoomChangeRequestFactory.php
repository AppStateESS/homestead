<?php

namespace Homestead;

/**
 * Factory class for loading RoomChangeRequest objects from
 * the database.
 *
 * @author jbooker
 * @package hms
 */
class RoomChangeRequestFactory {

    public static function getRequestById($id)
    {
        if (!isset($id) || is_null($id)) {
            throw new \InvalidArgumentException('Missing request id.');
        }

        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_request where id = :requestId";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'requestId' => $id
        ));
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\Homestead\RoomChangeRequestRestored');

        return $stmt->fetch();
    }

    /**
     * Returns the latest room change request for the given bed.
     * NB: This doesn't check the request status, so you're always going to
     * get the last room change request for the given bed
     * @param HMS_Bed $bed The HMS_Bed object that you want the room change request for
     * @return RoomChangeRequestRestored Room change request object that corresponds to this bed
     * @throws \InvalidArgumentException
     */
    public static function getCurrentRequestByBed(HMS_Bed $bed)
    {
        if (!isset($bed) || is_null($bed)) {
            throw new \InvalidArgumentException('Missing bed.');
        }

        $db = PdoFactory::getPdoInstance();

        $query = "SELECT *
                  FROM hms_room_change_curr_request
                  WHERE id
                  IN (SELECT request_id FROM hms_room_change_participant WHERE to_bed = :bedId)
                  ORDER BY effective_date desc limit 1";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'bedId' => $bed->getId()
        ));
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\Homestead\RoomChangeRequestRestored');

        return $stmt->fetch();
    }

    /**
     * Returns a RoomChangeReuqest object corresponding to any
     * pending requests a student might have open, or null otherwise.
     *
     * A student should only have one request pending at any time, so
     * an exception is thrown if more than one request is found to be
     * pending.
     *
     * @param Student $student
     * @param string $term
     */
    public static function getPendingByStudent(Student $student, $term)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                    JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id
                    WHERE
                        term = :term AND
                        banner_id = :bannerId AND
                        hms_room_change_curr_request.state_name NOT IN ('Cancelled', 'Denied', 'Complete') AND
                        hms_room_change_curr_participant.state_name NOT IN ('Cancelled', 'Checkedout', 'Declined', 'Denied')";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'term' => $term,
                'bannerId' => $student->getBannerId()
        ));

        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, '\Homestead\RoomChangeRequestRestored');

        // If more than one pending request is found, throw an exception
        if (sizeof($results) > 1) {
            throw new \InvalidArgumentException('More than one pending room change detected.');
        } else if (sizeof($results) == 0) {
            return null;
        } else {
            return $results[0];
        }
    }


    /**
     * Returns a set of RoomChangeRequest objects which are in the given state
     * for a given array of HMS_Floor objects.
     * Useful for showing RDs / Coordinators their pending requests.
     *
     * @param integer $term
     * @param array<HMS_Floor> $floorList
     * @param arary<string> $stateList
     */
    public static function getRoomChangesByFloor($term, Array $floorList, Array $stateList)
    {
        $db = PdoFactory::getPdoInstance();

        $floorPlaceholders = array();
        $floorParams = array();
        foreach ($floorList as $floor) {
            $placeholder = "floor_id_" . $floor->getId(); // piece together a placeholder name

            $floorPlaceholders[] = ':' . $placeholder; // Add it to the list of placeholders for \PDO
            $floorParams[$placeholder] = $floor->getId(); // Add the value for this placeholder, to be passed to execute()
        }

        $floorQuery = implode(',', $floorPlaceholders); // Collapse the array of placeholders into a comma separated list

        $statePlaceholders = array();
        $stateParams = array();
        foreach ($stateList as $state) {
            $placeholder = "state_name_$state";

            $statePlaceholders[] = ':' . $placeholder;
            $stateParams[$placeholder] = $state;
        }

        $stateQuery = implode(',', $statePlaceholders);

        /*
         * Get any requests in the given states where the request is
         * coming from a Participant currently living on one of the listed floor
         * (from_bed is on a floor in list)
         *
         * Union that with any requests in the given states, where the request
         * has a to_bed set and that bed is on one of the floors in the list
         *
         * The union is important because the 'to_bed' field does not always have to be
         * set. A combined JOIN (as opposed to UNION) would not include results where
         * to_bed field is empty.
         */
        $query = "SELECT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                    JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id
                    JOIN hms_hall_structure ON from_bed = hms_hall_structure.bedid
                  WHERE
                      term = :term AND
                      hms_room_change_curr_request.state_name IN ($stateQuery) AND
                      hms_hall_structure.floorid IN ($floorQuery)
                  UNION

                  SELECT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                      JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id
                      JOIN hms_hall_structure ON to_bed = hms_hall_structure.bedid
                  WHERE
                      term = :term AND
                      hms_room_change_curr_request.state_name IN ($stateQuery) AND
                      hms_hall_structure.floorid IN ($floorQuery)";

        $stmt = $db->prepare($query);

        $params = array_merge(array(
                'term' => $term
        ), $floorParams, $stateParams);

        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_CLASS, '\Homestead\RoomChangeRequestRestored');
    }

    /**
     * Returns a set of RoomChangeRequest objects which are in the given state
     * for a given array of HMS_Floor objects.
     * Useful for showing RDs / Coordinators their pending requests.
     *
     * @param integer $term
     * @param array<HMS_Floor> $floorList
     */
    public static function getRoomChangesNeedsApproval($term, Array $floorList)
    {
        $db = PdoFactory::getPdoInstance();

        $floorPlaceholders = array();
        $floorParams = array();
        foreach ($floorList as $floor) {
            $placeholder = "floor_id_" . $floor->getId(); // piece together a placeholder name

            $floorPlaceholders[] = ':' . $placeholder; // Add it to the list of placeholders for \PDO
            $floorParams[$placeholder] = $floor->getId(); // Add the value for this placeholder, to be passed to execute()
        }

        $floorQuery = implode(',', $floorPlaceholders); // Collapse the array of placeholders into a comma separated list

        /*
         * Get any requests in the 'Pending' or 'Hold' states where the request is
         * coming from a Participant currently living on one of the listed floor
         * (from_bed is on a floor in list) and the participant status is
         * 'StudentAproved' (i.e. this request is waiting on the currnt RDs approval)
         *
         * Union that with any Pending/Held request that has a to_bed set and that bed
         * is on one of the floors in the list, and the participant's status is
         * 'CurrRdApproved' (i.e. the request is waiting on the future RD's approval).
         *
         * The union is important because the 'to_bed' field does not always have to be
         * set. A combined JOIN (as opposed to UNION) would not include results where
         * to_bed field is empty.
         */
        $query = "SELECT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                    JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id
                    JOIN hms_hall_structure ON from_bed = hms_hall_structure.bedid
                  WHERE
                      term = :term AND
                      hms_room_change_curr_request.state_name IN ('Pending', 'Hold') AND
                      hms_hall_structure.floorid IN ($floorQuery) AND hms_room_change_curr_participant.state_name IN ('StudentApproved')
                  UNION

                  SELECT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                      JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id
                      JOIN hms_hall_structure ON to_bed = hms_hall_structure.bedid
                  WHERE
                      term = :term AND
                      hms_room_change_curr_request.state_name IN ('Pending', 'Hold') AND
                      hms_hall_structure.floorid IN ($floorQuery) AND hms_room_change_curr_participant.state_name IN ('CurrRdApproved')";

        $stmt = $db->prepare($query);

        $params = array_merge(array(
                'term' => $term
        ), $floorParams);

        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_CLASS, '\Homestead\RoomChangeRequestRestored');
    }

    /**
     * Returns a set of RoomChangeRequest objects which are ready for Housing Assignments approval.
     * Useful for showing Assignments Office their pending requests.
     *
     * @param integer $term
     */
    public static function getAllRoomChangesNeedsAdminApproval($term)
    {
        $db = PdoFactory::getPdoInstance();

        /*
         * Get any requests in the 'Pending' or 'Hold' states and the participant status is
         * 'FutureRdAproved' (i.e. this request is waiting on assignment office approval)
         */
        $query = "SELECT DISTINCT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                    JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id
                  WHERE
                      term = :term AND
                      hms_room_change_curr_request.state_name IN ('Pending', 'Hold') AND
                      hms_room_change_curr_request.id NOT IN
                        (select request_id from hms_room_change_curr_participant where hms_room_change_curr_participant.state_name NOT IN ('FutureRdApproved'))";

        $stmt = $db->prepare($query);
        $params = array('term' => $term);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_CLASS, '\Homestead\RoomChangeRequestRestored');
    }

    /**
     * Returns a set of RoomChangeRequest objects which are in the given state
     * Useful for showing assignment office their pending requests.
     *
     * @param integer $term
     * @param arary<string> $stateList
     */
    public static function getAllRoomChangesByState($term, Array $stateList)
    {
        $db = PdoFactory::getPdoInstance();

        $statePlaceholders = array();
        $stateParams = array();
        foreach ($stateList as $state) {
            $placeholder = "state_name_$state";

            $statePlaceholders[] = ':' . $placeholder;
            $stateParams[$placeholder] = $state;
        }

        $stateQuery = implode(',', $statePlaceholders);

        /*
         * Get any requests in the given states
         */
        $query = "SELECT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                  WHERE
                      term = :term AND
                      hms_room_change_curr_request.state_name IN ($stateQuery)";
        $stmt = $db->prepare($query);

        $params = array_merge(array('term' => $term), $stateParams);

        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_CLASS, '\Homestead\RoomChangeRequestRestored');
    }

    public static function getRequestPendingCheckout(Student $student, $term)
    {
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                    JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id
                    WHERE
                        term = :term AND
                        banner_id = :bannerId AND
                        hms_room_change_curr_request.state_name = 'Approved' AND
                        hms_room_change_curr_participant.state_name = 'InProcess'";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'term' => $term,
                'bannerId' => $student->getBannerId()
        ));

        $results = $stmt->fetchAll(\PDO::FETCH_CLASS, '\Homestead\RoomChangeRequestRestored');

        // If more than one pending request is found, throw an exception
        if (sizeof($results) > 1) {
            throw new \InvalidArgumentException('More than one pending room change detected.');
        } else if (sizeof($results) == 0) {
            return null;
        } else {
            return $results[0];
        }

    }
}
