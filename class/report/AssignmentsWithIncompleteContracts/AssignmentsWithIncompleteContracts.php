<?php

/**
 * Cancelled Housing Assignments List
 * Generates a list of all cancelled housing aplications
 * for a selected term.
 *
 * @author Jeremy Booker
 * @package HMS
 */
class AssignmentsWithIncompleteContracts extends Report implements iCsvReport{

    const friendlyName = 'Assignments with Incomplete Contracts';
    const shortName    = 'AssignmentsWithIncompleteContracts';

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
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $pdo = PdoFactory::getPdoInstance();

        $sql = "SELECT
                    hms_assignment.banner_id,
                    hms_assignment.asu_username,
                    hms_assignment.application_term,
                    hms_assignment.reason,
                    hms_residence_hall.hall_name,
                    hms_room.room_number,
                    hms_contract.envelope_id,
                    hms_contract.envelope_status
                FROM hms_assignment
                    LEFT OUTER JOIN hms_contract
                        ON (hms_assignment.banner_id = hms_contract.banner_id AND (hms_assignment.term = hms_contract.term OR hms_contract.term IS NULL))

                    LEFT OUTER JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id
                    LEFT OUTER JOIN hms_room ON hms_bed.room_id = hms_room.id
                    LEFT OUTER JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    LEFT OUTER JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id

                WHERE
                    hms_assignment.term = :term and
                    (hms_contract.envelope_status != 'completed' OR hms_contract.envelope_status IS NULL)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array('term'=>$this->term));


        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize storage for processed rows
        $this->rows = array();

        // Process and store each result
        foreach($results as $assignment){
            $row = array();
            $student = StudentFactory::getStudentByBannerId($assignment['banner_id'], $this->term);

            $row['bannerId']            = $assignment['banner_id'];
            $row['username']            = $assignment['asu_username'];
            $row['name']                = $student->getName();
            $row['gender']              = $student->getPrintableGenderAbbreviation();
            $row['applicationTerm']     = $assignment['application_term'];
            $row['studentType']         = $student->getPrintableType();
            $row['assignmentReason']    = $assignment['reason'];
            $row['hallName']            = $assignment['hall_name'];
            $row['roomNumber']          = $assignment['room_number'];
            $row['envelopeId']          = $assignment['envelope_id'];
            $row['envelopeStatus']      = $assignment['envelope_status'];

            $this->rows[] = $row;
        }
    }

    public function getData(){
        return $this->rows;
    }

    public function getCsvColumnsArray()
    {
        return array('Banner Id', 'Username', 'Name', 'Gender', 'Application Term', 'Student Type', 'Hall Name', 'Room Number', 'Envelope ID', 'Envelope Status');
    }

    public function getCsvRowsArray()
    {
        return $this->rows;
    }
}
