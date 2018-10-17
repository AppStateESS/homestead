<?php

namespace Homestead\Report\AppliedStudentData;

use \Homestead\Report;
use \Homestead\iCsvReport;
use \Homestead\Term;
use \Homestead\HMS_Assignment;
use \Homestead\StudentFactory;
use \Homestead\CommandFactory;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class AppliedStudentData extends Report implements iCsvReport {

    const friendlyName = 'Applied Student Data Export';
    const shortName = 'AppliedStudentData';

    private $term;
    private $rows;

    public function setTerm($term) {
        $this->term = $term;
    }

    public function getTerm() {
        return $this->term;
    }

    public function execute() {
        $db = new \PHPWS_DB('hms_new_application');
        $db->addColumn('hms_new_application.*');
        $db->addWhere('term', $this->term);
        $db->addWhere('cancelled', 0);

        $term = Term::getTermSem($this->term);

        if ($term == TERM_FALL) {
            $db->addJoin('LEFT', 'hms_new_application', 'hms_fall_application', 'id', 'id');
            $db->addColumn('hms_fall_application.*');
        } else if ($term == TERM_SPRING) {
            $db->addJoin('LEFT', 'hms_new_application', 'hms_spring_application', 'id', 'id');
            $db->addColumn('hms_spring_application.*');
        } else if ($term == TERM_SUMMER1 || $term == TERM_SUMMER2) {
            $db->addJoin('LEFT', 'hms_new_application', 'hms_summer_application', 'id', 'id');
            $db->addColumn('hms_summer_application.*');
        }

        $result = $db->select();

        $app = array();

        foreach ($result as $app) {

            $username = $app['username'];
            $bannerId = $app['banner_id'];
            $type = $app['student_type'];
            $cellPhone = $app['cell_phone'];
            $date = date('n/j/Y', $app['created_on']);


            $assignment = HMS_Assignment::getAssignmentByBannerId($bannerId, $this->term);

            if (!is_null($assignment)) {
                $room = $assignment->where_am_i();
                $reason = $assignment->getReason();
            } else {
                $room = '';
                $reason = '';
            }

            $student = StudentFactory::getStudentByBannerId($bannerId, $this->term);

            $preferredName = $student->getPreferredName();
            $first = $student->getFirstName();
            $middle = $student->getMiddleName();
            $last = $student->getLastName();
            $gender = $student->getPrintableGender();
            $birthday = date("m/d/Y", $student->getDobDateTime()->getTimestamp());
            $appTerm = $student->getApplicationTerm();

            $address = $student->getAddress(NULL);

            if ($term == TERM_SPRING || $term == TERM_FALL) {
                $lifestyle = ($app['lifestyle_option'] == 1) ? 'Single Gender' : 'Co-Ed';
            } else {
                $lifestyle = ($app['room_type'] == 1) ? 'Single Room' : 'Double Room';
            }
            
            $over_21 = "No";
            // Calculate the timestamp from 21 years ago
            $twentyOneYearsAgo = strtotime("-21 years");
            $DOB = strtotime($birthday);
            
            if ($DOB < $twentyOneYearsAgo) {
                $over_21 = "Yes";
            }
            
            if (!is_null($address) && $address !== false) {
                $this->rows[] = array(
                            $username, $bannerId, $preferredName, $first, $middle, $last, $gender, $birthday,
                            $type, $cellPhone, $date, $appTerm, $lifestyle, $room, $reason, $address->line1, $address->line2,
                            $address->line3, $address->city,
                            $address->state, $address->zip
                );
            } else {
                $this->rows[] = array($username, $bannerId, $preferredName, $first, $middle, $last, '',
                            $type, $cellPhone, $date, $appTerm, $lifestyle, $room, $reason, $date, '', '', '', '', '');
            }
        }
    }

    public function getCsvColumnsArray() {
        return array('Username', 'Banner id', 'Preferred Name', 'First name', 'Middle name',
            'Last name', 'Gender', 'Birthday', 'Student type', 'Cell Phone', 'Date Applied', 'Application Term', 'Lifestyle',
            'Assignment', 'Assignment Type', 'Address 1', 'Address 2', 'Address 3', 'City', 'State', 'Zip');
    }

    public function getCsvRowsArray() {
        return $this->rows;
    }

    public function getDefaultOutputViewCmd() {
        $cmd = CommandFactory::getCommand('ShowReportCsv');
        $cmd->setReportId($this->id);

        return $cmd;
    }

}
