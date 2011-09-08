<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class TwentyFive extends Report {

    const friendlyName = 'Students 25 and Older';
    const shortName = 'TwentyFive';

    private $term;
    private $all_rows;

    public function __construct($id = 0)
    {
        parent::__construct($id);
    }

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

        if (!isset($this->term) || is_null($this->term)) {
            throw new InvalidArgumentException('Missing term.');
        }

        $term = Term::getTermSem($this->term);

        $db = new PHPWS_DB('hms_new_application');

        $db->addColumn('banner_id');
        $db->addColumn('username');
$db->setLimit(10);
        $db->addWhere('term', $this->term);

        $results = $db->select();
        if (empty($results)) {
            return;
        } elseif (PEAR::isError($results)) {
            throw new DatabaseException($results->toString());
        }

        foreach ($results as $student) {
            $sf = StudentFactory::getStudentByBannerId($student['banner_id'], $term);
            $student['name'] = $sf->getFullName();
            $student['dob'] = $sf->getDOB();
            $this->all_rows[] = $student;
        }

        test($this->all_rows,1);
        
    }

    public function getSortedRows()
    {
        return $this->sorted_rows;
    }

    public function getCsvColumnsArray()
    {
         return array('Banner ID', 'Username', 'Student Type', 'Physical Need', 'Psychological Need', 'Medical Need', 'Gender-based Need', 'Name', 'Class Status');
    }

    public function getCsvRowsArray()
    {
        return $this->all_rows;
    }


}

?>
