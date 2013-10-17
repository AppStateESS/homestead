<?php
PHPWS_Core::initCoreClass('PdoFactory.php');
PHPWS_Core::initModClass('hms', 'RoomChangeRequest.php');


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
            throw new InvalidArgumentException('Missing request id.');
        }

        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_room_change_curr_request where id = :requestId";

        $stmt = $db->prepare($query);
        $stmt->execute(array('requestId' => $id));
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'RoomChangeRequestRestored');

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
                        hms_room_change_curr_request.state_name NOT IN ('Cancelled', 'Denied', 'Complete', 'Approved') AND
                        hms_room_change_curr_participant.state_name NOT IN ('Cancelled', 'Checkedout', 'Declined', 'Denied')";

        $stmt = $db->prepare($query);
        $stmt->execute(array(
                'term'      => $term,
                'bannerId'  => $student->getBannerId()
        ));

        $results = $stmt->fetchAll(PDO::FETCH_CLASS, 'RoomChangeRequestRestored');

        // If more than one pending request is found, throw an exception
        if (sizeof($results) > 1) {
            throw new InvalidArgumentExcpetion('More than one pending room change detected.');
        } else if (sizeof($results) == 0) {
            return null;
        } else {
            return $results[0];
        }
    }

    /**
     * Returns a set of RoomChangeRequest objects which are in the given state
     * for a given array of HMS_Floor objects. Useful for showing RDs / Coordinators their pending requests.
     *
     * @param integer $term
     * @param array<HMS_Floor> $floorList
     * @param arary<string> $stateList
     */
    public static function getRoomChangesByFloorList($term, Array $floorList, Array $stateList)
    {
        $db = PdoFactory::getPdoInstance();

        $floorPlaceholders = array();
        $floorParams = array();
        foreach($floorList as $floor){
            $placeholder = "floor_id_" . $floor->getId(); // piece together a placeholder name

            $floorPlaceholders[] = ':' . $placeholder; // Add it to the list of placeholders for PDO
            $floorParams[$placeholder] = $floor->getId(); // Add the value for this placeholder, to be passed to execute()
        }

        $floorQuery = implode(',', $floorPlaceholders); // Collapse the array of placeholders into a comma separated list

        $statePlaceholders = array();
        $stateParams = array();
        foreach($stateList as $state){
            $placeholder = "state_name_$state";

            $statePlaceholders[] = ':' . $placeholder;
            $stateParams[$placeholder] = $state;
        }

        $stateQuery = implode(',', $statePlaceholders);

        $query = "SELECT hms_room_change_curr_request.* FROM hms_room_change_curr_request
                    JOIN hms_room_change_curr_participant ON hms_room_change_curr_request.id = hms_room_change_curr_participant.request_id
                    JOIN hms_hall_structure ON from_bed = hms_hall_structure.bedid
                    WHERE
                    term = :term AND
                    hms_room_change_curr_request.state_name IN ($stateQuery) and
                    hms_hall_structure.floorid IN ($floorQuery)";

        $stmt = $db->prepare($query);

        $params = array(
                'term'      => $term,
        );

        $params = array_merge($params, $floorParams, $stateParams);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_CLASS, 'RoomChangeRequestRestored');
    }
}

?>