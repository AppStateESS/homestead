<?php

namespace Homestead\command;

use \Homestead\Command;

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

        $query = "select bedid, hall_name, room_number, bedroom_label, bed_letter
                FROM hms_hall_structure
                LEFT OUTER JOIN hms_assignment ON hms_assignment.bed_id = hms_hall_structure.bedid
                WHERE
                    bed_term = :term and
                    hms_assignment.bed_id IS NULL and
                    (room_gender = :gender OR room_gender = 2) and
                    offline = 0 and
                    overflow = 0 and
                    parlor = 0 and
                    ra_roommate = 0 and
                    private = 0 and
                    reserved = 0 and
                    room_change_reserved = 0
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
