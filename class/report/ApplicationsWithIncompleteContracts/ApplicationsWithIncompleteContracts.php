<?php

namespace Homestead\report\ApplicationsWithIncompleteContracts;

/**
 * Cancelled Housing Applications List
 * Generates a list of all cancelled housing aplications
 * for a selected term.
 *
 * @author Jeremy Booker
 * @package HMS
 */
class ApplicationsWithIncompleteContracts extends Report implements iCsvReport{

    const friendlyName = 'Applications with Incomplete Contracts';
    const shortName    = 'ApplicationsWithIncompleteContracts';

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
                    hms_new_application.banner_id,
                    hms_new_application.username,
                    hms_new_application.gender,
                    hms_new_application.application_term,
                    hms_new_application.student_type,
                    hms_residence_hall.hall_name,
                    hms_room.room_number,
                    hms_contract.envelope_id,
                    hms_contract.envelope_status
                FROM hms_new_application
                    LEFT OUTER JOIN hms_contract
                        ON (hms_new_application.banner_id = hms_contract.banner_id AND hms_new_application.term = hms_contract.term)
                    LEFT OUTER JOIN hms_assignment
                        ON (hms_new_application.banner_id = hms_assignment.banner_id AND hms_new_application.term = hms_assignment.term)
                    LEFT OUTER JOIN hms_bed ON hms_assignment.bed_id = hms_bed.id
                    LEFT OUTER JOIN hms_room ON hms_bed.room_id = hms_room.id
                    LEFT OUTER JOIN hms_floor ON hms_room.floor_id = hms_floor.id
                    LEFT OUTER JOIN hms_residence_hall ON hms_floor.residence_hall_id = hms_residence_hall.id

                    WHERE
                        hms_new_application.term = :term and
                        (hms_contract.envelope_status != 'completed' OR hms_contract.envelope_status IS NULL)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array('term'=>$this->term));


        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize storage for processed rows
        $this->rows = array();

        // Process and store each result
        foreach($results as $app){
            $row = array();
            $student = StudentFactory::getStudentByBannerId($app['banner_id'], $this->term);

            $row['bannerId']            = $app['banner_id'];
            $row['username']            = $app['username'];
            $row['name']                = $student->getName();
            $row['gender']              = HMS_Util::formatGender($app['gender']);
            $row['applicationTerm']     = $app['application_term'];
            $row['studentType']         = $app['student_type'];
            $row['hallName']            = $app['hall_name'];
            $row['roomNumber']          = $app['room_number'];
            $row['envelopeId']          = $app['envelope_id'];
            $row['envelopeStatus']      = $app['envelope_status'];

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
