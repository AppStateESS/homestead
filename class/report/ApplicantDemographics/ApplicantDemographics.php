<?php

/**
 * The Applicant Demographics report.
 * Calculates totals breaking down who's applied
 * by gender, studen type, and class.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package hms
 */

class ApplicantDemographics extends Report {

    const friendlyName = 'Freshmen/Transfer Applicant Demographics';
    const shortName    = 'ApplicantDemographics';

    private $term;

    private $applicationTotals;

    private $maleTotals;
    private $maleSubTotal;

    private $femaleTotals;
    private $femaleSubTotal;

    private $typeTotals;
    private $subTotal;

    private $cancelledTotals;
    private $cancelledSubTotal;

    private $femaleGrandTotal;
    private $maleGrandTotal;

    private $total;

    public function __construct($id = 0)
    {
        parent::__construct($id);

        $this->applicationTotals    = array();
        $this->typeTotals           = array();

        $this->maleSubTotal         = 0;
        $this->femaleSubTotal       = 0;

        $this->cancelledTotals      = array();
        $this->cancelledSubTotal    = 0;

        $this->femaleGrandTotal     = 0;
        $this->maleGrandTotal       = 0;

        $this->total                = 0;
    }

    public function execute()
    {
        if(!isset($this->term) || is_null($this->term)){
            throw new InvalidArgumentException('Missing term.');
        }

        $sem = Term::getTermSem($this->term);

        switch($sem){
            case TERM_FALL:
                $db = new PHPWS_DB('hms_fall_application');
                $db->addJoin('LEFT OUTER', 'hms_fall_application', 'hms_new_application', 'id', 'id');
                break;
            case TERM_SPRING:
                $db = new PHPWS_DB('hms_spring_application');
                $db->addJoin('LEFT OUTER', 'hms_spring_application', 'hms_new_application', 'id', 'id');
                break;
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                $db = new PHPWS_DB('hms_summer_application');
                $db->addJoin('LEFT OUTER', 'hms_summer_application', 'hms_new_application', 'id', 'id');
                break;
        }

        $db->addColumn('hms_new_application.*');

        // Only applications for the selected term
        $db->addWhere('hms_new_application.term', $this->term);

        // Only non-cancelled applications
        $db->addWhere('hms_new_application.cancelled', 0);

        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException($results->toString());
        }

        $types      = array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING, TYPE_NONDEGREE, TYPE_GRADUATE);
        $genders    = array(MALE, FEMALE);

        // Initalize the array for totals
        foreach($types as $t){
            foreach($genders as $g){
                $this->applicationTotals[$t][$g] = 0;
            }
        }

        foreach($genders as $g){
            $this->cancelledTotals[$g] = 0;
        }

        // Calculate the sub-totals
        foreach($results as $application){
            // Adjust the student types to count 'readmit' and 'returning' as 'continuing' instead
            if($application['student_type'] == TYPE_READMIT || $application['student_type'] == TYPE_RETURNING){
                $studentType = TYPE_CONTINUING;
            }else{
                $studentType = $application['student_type'];
            }
            $this->applicationTotals[$studentType][$application['gender']]++;
        }

        // Male sub-total
        foreach($types as $type){
            $this->maleTotals[] = $this->applicationTotals[$type][MALE];
            $this->maleSubTotal += $this->applicationTotals[$type][MALE];
        }

        // Female sub-total
        foreach($types as $type){
            $this->femaleTotals[] = $this->applicationTotals[$type][FEMALE];
            $this->femaleSubTotal += $this->applicationTotals[$type][FEMALE];
        }

        // Type sums
        foreach($types as $type){
            $this->typeTotals[$type] = array_sum($this->applicationTotals[$type]);
        }

        // Sub-total
        $this->subTotal = $this->femaleSubTotal + $this->maleSubTotal;

        /****
         * Count the cancelled applications
         */
        $db->resetWhere();
        // Only applications for the selected term
        $db->addWhere('hms_new_application.term', $this->term);

        // Only cancelled applications
        $db->addWhere('hms_new_application.cancelled', 1);

        $results = $db->select();
        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException($results->toString());
        }

        foreach($results as $application){
            $this->cancelledTotals[$application['gender']]++;
        }

        // Cancelled sub-total
        $this->cancelledSubTotal = $this->cancelledTotals[FEMALE] + $this->cancelledTotals[MALE];

        // Gender totals
        $this->maleGrandTotal   = $this->maleSubTotal   + $this->cancelledTotals[MALE];
        $this->femaleGrandTotal = $this->femaleSubTotal + $this->cancelledTotals[FEMALE];

        // Grand total
        $this->total = $this->subTotal + $this->cancelledSubTotal;
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

    public function getMaleTotals(){
        return $this->maleTotals;
    }

    public function getFemaleTotals(){
        return $this->femaleTotals;
    }

    public function getMaleSubTotal(){
        return $this->maleSubTotal;
    }

    public function getFemaleSubTotal(){
        return $this->femaleSubTotal;
    }

    public function getTypeTotals(){
        return $this->typeTotals;
    }

    public function getCancelledTotals(){
        return $this->cancelledTotals;
    }

    public function getCancelledSubTotal(){
        return $this->cancelledSubTotal;
    }

    public function getSubTotal(){
        return $this->subTotal;
    }

    public function getFemaleGrandTotal(){
        return $this->femaleGrandTotal;
    }

    public function getMaleGrandTotal(){
        return $this->maleGrandTotal;
    }

    public function getTotal(){
        return $this->total;
    }
}
