<?php

namespace Homestead\Report\CancelledAppsByType;

use \Homestead\Report;

/**
 * Report fo listing the number of cancelled housing application by student type.
 *
 * @author Jeremy Booker
 * @package hms
 */
class CancelledAppsByType extends Report {

    const friendlyName = 'Cancelled Housing Applications by Student Type';
    const shortName    = 'CancelledAppsByStudentType';

    private $term;
    private $typeCounts;

    public function execute()
    {
        $db = new \PHPWS_DB('hms_new_application');

        $db->addColumn('student_type');
        $db->addColumn('id', null, 'count', true);
        $db->addWhere('term', $this->getTerm());
        $db->addWhere('cancelled', 1);

        $db->addGroupBy('student_type');

        $this->typeCounts = $db->select('assoc');
    }

    public function getTypeCounts()
    {
        return $this->typeCounts;
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
