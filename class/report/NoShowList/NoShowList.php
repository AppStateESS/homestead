<?php

/**
 * No-show List report
 * Lists all of the students who are currently assigned, but have never checked in
 *
 * @author jbooker
 * @package HMS
 */

class NoShowList extends Report implements iCsvReport {

    const friendlyName = 'Check-in - List of No-shows';
    const shortName = 'NoShowList';

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


        $results = PHPWS_DB::getAll("select hms_assignment.banner_id, asu_username, reason, class, hall_name, room_number from hms_assignment JOIN hms_hall_structure ON hms_assignment.bed_id = hms_hall_structure.bedid where term = 201340 and hms_assignment.banner_id NOT IN (select banner_id from hms_checkin WHERE term = $term and checkout_date IS NULL) order by hall_name, room_number;");

        if(PHPWS_Error::isError($results)){
            throw new DatabaseException($results->toString());
        }

        // Post-processing, cleanup, making it pretty
        foreach($results as $row){

            // Updates counts
            $this->total++;

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

?>
