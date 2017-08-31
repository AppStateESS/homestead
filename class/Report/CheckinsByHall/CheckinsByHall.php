<?php

namespace Homestead\Report\CheckinsByHall;

use \Homestead\Report;
use \Homestead\Exception\DatabaseException;

/**
 * The Checkins By Hall Report.
 *
 * Gives a breakdown of assignments by their assignment reason
 * for the given term.
 *
 * @author jbooker
 * @package HMS
 */

class CheckinsByHall extends Report {

    const friendlyName = 'Check-ins By Hall';
    const shortName    = 'CheckinsByHall';

    private $term;

    private $checkinCounts;

    public function __construct($id = 0)
    {
        parent::__construct($id);

        $this->hallCounts = array();
    }

    public function execute()
    {
        $this->checkinCounts = \PHPWS_DB::getAssoc("select hall_name, count(*) from hms_checkin JOIN hms_hall_structure ON hms_checkin.bed_id = hms_hall_structure.bedid WHERE term = {$this->term} and checkout_date IS NULL GROUP BY hall_name ORDER BY hall_name");

        if(\PHPWS_Error::isError($this->checkinCounts)){
            throw new DatabaseException($this->checkinCounts->toString());
        }
    }

    /****************************
     * Accessor/Mutator Methods *
     ****************************/

    public function setTerm($term){
        $this->term = $term;
    }

    public function getTerm(){
        return $this->term;
    }

    public function getCheckinCounts()
    {
        return $this->checkinCounts;
    }
}
