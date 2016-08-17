<?php

/**
 * Checkout List Report
 * Lists all of the currently checked-out students, ordered by hall and room number
 *
 * @author Chris Detsch
 * @package HMS
*/

class CheckoutList extends Report implements iCsvReport {

    const friendlyName = 'Check-out - List of Check-outs';
    const shortName = 'CheckoutList';

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
        $db->addColumn('hms_checkin.checkout_date');
        $db->addColumn('hms_checkin.checkout_by');
        $db->addColumn('hms_hall_structure.hall_name');
        $db->addColumn('hms_hall_structure.floor_number');
        $db->addColumn('hms_hall_structure.room_number');
        $db->addColumn('hms_hall_structure.bedroom_label');
        $db->addColumn('hms_hall_structure.bed_letter');

        $db->addWhere('hms_checkin.term', $term);
        $db->addWhere('hms_checkin.checkout_date', null, 'IS NOT');

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

            $bannerId = $row['banner_id'];

            $student = StudentFactory::getStudentByBannerId($bannerId, $this->term);

            $row['checkout_date'] = HMS_Util::get_short_date_time($row['checkout_date']);
            $row['username'] = $student->getUsername();
            $row['first_name'] = $student->getFirstName();
            $row['last_name'] = $student->getLastName();
            $row['bed'] = $row['bedroom_label'] . $row['bed_letter'];

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
