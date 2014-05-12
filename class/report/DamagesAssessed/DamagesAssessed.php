<?php

/**
 *
 * @author Jeremy Booker
 */

class DamagesAssessed extends Report implements iCsvReport {
    const friendlyName = 'Assessed Damages Export';
    const shortName = 'AssessedDamages';

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

        $query = "select hall_name, room_number, hms_room_damage_responsibility.banner_id, amount, hms_damage_type.description, to_timestamp(CAST(assessed_on as integer)), assessed_by from hms_room_damage_responsibility JOIN hms_room_damage ON hms_room_damage_responsibility.damage_id = hms_room_damage.id JOIN hms_room ON (hms_room.persistent_id = hms_room_damage.room_persistent_id AND hms_room_damage.term = hms_room.term) JOIN hms_floor ON hms_room.floor_id = hms_floor.id JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id JOIN hms_damage_type ON hms_room_damage.damage_type = hms_damage_type.id WHERE hms_room_damage.term = :term and state = 'assessed' and amount != 0 ORDER BY assessed_on, banner_id";

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
        return array('Hall Name', 'Room Number', 'Banner Id', 'Amount', 'Damage Type', 'Date Assessed', 'Assessed By', 'First Name', 'Last Name');
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
