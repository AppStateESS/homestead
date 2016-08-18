<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

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
        $db = PdoFactory::getPdoInstance();

        $query = "SELECT hms_checkin.banner_id, checkout_date, checkout_by, hall_name, floor_number, room_number, bedroom_label, bed_letter FROM hms_checkin JOIN hms_hall_structure ON hms_checkin.bed_id = hms_hall_structure.bedid WHERE hms_checkin.term = :term AND hms_checkin.checkout_date IS NOT NULL ORDER BY hall_name ASC,room_number ASC";

        $stmt = $db->prepare($query);
        $stmt->execute(array('term' => $this->term));

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Post-processing, cleanup, making it pretty
        foreach($results as $row){
            // Updates counts
            $this->total++;

            $bannerId = $row['banner_id'];

            $student = StudentFactory::getStudentByBannerId($bannerId, $this->term);

            $row['bed'] = $row['bedroom_label'] . $row['bed_letter'];
            $row['checkout_date'] = HMS_Util::get_short_date_time($row['checkout_date']);
            $row['username'] = $student->getUsername();
            $row['first_name'] = $student->getFirstName();
            $row['last_name'] = $student->getLastName();

            unset($row['bed_letter']);
            unset($row['bedroom_label']);

            // Copy the cleaned up row to the member var for data
            $this->data[] = $row;
        }
    }

    public function getCsvColumnsArray()
    {
        return array('Banner ID', 'Checkout Date', 'checked out by', 'Hall', 'Floor', 'Room', 'Bed', 'username', 'First Name', 'Last Name');
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
