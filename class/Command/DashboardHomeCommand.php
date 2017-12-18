<?php

namespace Homestead\Command;

use Homestead\PdoFactory;
use Homestead\Term;
use \PDO;

class DashboardHomeCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'DashboardHome');
    }

    public function execute(CommandContext $context){
        $tpl = array();

        $tpl['NUM_RESIDENTS'] = self::getNumResidents();
        $tpl['NUM_BEDS_AVAIL'] = self::getAvailableBedsCount();
        $tpl['OVERFLOW_ASSIGNMENTS'] = self::getOverflowAssignmentsCount();

        $classBreakdown = self::getClassBreakdown();
        $tpl['CLASS_BREAK_FR'] = $classBreakdown['FR'];
        $tpl['CLASS_BREAK_SO'] = $classBreakdown['SO'];
        $tpl['CLASS_BREAK_JR'] = $classBreakdown['JR'];
        $tpl['CLASS_BREAK_SR'] = $classBreakdown['SR'];

        $context->setContent(\PHPWS_Template::process($tpl, 'hms', 'admin/dashboardHome.tpl'));
    }

    // TODO: Query for building the assignments over time graph:
    /*
    SELECT extract(epoch from foo.day) * 1000, count(*) FROM (SELECT day::timestamp FROM generate_series(timestamp '2016-11-21', timestamp '2017-11-21', '1 day') as day) as foo JOIN hms_assignment_history ON (to_timestamp(assigned_on) < foo.day AND (to_timestamp(removed_on) > foo.day OR removed_on IS NULL)) WHERE term = 201810 GROUP BY foo.day order by day asc;
    */

    private static function getNumResidents(){
        $pdo = PdoFactory::getPdoInstance();
        $query = 'SELECT count(*) FROM hms_assignment WHERE term = :term';

        $stmt = $pdo->prepare($query);
        $stmt->execute(array('term'=>Term::getCurrentTerm()));

        $result = $stmt->fetch();

        return $result[0];
    }

    private static function getAvailableBedsCount()
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

    public static function getOverflowAssignmentsCount()
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

    public static function getClassBreakdown()
    {
        $pdo = PdoFactory::getPdoInstance();
        $query = 'SELECT class, round((bar.c * 100)::numeric/bar.total, 0) as perc
                    FROM (select class, count(class) as c, foo.total
                            FROM hms_assignment full
                                JOIN (select count(*) as total from hms_assignment where term = :term) as foo ON 1 = 1
                            WHERE term = :term group by class, foo.total) as bar order by perc DESC';

        $stmt = $pdo->prepare($query);
        $stmt->execute(array('term'=>Term::getCurrentTerm()));
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $result = $stmt->fetchAll();

        $breakdown = array();
        foreach($result as $row){
            $breakdown[$row['class']] = $row['perc'];
        }

        foreach(array('FR', 'SO', 'JR', 'SR') as $class){
            if(!isset($breakdown[$class])){
                $breakdown[$class] = 0;
            }
        }

        return $breakdown;
    }
}
