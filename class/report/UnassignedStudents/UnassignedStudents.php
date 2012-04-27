<?php

/**
 * Unassigned Students Report
 * Computes the list of all unassigned students for the given term.
 * 
 * TODO: Use raw SQL to join hms_roommates table for roommate username and banner id,
 * instead of looping over calls to HMS_Roommate::get_confirmed_roommate() like we do now
 * 
 * TODO: Horizontal PDF dispaly
 * 
 * @author jbooker
 * @package HMS
 */

class UnassignedStudents extends Report implements iCsvReport {
    
    const friendlyName = 'Unassigned Students';
    const shortName = 'UnassignedStudents';
    
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
        PHPWS_Core::initModClass('hms', 'LotteryApplication.php');
        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        
        $term = $this->term;

        $sem = Term::getTermSem($term);
        $year = Term::getTermYear($term);
        
        $db = new PHPWS_DB('hms_new_application');
        $db->addColumn('hms_new_application.*');
        
        $applicationClassName = '';
        
        // Join for additional application data based on semester
        switch($sem){
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                $db->addJoin('', 'hms_new_application', 'hms_summer_application', 'id', 'id');
                $db->addColumn('hms_summer_application.*');
                $applicationClassName = 'SummerApplication';
                //$db->addWhere('application_type', 'summer');
                break;
            case TERM_FALL:
                $db->addJoin('', 'hms_new_application', 'hms_fall_application', 'id', 'id');
                $db->addColumn('hms_fall_application.*');
                $applicationClassName = 'FallApplication';
                //$db->addWhere('application_type', 'fall');
                break;
            case TERM_SPRING:
                $db->addJoin('', 'hms_new_application', 'hms_spring_application', 'id', 'id');
                $db->addColumn('hms_spring_application.*');
                $applicationClassName = 'SpringApplication';
                //$db->addWhere('application_type', 'spring');
                break;
            default:
                // error
                throw new InvalidArgumentException('Invalid term specified.');
        }

        // Limit to the given term
        $db->addWhere('hms_new_application.term', $term);
        
        // Join for un-assigned students
        $db->addJoin('LEFT OUTER', 'hms_new_application', 'hms_assignment', 'banner_id', 'banner_id AND hms_new_application.term = hms_assignment.term');
        $db->addWhere('hms_assignment.banner_id', 'NULL');
        
        // Don't show students who are type 'W' or have cancelled applications
        $db->addWhere('hms_new_application.cancelled', 0);
        
        // Sort by gender, then application date (earliest to latest)
        $db->addOrder(array('student_type ASC', 'gender ASC', 'created_on ASC'));
        
        $results = $db->getObjects($applicationClassName);
        
        if(PHPWS_Error::isError($results)){
            throw new DatabaseException($results->toString());
        }
        
        // Post-processing, cleanup, making it pretty
        foreach($results as $app){
            
            // Updates counts
            $this->total++;
            
            if($app->getGender() == MALE){
                $this->male++;
            }else if($app->getGender() == FEMALE){
                $this->female++;
            }
            
            // Copy the cleaned up row to the member var for data
            $this->data[] = $app->unassignedStudentsFields();
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
