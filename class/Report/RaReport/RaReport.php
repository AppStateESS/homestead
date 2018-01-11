<?php

namespace Homestead\Report\RaReport;

use \Homestead\Report;
use \Homestead\iCsvReport;
use \Homestead\PdoFactory;
use \Homestead\StudentFactory;
use \Homestead\Exception\StudentNotFoundException;

/**
 * RaReport Report.
 *
 * @author John Felipe
 * @package HMS
 */

class RaReport extends Report implements iCsvReport
{
	const friendlyName = 'RA Report';
    const shortName = 'RaReport';

    private $rows;
    private $term;

    public function __construct($id = 0)
    {
    	parent::__construct($id);

    	$this->rows = array();
    }

    public function execute()
    {
        $db = PdoFactory::getInstance()->getPdo();

        $query = " select hms_assignment.banner_id, hall_name, floor_number, room_number from hms_assignment join hms_bed on hms_assignment.bed_id = hms_bed.id join hms_room on hms_bed.room_id = hms_room.id join hms_floor on hms_room.floor_id = hms_floor.id join hms_residence_hall on hms_floor.residence_hall_id = hms_residence_hall.id where hms_bed.ra = 1 and hms_assignment.term = :term";

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
        return array('Name', 'Banner ID', 'Username', 'Hall Name', 'Floor', 'Room Number');
    }

    public function getCsvRowsArray()
    {
        return $this->rows;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getRows()
    {
        return $this->rows;
    }
}
