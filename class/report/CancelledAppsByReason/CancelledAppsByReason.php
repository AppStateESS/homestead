<?php

/**
 * Report for listing the number of cancelled housing applications by cancellation reason.
 *
 * @author Jeremy Booker
 * @package HMS
 */

class CancelledAppsByReason extends Report {

    const friendlyName = 'Cancelled Housing Applications by Cancellation Reason';
    const shortName    = 'CancelledAppsByReason';

    private $term;
    private $reasonCounts;
    private $freshmenReasonCounts;
    private $continuingReasonCounts;
    private $reasons;

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        
        $this->reasons = HousingApplication::getCancellationReasons();
        
        // All students
        $db = new PHPWS_DB('hms_new_application');
        
        $db->addColumn('cancelled_reason');
        $db->addColumn('id', null, 'myCount', true);
        $db->addWhere('term', $this->getTerm());
        $db->addWhere('cancelled', 1);
        
        $db->addGroupBy('cancelled_reason');
        
        $this->reasonCounts = $db->select('assoc');

        // Freshmen
        $db = new PHPWS_DB('hms_new_application');
        $db->addColumn('cancelled_reason');
        $db->addColumn('id', null, 'myCount', true);
        $db->addWhere('term', $this->getTerm());
        $db->addWhere('cancelled', 1);
        $db->addWhere('student_type', TYPE_FRESHMEN);
        
        $db->addGroupBy('cancelled_reason');

        $this->freshmenReasonCounts = $db->select('assoc');

        // Continuing
        $db = new PHPWS_DB('hms_new_application');
        $db->addColumn('cancelled_reason');
        $db->addColumn('id', null, 'myCount', true);
        $db->addWhere('term', $this->getTerm());
        $db->addWhere('cancelled', 1);
        $db->addWhere('student_type', TYPE_CONTINUING);
        
        $db->addGroupBy('cancelled_reason');
        
        $this->continuingReasonCounts = $db->select('assoc');
    }
    
    public function getReasonCounts()
    {
        return $this->reasonCounts;
    }
   
    public function getFreshmenReasonCounts()
    {
        return $this->freshmenReasonCounts;
    }

    public function getContinuingReasonCounts()
    {
        return $this->continuingReasonCounts;
    }

    public function getReasons()
    {
        return $this->reasons;
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
?>
