<?php

/**
 * Unassigned Freshmen Report
 * Computes the list of all unassigned freshmen for the given term.
 * 
 * TODO: Use raw SQL to join hms_roommates table for roommate username and banner id,
 * instead of looping over calls to HMS_Roommate::get_confirmed_roommate() like we do now
 * 
 * TODO: Horizontal PDF dispaly
 * 
 * @author jbooker
 * @package HMS
 */

class UnassignedFreshmen extends Report implements iCsvReport {
    
    const friendlyName = 'Unassigned Freshmen';
    const shortName = 'Unassigned Freshmen';
    
    private $term;
    
    // Counts
    private $total;
    private $male;
    private $female;
    
    private $data;
    
    public function __construct($id = 0){
        parent::__construct($id);
        
        $this->total = 0;
        $this->male = 0;
        $this->female = 0;
        
        $this->data = array();
    }
    
    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        PHPWS_Core::initModClass('hms', 'HousingApplication.php');
        PHPWS_Core::initModClass('hms', 'SpringApplication.php');
        PHPWS_Core::initModClass('hms', 'SummerApplication.php');
        PHPWS_Core::initModClass('hms', 'FallApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        
        $term = $this->term;
        $sem = Term::getTermSem($term);
        $year = Term::getTermYear($term);
        
        // List of student 'application terms' which we'll consider as 'Freshmen' for term we're looking at
        // E.g. Students with an applicationt erm in Summer 1, Summer 2, and Fall all count as Freshmen for Fall.  
        $applicationTerms = array();
        
        $db = new PHPWS_DB('hms_new_application');
        $db->addColumn('hms_new_application.*');
        
        // Join for additional application data based on semester
        switch($sem){
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                $db->addJoin('', 'hms_new_application', 'hms_summer_application', 'id', 'id');
                $db->addColumn('hms_summer_application.*');
                $applicationTerms[] = $term;
                break;
            case TERM_FALL:
                $db->addJoin('', 'hms_new_application', 'hms_fall_application', 'id', 'id');
                $db->addColumn('hms_fall_application.*');
                
                // Add the summer 1 and summe 2 application terms
                $summer2 = Term::getPrevTerm($term);
                $summer1 = Term::getPrevTerm($summer2);
                
                $applicationTerms[] = $summer1;
                $applicationTerms[] = $summer2;
                $applicationTerms[] = $term;
                break;
            case TERM_SPRING:
                $db->addJoin('', 'hms_new_application', 'hms_spring_application', 'id', 'id');
                $db->addColumn('hms_spring_application.*');
                $applicationTerms[] = $term;
                break;
            default:
                // error
                throw new InvalidArgumentException('Invalid term specified.');
        }
        
        // Join for un-assigned students
        $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_assignment', 'banner_id', 'banner_id AND hms_new_application.term = hms_assignment.term');
        $db->addWhere('hms_assignment.banner_id', 'NULL');
        $db->addWhere('hms_new_application.term', $term);
        
        // Don't show students who are type 'W' or have cancelled applications
        $db->addWhere('hms_new_application.withdrawn', 0);
        $db->addWhere('hms_new_application.student_type', 'W', '!=');
        
        // Limit for freshmen-only
        $db->addWhere('application_type', 'fall');
        
        // Limit by application term
        foreach($applicationTerms as $t){
            $db->addWhere('application_term', $t, '=', 'OR', 'app_term_group');
        }
        
        // Sort by gender, then application date (earliest to latest)
        $db->addOrder(array('gender ASC', 'created_on ASC'));

        $results = $db->select();
        
        if(PHPWS_Error::isError($results)){
            throw new DatabaseException($results->toString());
        }
        
        // Post-processing, cleanup, making it pretty
        foreach($results as $row){
            
            // Updates counts
            $this->total++;
            
            if($row['gender'] == MALE){
                $this->male++;
            }else if($row['gender'] == FEMALE){
                $this->female++;
            }
            
            $row['application_term'] = Term::toString($row['application_term']);
            $row['gender'] = HMS_Util::formatGender($row['gender']);
            $row['created_on'] = HMS_Util::get_short_date_time($row['created_on']);
            $row['meal_plan'] = HMS_Util::formatMealOption($row['meal_plan']);
            
            $row['lifestyle_option'] = HMS_Util::formatLifestyle($row['lifestyle_option']);
            $row['room_condition'] = HMS_Util::formatRoomCondition($row['room_condition']);
            $row['preferred_bedtime'] = HMS_Util::formatBedtime($row['preferred_bedtime']);
            
            // Roommates
            $roommie = HMS_Roommate::get_confirmed_roommate($row['username'], $this->term);
            if(!is_null($roommie)){
                $row['roommate'] = $roommie->getUsername();
                $row['roommate_banner_id'] = $roommie->getBannerId();
            }
            
            // Copy the cleaned up row to the member var for data
            $this->data[] = $row;
        }
    }

    public function getCsvColumnsArray()
    {
        return array_keys($this->data[0]);
    }
    
    public function getCsvRowsArray(){
        return $this->data;
    }
    
    public function getData(){
        return $this->data;
    }
    
    public function setTerm($term)
    {
        $this->term = $term;
    }
    
    public function getTerm()
    {
        return $this->term;
    }
    
    public function getTotal(){
        return $this->total;
    }
    
    public function getMale(){
        return $this->male;
    }
    
    public function getFemale(){
        return $this->female;
    }
}

?>