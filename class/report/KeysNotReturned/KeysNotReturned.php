<?php

/**
 *
 * @author Jeremy Booker
 */

class KeysNotReturned extends Report implements iCsvReport {
    const friendlyName = 'Keys Not Returned';
    const shortName = 'KeysNotReturned';

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
        PHPWS_Core::initCoreClass('PdoFactory.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $db = PdoFactory::getInstance()->getPdo();

        $query = "select hall_name, room_number, hms_checkin.banner_id, to_timestamp(checkout_date), checkout_by from hms_checkin JOIN hms_bed ON (hms_checkin.bed_persistent_id = hms_bed.persistent_id AND hms_checkin.term = hms_bed.term) JOIN hms_room ON hms_bed.room_id = hms_room.id JOIN hms_floor ON hms_room.floor_id = hms_floor.id JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id WHERE hms_checkin.term = :term and key_not_returned = 1 ORDER BY checkout_date";

        $stmt = $db->prepare($query);
        $stmt->execute(array('term'=>$this->term));
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($results as $row)
        {
            try {
                $student = StudentFactory::getStudentByBannerId($row['banner_id'], $this->term);

                $row['first_name'] = $student->getFirstName();
                $row['last_name']  = $student->getLastName();
            } catch (StudentNotFoundException $e) {
                $row['first_name'] = 'NOT FOUND';
                $row['last_name']  = 'NOT FOUND';
            }

            $this->rows[] = $row;
        }


    }

    public function getCsvColumnsArray()
    {
        return array('Hall Name', 'Room Number', 'Banner Id', 'Checkout Date', 'Checkout By', 'First Name', 'Last Name');
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
?>
