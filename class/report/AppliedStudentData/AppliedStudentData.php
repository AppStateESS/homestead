<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class AppliedStudentData extends Report implements iCsvReport {
    const friendlyName = 'Applied Student Data Export';
    const shortName = 'AppliedStudentData';

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
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        $db = new PHPWS_DB('hms_new_application');
        $db->addColumn('id');
        $db->addWhere('term', $this->term);

        //remove
        //$db->setLimit(50);
        $result = $db->select('col');

        $apps = array();

        foreach ($result as $id) {
            $application = HousingApplicationFactory::getApplicationById($id);

            $username   = $application->getUsername();
            $bannerId   = $application->getBannerId();
            $type       = $application->getStudentType();

            $assignment = HMS_Assignment::getAssignment($application->getUsername(), $this->term);

            if(!is_null($assignment)){
                $room = $assignment->where_am_i();
            }else{
                $room = '';
            }

            $student = StudentFactory::getStudentByUsername($username, $this->term);

            $first  = $student->getFirstName($username);
            $middle = $student->getMiddleName($username);
            $last   = $student->getLastName($username);

            $address = $student->getAddress(NULL);

            $this->rows[] =
                    array(
                        $username, $bannerId, $first, $middle, $last,
                        $type, $room, $address->line1, $address->line2,
                        $address->line3, $address->city,
                        $address->state, $address->zip
                    );
        }
    }

    public function getCsvColumnsArray()
    {
        return array('Username', 'Banner id', 'First name', 'Middle name',
            'Last name', 'Student type', 'Assignment', 'Address 1',
            'Address 2', 'Address 3', 'City', 'State', 'Zip');
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
