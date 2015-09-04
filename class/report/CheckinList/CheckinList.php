<?php

/**
 * Checkin List Report
 * Lists all of the currently checked-in students, orderd by hall and room number
 *
 * @author jbooker
 * @package HMS
 */

class CheckinList extends Report implements iCsvReport {

    const friendlyName = 'Check-in - List of Check-ins';
    const shortName    = 'CheckinList';
    const category     = 'Checkin';

    private $term;

    // Counts
    private $total;

    private $data;

    public function __construct($id = 0){
        parent::__construct($id);

        $this->total = 0;

        $this->data = array();
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $term = $this->term;


        $db = new PHPWS_DB('hms_checkin');

        // Join hall structure
        $db->addJoin('', 'hms_checkin', 'hms_hall_structure', 'bed_id', 'bedid');

        $db->addColumn('hms_checkin.banner_id');
        $db->addColumn('hms_checkin.checkin_date');

        $db->addColumn('hms_hall_structure.hall_name');
        $db->addColumn('hms_hall_structure.room_number');

        $db->addWhere('hms_checkin.term', $term);
        $db->addWhere('hms_checkin.checkout_date', null, 'IS NULL');

        // Sort by hall, then room number
        $db->addOrder(array('hms_hall_structure.hall_name ASC', 'hms_hall_structure.room_number ASC'));

        $results = $db->select();

        if(PHPWS_Error::isError($results)){
            throw new DatabaseException($results->toString());
        }

        // Post-processing, cleanup, making it pretty
        foreach($results as $row){

            // Updates counts
            $this->total++;

            $row['checkin_date'] = HMS_Util::get_short_date_time($row['checkin_date']);

            // Copy the cleaned up row to the member var for data
            $this->data[] = $row;
        }
    }

    public function getCsvColumnsArray()
    {
        return array_keys($this->data[0]);
    }

    public function getCsvRowsArray(){
        return $this->data;
    }

    public function getData(){
        return $this->data;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getTotal(){
        return $this->total;
    }
}
