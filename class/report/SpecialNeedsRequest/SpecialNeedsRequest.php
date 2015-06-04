<?php

/*
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class SpecialNeedsRequest extends Report {
    const friendlyName = 'Special Needs Request';
    const shortName = 'SpecialNeedsRequest';

    private $term;
    private $sorted_rows = array();
    private $all_rows;
    private $problems;

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
        $f_total = $s_total = $m_total = $g_total = 0;

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
            try {
                $sf = StudentFactory::getStudentByBannerId($student['banner_id'], $this->term);
                $student['name'] = $sf->getFullName();
                $student['class'] = $sf->getClass();
            } catch (Exception $e) {
                $student['name'] = $student['class'] = null;
                $this->problems[] = $student['banner_id'];
            }
            $student['style'] = 'nope';

            if ($student['physical_disability']) {
                $student['f_word'] = $student['f_total'] = null;
                $this->sorted_rows['f'][] = $student;
                $f_total++;
            }

            if ($student['psych_disability']) {
                $student['s_word'] = $student['s_total'] = null;
                $this->sorted_rows['s'][] = $student;
                $s_total++;
            }

            if ($student['medical_need']) {
                $student['m_word'] = $student['m_total'] = null;
                $this->sorted_rows['m'][] = $student;
                $m_total++;
            }

            if ($student['gender_need']) {
                $student['g_word'] = $student['g_total'] = null;
                $this->sorted_rows['g'][] = $student;
                $g_total++;
            }

            $this->all_rows[] = $student;
        }
        $this->sorted_rows['f'][0]['f_word'] = 'Physical';
        $this->sorted_rows['f'][0]['f_total'] = $f_total;

        $this->sorted_rows['s'][0]['s_word'] = 'Psychological';
        $this->sorted_rows['s'][0]['s_total'] = $s_total;

        $this->sorted_rows['m'][0]['m_word'] = 'Medical';
        $this->sorted_rows['m'][0]['m_total'] = $m_total;

        $this->sorted_rows['g'][0]['g_word'] = 'Gender';
        $this->sorted_rows['g'][0]['g_total'] = $g_total;

        $this->sorted_rows['f'][0]['style'] = 'nope cat';
        $this->sorted_rows['s'][0]['style'] = 'nope cat';
        $this->sorted_rows['m'][0]['style'] = 'nope cat';
        $this->sorted_rows['g'][0]['style'] = 'nope cat';
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


