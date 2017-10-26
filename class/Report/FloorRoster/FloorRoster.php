<?php

namespace Homestead\Report\FloorRoster;

use \Homestead\Report;
use \Homestead\StudentFactory;
use \Homestead\CommandFactory;
use \Homestead\HMS_Util;
use \Homestead\Exception\DatabaseException;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class FloorRoster extends Report {
    const friendlyName = 'Floor Roster';
    const shortName = 'FloorRoster';

    public $rows;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function execute()
    {
        $db = new \PHPWS_DB;

        $query = <<<EOF
SELECT
        assign.banner_id,
        assign.asu_username,
        bed.bed_letter,
        bed.bedroom_label,
        room.room_number,
        floor.floor_number,
        hall.hall_name

FROM
        hms_assignment as assign
        LEFT JOIN hms_bed as bed on assign.bed_id=bed.id
        LEFT JOIN hms_room as room on bed.room_id=room.id
        LEFT JOIN hms_floor as floor on room.floor_id=floor.id
        LEFT JOIN hms_residence_hall as hall on floor.residence_hall_id=hall.id
WHERE
        assign.term = '{$this->term}'
ORDER BY
        hall.hall_name,
        floor.floor_number,
        room.room_number,
        bed.bedroom_label,
        bed.bed_letter
EOF;

        $result = $db->select(null, $query);
        if (\PEAR::isError($result)) {
            throw new DatabaseException($result->toString());
        }

        $final_rows = array();

        foreach ($result as $row) {
            $hall_name = $row['hall_name'];

            $student = StudentFactory::getStudentByBannerId($row['banner_id'], $this->term);
            $row['name'] = $student->getFullName();
            $row['dob'] = $student->getDOB();
            $row['year'] = $student->getClass();
            $row['gender'] = HMS_Util::formatGender($student->getGender());

            $final_rows[$hall_name][] = $row;
        }
        $this->rows = & $final_rows;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getDefaultOutputViewCmd()
    {
        $cmd = CommandFactory::getCommand('ShowReportPdf');
        $cmd->setReportId($this->id);

        return $cmd;
    }

}
