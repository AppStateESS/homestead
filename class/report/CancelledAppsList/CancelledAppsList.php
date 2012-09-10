<?php

/**
 * Cancelled Housing Applications List
 * Generates a list of all cancelled housing aplications
 * for a selected term.
 *
 * @author Jeremy Booker
 * @package HMS
 */
class CancelledAppsList extends Report implements iCsvReport{

    const friendlyName = 'Cancelled Housing Applications List';
    const shortName    = 'CancelledAppsList';

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
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        // Select all cancelled apps for the given term
        $db = new PHPWS_DB('hms_new_application');

        $db->addWhere('cancelled', 1);
        $db->addWhere('term', $this->term);

        $results = $db->select();

        // Initialize storage for processed rows
        $this->rows = array();

        // Get friendly cancellation reasons from HousingApplication
        $reasons = HousingApplication::getCancellationReasons();

        // Process and store each result
        foreach($results as $app){
            $row = array();

            $row['bannerId']            = $app['banner_id'];
            $row['username']            = $app['username'];
            $row['gender']              = HMS_Util::formatGender($app['gender']);
            $row['application_term']    = $app['application_term'];
            $row['student_type']        = $app['student_type'];
            $row['cancelled_reason']    = $reasons[$app['cancelled_reason']];
            $row['cancelled_on']        = HMS_Util::get_long_date($app['cancelled_on']);
            $row['cancelled_by']        = $app['cancelled_by'];

            $this->rows[] = $row;
        }
    }

    public function getCsvColumnsArray()
    {
        return array('Banner Id', 'Username', 'Gender', 'Application Term', 'Student Type', 'Cancellation Reason', 'Cancellation Date', 'Cancelled By');
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
