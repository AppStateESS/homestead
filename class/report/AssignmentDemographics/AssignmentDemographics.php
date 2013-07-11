<?php

/**
 * The Assignment Demographics report.
 * Calculates totals breaking down who is assigned to each hall
 * by hall, student type, class, and gender. Gives grand totals.
 *
 * @author jbooker
 * @package HMS
 */

class AssignmentDemographics extends Report {

    const friendlyName = 'Assignment Demographics';
    const shortName    = 'AssignmentDemographics';

    private $term;

    private $studentTypes;
    private $classes;
    private $genders;

    private $halls; // A list of hall IDs and names
    
    private $hallSummaries; // Array holding the summary data for each hall
    private $hallGenders;
    private $hallTypes;
    
    private $grandTotals; // Array holding total data
    private $grandTotalsByGender;
    private $grandTotalsByType;

    private $problems; // List of students we couldn't lookup in Banner

    public function __construct($id = 0)
    {
        parent::__construct($id);

        $this->hallSummaries  = array();

        $this->studentTypes   = array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING, 'OTHER');
        $this->classes        = array(CLASS_FRESHMEN, CLASS_SOPHOMORE, CLASS_JUNIOR, CLASS_SENIOR);
        $this->genders        = array(MALE, FEMALE);

        $this->hallSummaries  = array(); // Array of halls containing counts by type/class/gender
        
        $this->grandTotals    = array(); // totals over all halls by type/class/gender
        $this->initializeArray($this->grandTotals);
        
        $this->genderTotals   = array(); // totals by gender
        $this->typeTotals     = array(); // totals by student stype
        
        // Initalize the array for totals by gender
        foreach($this->genders as $g){
            $this->grandTotalsByGender[$g] = 0;
        }
        
        // Initalize the array for totals by student type
        foreach($this->studentTypes as $t){
            $this->grandTotalsByType[$t] = 0;
        }
        
        $this->problems       = array();
    }

    public function execute()
    {
        // Load the necessary classes
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        
        // Get a list of hall IDs and names
        $this->halls = $this->getHallList();

        // For each hall, get every assignment in that hall and tally it up
        foreach($this->halls as $hall){
            // Get all the assignments in this hall
            $assignments = $this->getAssignmentsByHall($hall['id']);

            // Generate the summary for this hall based the assignments,
            // Add the summary to the set of summaries
            $this->hallSummaries[$hall['hall_name']] = $this->getSummary($assignments);
        }

        // Generate the grand totals
        $this->calcGrandTotals();
    }

    private function getHallList()
    {
        $db = new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addWhere('term', $this->term);
        $db->addWhere('is_online', 1); // only get halls that are online
        $db->addOrder('hall_name', 'asc');
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    private function getAssignmentsByHall($hallId)
    {
        $db = new PHPWS_DB('hms_assignment');
        $db->addColumn('hms_assignment.banner_id');

        // Limit to just the requested hall id
        $db->addWhere('hms_residence_hall.id', $hallId);

        // Join the assignment all the way up to the hall
        $db->addJoin('LEFT OUTER', 'hms_assignment',    'hms_bed',              'bed_id',               'id');
        $db->addJoin('LEFT OUTER', 'hms_bed',           'hms_room',             'room_id',              'id');
        $db->addJoin('LEFT OUTER', 'hms_room',          'hms_floor',            'floor_id',             'id');
        $db->addJoin('LEFT OUTER', 'hms_floor',         'hms_residence_hall',   'residence_hall_id',    'id');

        // Don't report on anything that's not online
        $db->addWhere('hms_room.offline',               0);
        $db->addWhere('hms_floor.is_online',            1);
        $db->addWhere('hms_residence_hall.is_online',   1);

        $assignments = $db->select();

        if(PHPWS_Error::logIfError($assignments)) {
            throw new DatabaseException($assignments->toString());
        }

        return $assignments;
    }

    private function initializeArray(Array &$arr)
    {
        // Initialize the multi-dimensional array
        foreach($this->studentTypes as $t){
            foreach($this->classes as $c){
                foreach($this->genders as $g){
                    $arr[$t][$c][$g] = 0;
                }
            }
            
        }
        
        $arr['OTHER'] = 0;
    }
    
    private function getSummary(Array $assignments)
    {
        // Create the summary array for this set of assignments and initialize it
        $summary = array();
        $this->initializeArray($summary);
        
        foreach($assignments as $assign){
            // Create the student object
            //TODO use banner IDs
            try{
                $student = StudentFactory::getStudentByBannerId($assign['banner_id'], $this->term);
            }catch(Exception $e){
                $this->problems[] = $assign['banner_id'] . ': Unknown student';
                $summary['OTHER']++;
                continue;
            }

            // Get the student's gener in numeric form
            $gender = $student->getGender();

            // Check the gender for bad data
            if(!isset($gender) || $gender === NULL || ($gender != MALE && $gender != FEMALE)) {
                $this->problems[] = $assign['asu_username'] .': Gender is unrecognized ('. $gender .')';
                $summary['OTHER']++;
                continue;
            }

            # Get the class of the student for this assignment
            $class = $student->getClass();

            # Check the class for bad data
            if(!isset($class) || $class === NULL ||
            ($class != CLASS_FRESHMEN && $class != CLASS_SOPHOMORE &&
            $class != CLASS_JUNIOR && $class != CLASS_SENIOR)) {
                //$this->problems[] = $assign['asu_username'] . ': Class is unrecognized ('. $class .')';
                $summary['OTHER']++;
                continue;
            }

            // Get the type of the student for this assignment
            $type = $student->getType();

            // Check the type for bad data
            if(!isset($type) || $type === NULL ||
            ($type != TYPE_FRESHMEN && $type != TYPE_TRANSFER && $type != TYPE_CONTINUING && $type != TYPE_READMIT && $type != TYPE_RETURNING)) {
                //$this->problems[] = $assign['asu_username'] . ': Type is unrecognized ('. $type .')';
                $summary['OTHER']++;
                continue;
            }

            // Force returing and re-admit types to be type continuing
            if($type == TYPE_RETURNING || $type == TYPE_READMIT){
                $type = TYPE_CONTINUING;
            }
            
            // If student type is freshmen, force class to freshmen
            if($type == TYPE_FRESHMEN){
                $class = CLASS_FRESHMEN;
            }
            
            $summary[$type][$class][$gender]++;
        }
        
        return $summary;
    }
    
    private function calcGrandTotals()
    {
        foreach($this->hallSummaries as $hall){
            foreach($this->studentTypes as $t){
                // handle the 'other' type and skip the other types
                if($t == 'OTHER'){
                    $this->grandTotalsByType['OTHER'] += $hall['OTHER'];
                    continue;
                }
                // handle all the other types
                foreach($this->classes as $c){
                    foreach($this->genders as $g){
                        $this->grandTotals[$t][$c][$g] += $hall[$t][$c][$g];
                        $this->grandTotalsByGender[$g] += $hall[$t][$c][$g];
                        $this->grandTotalsByType[$t] += $hall[$t][$c][$g];
                    }
                }
            }
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
    
    public function getHallSummaries(){
        return $this->hallSummaries;
    }
    
    public function getGrandTotals(){
        return $this->grandTotals;
    }
    
    public function getGrandTotalsByType(){
        return $this->grandTotalsByType;
    }
    
    public function getGrandTotalsByGender(){
        return $this->grandTotalsByGender;
    }
    
    public function getProblemsList(){
    	return $this->problems;
    }
}

?>
