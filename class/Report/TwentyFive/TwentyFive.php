<?php

namespace Homestead\Report\TwentyFive;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class TwentyFive extends Report implements iCsvReport {
    const friendlyName = 'Students 25 and Older';
    const shortName = 'TwentyFive';

    private $term;
    private $all_rows;
    private $problems;

    public function __construct($id = 0)
    {
        parent::__construct($id);
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');

        if (!isset($this->term) || is_null($this->term)) {
            throw new \InvalidArgumentException('Missing term.');
        }

        $db = new \PHPWS_DB('hms_new_application');

        $db->addColumn('banner_id');
        $db->addColumn('username');
        $db->addWhere('term', $this->term);

        $results = $db->select();
        if (empty($results)) {
            return;
        } elseif (\PEAR::isError($results)) {
            throw new DatabaseException($results->toString());
        }

        $twentyFiveYearsAgo = strtotime("-25 years");

        foreach ($results as $student) {
            try {
                $sf = StudentFactory::getStudentByBannerId($student['banner_id'], $this->term);
                $dob = $sf->getDOB();
                if (strtotime($dob) > $twentyFiveYearsAgo) {
                    continue;
                }
                $student['dob'] = $dob;
                $student['full_name'] = $sf->getFullName();
                $this->all_rows[] = $student;
            } catch (\Exception $e) {
                $student['dob'] = $student['full_name'] = null;
                $this->problems[] = $student['banner_id'];
            }
        }
    }

    public function getRows()
    {
        return $this->all_rows;
    }

    public function getCsvColumnsArray()
    {
        return array('Banner ID', 'Username', 'Date of Birth', 'Name');
    }

    public function getCsvRowsArray()
    {
        return $this->all_rows;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }
}
