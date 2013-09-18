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
                        hms_room_change_curr_request.state NOT IN ('Cancelled', 'Denied', 'Complete', 'Approved') AND
                        hms_room_change_curr_participant.state NOT IN ('Cancelled', 'Checkedout', 'Declined', 'Denied')";

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
}

?>