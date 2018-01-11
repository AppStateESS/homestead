<?php

namespace Homestead\Report\CoedRooms;

use \Homestead\Report;
use \Homestead\iCsvReport;

/**
 * Coed Room Report.
 *
 * @author John Felipe
 * @package HMS
 */

class CoedRooms extends Report implements iCsvReport
{
	const friendlyName = 'Coed Rooms';
    const shortName = 'CoedRooms';

    private $totalCoed;

	private $term;
	private $rows;

	public function __construct($id = 0)
    {
        parent::__construct($id);

        $this->rows = array();
    }


    public function execute()
    {
        $db = new \PHPWS_DB('hms_room');

        $db->addColumn('hms_residence_hall.hall_name');
        $db->addColumn('hms_floor.floor_number');
        $db->addColumn('hms_room.room_number');

        $db->addJoin('LEFT', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addWhere('hms_room.term', $this->term);
        $db->addWhere('hms_room.gender_type', COED);
        $results = $db->select();



        $this->totalCoed = sizeof($results);

        $this->rows = $results;


    }

     public function getCsvColumnsArray(){
        return array('Hall Name', 'Floor', 'Room Number');
    }

    public function getCsvRowsArray()
    {
        return $this->rows;
    }

	public function getTotalCoed()
    {
        return $this->totalCoed;
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
