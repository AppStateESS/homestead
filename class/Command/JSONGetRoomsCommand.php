<?php

namespace Homestead\Command;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class JSONGetRoomsCommand
{

    public function getRequestVars()
    {
        return array('action' => 'JSONGetRooms');
    }

    public function execute(CommandContext $context)
    {
        $newrows = array();
        $pdo = PdoFactory::getPdoInstance();
        $floor_id = (int) $context->get('floorId');
        $query = "SELECT
                    room.id as room_id,
                    room.room_number,
                    room.gender_type,
                    bed.id as bed_id,
                    bed.bedroom_label,
                    bed.bed_letter,
                    assign.banner_id,
                    assign.asu_username
                FROM
                    hms_room as room
                FULL JOIN
                    hms_bed as bed on room.id=bed.room_id
                FULL JOIN
                    hms_assignment as assign on bed.id=assign.bed_id
                WHERE
                    room.floor_id = :floor_id
                ORDER BY room_number asc, bedroom_label, bed_letter";

        $prep = $pdo->prepare($query);
        $prep->execute(array(':floor_id' => $floor_id));
        $rows = $prep->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($rows)) {
            $context->setContent(json_encode(array()));
            return;
        }
        $count = -1;
        $room_number_track = 0;
        foreach ($rows as $k => $v) {
            $gender = HMS_Util::formatGender($v['gender_type']);
            if ($v['banner_id']) {
                $student = StudentFactory::getStudentByBannerID($v['banner_id'], Term::getSelectedTerm());
                if ($student) {
                    $v['student'] = $student->first_name . ' ' . $student->last_name;
                } else {
                    $v['student'] = null;
                }
            } else {
                $v['student'] = null;
            }
            if ($v['room_number'] != $room_number_track) {
                $count++;
                $newrows[$count]['room_number'] = $v['room_number'];
                $newrows[$count]['gender'] = $gender;
                $newrows[$count]['beds'][] = $v;
                $room_number_track = $v['room_number'];
            } else {
                $newrows[$count]['beds'][] = $v;
            }
        }

        $context->setContent(json_encode($newrows));
    }

}
