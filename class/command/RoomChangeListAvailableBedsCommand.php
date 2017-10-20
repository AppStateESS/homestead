<?php

/**
 * Returns a JSON-encoded list of empty beds.
 *
 * @author jbooker
 * @package hms
 */

class RoomChangeListAvailableBedsCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RoomChangeListAvailableBeds');
    }

    public function execute(CommandContext $context)
    {
        $term = Term::getCurrentTerm();

        $gender = $context->get('gender');

        if(!isset($gender)){
            echo "Missing gender!";
        }

        $db = PdoFactory::getPdoInstance();

        $query = "select hms_bed.id, hall_name, room_number, bedroom_label, bed_letter
                FROM hms_bed
                    JOIN hms_room ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                    LEFT OUTER JOIN hms_assignment ON hms_assignment.bed_id = hms_bed.id
                WHERE
                    hms_bed.term = :term and
                    hms_assignment.bed_id IS NULL and
                    (hms_room.gender_type = :gender OR hms_room.gender_type = 3) and
                    offline = 0 and
                    overflow = 0 and
                    parlor = 0 and
                    ra_roommate = 0 and
                    private = 0 and
                    reserved = 0 and
                    room_change_reserved = 0 AND

                    hms_bed.persistent_id NOT IN
                    (SELECT hms_checkin.bed_persistent_id FROM hms_checkin WHERE
                        hms_bed.persistent_id = hms_checkin.bed_persistent_id AND
                        hms_checkin.checkout_date IS NULL)

                ORDER BY hall_name, room_number";

        $stmt = $db->prepare($query);

        $params = array(
            'term' => $term,
            'gender' => $gender
        );

        $stmt->execute($params);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
}
