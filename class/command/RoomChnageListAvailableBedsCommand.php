<?php

/**
 * Returns a JSON-encoded list of empty beds.
 *
 * @author jbooker
 * @package hms
 */

class RoomChnageListAvailableBedsCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RoomChnageListAvailableBeds');
    }

    public function execute(CommandContext $context)
    {
        $term = 201340;

        $db = PdoFactory::getPdoInstance();

        $query = "select hall_name, room_number
                FROM hms_hall_structure
                LEFT OUTER JOIN hms_assignment ON hms_assignment.bed_id = hms_hall_structure.bedid
                WHERE
                    bed_term = :term and
                    hms_assignment.bed_id IS NULL and
                    offline = 0 and
                    overflow = 0 and
                    parlor = 0 and
                    ra = 0 and
                    private = 0";

        $stmt = $db->prepare($query);

        $params = array(
            'term' => $term
        );

        $stmt->execute($params);

        echo json_encode($stmt->fetchAll());
        exit;
    }
}

?>