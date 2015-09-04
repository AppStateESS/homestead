<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

class AssignedStudentData extends Report implements iCsvReport {
    const friendlyName = 'Assigned Student Data Export';
    const shortName = 'AssignedStudentData';
    const category = "Assignments";

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

        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('hms_assignment.*');
        $db->addColumn('hms_residence_hall.*');
        $db->addColumn('hms_room.*');
        $db->addColumn('hms_new_application.*');

        $db->addWhere('hms_assignment.term', $this->term);

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_bed', 'bed_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_bed', 'hms_room', 'room_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_room', 'hms_floor', 'floor_id', 'id');
        $db->addJoin('LEFT OUTER', 'hms_floor', 'hms_residence_hall', 'residence_hall_id', 'id');

        $db->addJoin('LEFT OUTER', 'hms_assignment', 'hms_new_application', 'banner_id', 'banner_id AND hms_assignment.term = hms_new_application.term');

        $results = $db->select();

        if (PHPWS_Error::logIfError($results)) {
            return $results;
        }


        foreach ($results as $row) {
            try {
                $student = StudentFactory::getStudentByUsername($row['asu_username'], $this->term);
            } catch (StudentNotFoundException $e) {
                $username = $row['asu_username'];
                $username = '';
                $first = '';
                $middle = '';
                $last = '';
                $gender = '';
                $dob = '';
                $type = '';
                $cellPhone = '';
                $line1 = "";
                $line2 = "";
                $line3 = "";
                $city = "";
                $state = "";
                $zip = "";
                $appTerm = "";
            }

            $bannerId = $student->getBannerId();

            if (!isset($bannerId) || is_null($bannerId) || empty($bannerId)) {
                continue;
            }

            $username = $student->getUsername();
            $first = $student->getFirstName();
            $middle = $student->getMiddleName();
            $last = $student->getLastName();
            $type = $student->getType();
            $appTerm = $student->getApplicationTerm();
            $cellPhone= $row['cell_phone'];
            $assignmentType = $row['reason'];

            $gender = HMS_Util::formatGender($student->getGender());
            $dob = $student->getDob();

            $room = $row['hall_name'] . ' ' . $row['room_number'];

            $address = $student->getAddress(NULL);

            if (!$address || !isset($address) || is_null($address)) {
                $line1 = "";
                $line2 = "";
                $line3 = "";
                $city = "";
                $state = "";
                $zip = "";
            } else {
                $line1 = $address->line1;
                $line2 = $address->line2;
                $line3 = $address->line3;
                $city = $address->city;
                $state = $address->state;
                $zip = $address->zip;
            }

            $this->rows[] = array($username,$bannerId,$first,$middle,$last,$gender,$dob,$type,$appTerm,$cellPhone,$assignmentType,$room,$line1,$line2,$line3,$city,$state,$zip);
        }
    }

    public function getCsvColumnsArray()
    {
        return array('Username', 'Banner id', 'First name', 'Middle name', 'Last Name', 'Gender', 'Birthday',
            'Student type', 'Application Term', 'Cell Phone', 'Assignment Type','Assignment', 'Address 1',
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
