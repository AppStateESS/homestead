<?php

namespace Homestead\Command;

use Homestead\PdoFactory;
use Homestead\Term;

class DashboardHomeCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'DashboardHome');
    }

    public function execute(CommandContext $context){
        $tpl = array();

        $tpl['NUM_RESIDENTS'] = $this->getNumResidents();
        $tpl['NUM_BEDS_AVAIL'] = $this->getAvailableBedsCount();
        $tpl['OVERFLOW_ASSIGNMENTS'] = $this->getOverflowAssignmentsCount();

        $context->setContent(\PHPWS_Template::process($tpl, 'hms', 'admin/dashboardHome.tpl'));
    }

    private function getNumResidents(){
        $pdo = PdoFactory::getPdoInstance();
        $query = 'SELECT count(*) FROM hms_assignment WHERE term = :term';

        $stmt = $pdo->prepare($query);
        $stmt->execute(array('term'=>Term::getCurrentTerm()));

        $result = $stmt->fetch();

        return $result[0];
    }

    private function getAvailableBedsCount()
    {
        $pdo = PdoFactory::getPdoInstance();
        $query = 'SELECT count(*)
            FROM hms_bed
                JOIN hms_room ON hms_bed.room_id = hms_room.id
                JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                LEFT OUTER JOIN hms_assignment ON (hms_bed.id = hms_assignment.bed_id AND hms_bed.term = hms_assignment.term)
            WHERE
                hms_bed.term = :term AND
                hms_assignment.banner_id IS NULL AND
                hms_bed.ra_roommate = 0 AND
                hms_bed.room_change_reserved = 0 AND
                hms_bed.international_reserved = 0 AND
                hms_room.overflow = 0 AND
                hms_room.offline = 0 AND
                hms_room.parlor = 0 AND
                hms_floor.is_online = 1 AND
                hms_residence_hall.is_online = 1;';

        $stmt = $pdo->prepare($query);
        $stmt->execute(array('term'=>Term::getCurrentTerm()));

        $result = $stmt->fetch();

        return $result[0];
    }

    public function getOverflowAssignmentsCount()
    {
        $pdo = PdoFactory::getPdoInstance();
        $query = 'SELECT count(*)
                FROM hms_bed
                    JOIN hms_room ON hms_bed.room_id = hms_room.id
                    JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id
                    JOIN hms_assignment ON (hms_bed.id = hms_assignment.bed_id AND hms_bed.term = hms_assignment.term)
                WHERE
                    hms_bed.term = :term AND
                    (
                        hms_bed.ra_roommate = 1 OR
                        hms_room.overflow = 1 OR
                        hms_room.parlor = 1
                    )';

        $stmt = $pdo->prepare($query);
        $stmt->execute(array('term'=>Term::getCurrentTerm()));

        $result = $stmt->fetch();

        return $result[0];
    }
}
