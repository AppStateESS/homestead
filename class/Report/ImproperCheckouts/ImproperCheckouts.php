<?php

namespace Homestead\Report\ImproperCheckouts;

/**
 *
 * @author Jeremy Booker
 */

class ImproperCheckouts extends Report implements iCsvReport {
    const friendlyName = 'Improper Checkouts';
    const shortName = 'ImproperCheckouts';

    private $term;
    private $rows;

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
        PHPWS_Core::initModClass('hms', 'PdoFactory.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $db = PdoFactory::getInstance()->getPdo();

        $query = "select hall_name, room_number, hms_checkin.banner_id, to_timestamp(checkout_date), checkout_by, improper_checkout_note from hms_checkin JOIN hms_bed ON (hms_checkin.bed_persistent_id = hms_bed.persistent_id AND hms_checkin.term = hms_bed.term) JOIN hms_room ON hms_bed.room_id = hms_room.id JOIN hms_floor ON hms_room.floor_id = hms_floor.id JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id WHERE hms_checkin.term = :term and improper_checkout = 1 ORDER BY checkout_date";

        $stmt = $db->prepare($query);
        $stmt->execute(array('term'=>$this->term));
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($results as $row)
        {
            try {
                $student = StudentFactory::getStudentByBannerId($row['banner_id'], $this->term);

                $row['first_name'] = $student->getFirstName();
                $row['last_name']  = $student->getLastName();
                $row['email']      = $student->getUsername();
            } catch (StudentNotFoundException $e) {
                $row['first_name'] = 'NOT FOUND';
                $row['last_name']  = 'NOT FOUND';
                $row['email']      = 'NOT FOUND';
            }

            $this->rows[] = $row;
        }


    }

    public function getCsvColumnsArray()
    {
        return array('Hall Name', 'Room Number', 'Banner Id', 'Checkout Date', 'Checkout By', 'Note', 'First Name', 'Last Name', 'Username');
    }

    public function getCsvRowsArray()
    {
        return $this->rows;
    }

    public function getDefaultOutputViewCmd()
    {
        $cmd = CommandFactory::getCommand('ShowReportCsv');
        $cmd->setReportId($this->id);

        return $cmd;
    }
}
