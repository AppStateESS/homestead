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

    const friendlyName = 'Freshmen Applicant Demographics';
    const shortName    = 'ApplicantDemographics';
    
    private $term;
    
    private $applicationTotals;
    
    private $maleTotals;
    private $maleSum;
    
    private $femaleTotals;
    private $femaleSum;
    
    private $typeTotals;
    private $total;

    public function __construct($id = 0)
    {
        parent::__construct($id);
        
        $this->applicationTotals    = array();
        $this->typeTotals           = array();
        $this->maleSum              = 0;
        $this->femaleSum            = 0;
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
        $db->addWhere('hms_new_application.term', $this->term);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            throw new DatabaseException($results->toString());
        }

        $types      = array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING, TYPE_NONDEGREE, TYPE_WITHDRAWN);
        $genders    = array(MALE, FEMALE);

        // Initalize the array for totals
        foreach($types as $t){
            foreach($genders as $g){
                $this->applicationTotals[$t][$g] = 0;
            }
        }

        // Calculate the totals
        foreach($results as $application){
            // Adjust the student types to count 'readmit' and 'returning' as 'continuing' instead
            if($application['student_type'] == TYPE_READMIT || $application['student_type'] == TYPE_RETURNING){
                $studentType = TYPE_CONTINUING;
            }else{
                $studentType = $application['student_type'];
            }
            $this->applicationTotals[$studentType][$application['gender']]++;
        }

        // Male sum
        foreach($types as $type){
            $this->maleTotals[] = $this->applicationTotals[$type][MALE];
            $this->maleSum += $this->applicationTotals[$type][MALE];
        }

        // Female sum
        foreach($types as $type){
            $this->femaleTotals[] = $this->applicationTotals[$type][FEMALE];
            $this->femaleSum += $this->applicationTotals[$type][FEMALE];
        }

        // Type sums
        foreach($types as $type){
            $this->typeTotals[$type] = array_sum($this->applicationTotals[$type]);
        }

        $this->total = $this->femaleSum + $this->maleSum;
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
    
    public function getMaleSum(){
        return $this->maleSum;
    }
    
    public function getFemaleSum(){
        return $this->femaleSum;
    }
    
    public function getTypeTotals(){
        return $this->typeTotals;
    }
    
    public function getTotal(){
        return $this->total;
    }
}

?>