<?php

namespace Homestead;

class AssignmentFactory {

    public static function getAssignmentsForTerm(string $term){

        $db = PdoFactory::getPdoInstance();

        $query = 'SELECT
                        hms_assignment.banner_id,
                        hms_assignment.asu_username,
                        hms_residence_hall.hall_name,
                        hms_room.room_number,
                        hms_new_application.cell_phone
                    FROM hms_assignment
                    LEFT OUTER JOIN hms_new_application
                        ON (hms_assignment.banner_id = hms_new_application.banner_id AND hms_assignment.term = hms_new_application.term)
                    JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id
                    JOIN hms_room ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id

                    WHERE hms_assignment.term = :term';

        $stmt = $db->prepare($query);
        $stmt->execute(array('term'=>$term));

        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        return $stmt->fetchAll();
    }
}
