<?php

/*
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class SpecialNeedsRequest extends Report {
    const friendlyName = 'Special Needs Request';
    const shortName = 'SpecialNeedsRequest';

    private $term;
    private $rows;
    public $p_total = 0;
    public $s_total = 0;
    public $g_total = 0;
    public $m_total = 0;

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
        $db->addColumn('student_type');

        $db->addColumn('physical_disability');
        $db->addColumn('psych_disability');
        $db->addColumn('medical_need');
        $db->addColumn('gender_need');

        $db->addWhere('term', $this->term);
        $db->addWhere('physical_disability', 1, '=', 'OR', 'conditions');
        $db->addWhere('psych_disability', 1, '=', 'OR', 'conditions');
        $db->addWhere('medical_need', 1, '=', 'OR', 'conditions');
        $db->addWhere('gender_need', 1, '=', 'OR', 'conditions');

        $results = $db->select();
        if (empty($results)) {
            return;
        } elseif (PEAR::isError($results)) {
            throw new DatabaseException($results->toString());
        }

        foreach ($results as $student) {

            $sf = StudentFactory::getStudentByBannerId($student['banner_id'], $term);
            $student['name'] = $sf->getFullName();
            $student['class'] = $sf->getClass();
            $student['type'] = $sf->getType();

            if ($student['physical_disability']) {
                $this->rows['f'][] = $student;
                $this->f_total++;
            }

            if ($student['psych_disability']) {
                $this->rows['s'][] = $student;
                $this->s_total++;
            }

            if ($student['medical_need']) {
                $this->rows['m'][] = $student;
                $this->m_total++;
            }

            if ($student['gender_need']) {
                $this->rows['g'][] = $student;
                $this->g_total++;
            }
        }
    }

    public function getRows()
    {
        return $this->rows;
    }

}

?>
