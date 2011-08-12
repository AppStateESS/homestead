<?php

PHPWS_Core::initModClass('hms', 'AssignmentHistory.php');
PHPWS_Core::initModClass('hms', 'Term.php');

class StudentAssignmentHistory extends ArrayObject{

	// http://weierophinney.net/matthew/archives/131-Overloading-arrays-in-PHP-5.2.0.html
	public function __construct($bannerID = null) {
        if ( !is_null($bannerID) ) {
        	init($bannerID);
        }

        // Allow accessing properties as either array keys or object properties:
        parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);
    }
    
    /**
     * initialize this object (fill the array) with assignment histories
     * 
     * @param int $bannerID banner id of student
     * @param int $term term to be searching
     * @return boolean flag to signal if the initialization was a success
     */
    private init ($bannerID=null, $term=null) {	
    	if ( is_null($bannerID) )
    		return false;
    	
    	if ( is_null($term) ) // default to current term
    		$term = Term::getCurrentTerm();
 
    	$db = new PHPWS_DB('hms_assignment_history');
    	$db->addWhere('banner_id', $bannerID);
    	$db->addWhere('term', $term);
        $db->loadClass('hms', 'AssignmentHistory.php');
        $result = $db->getObjects('AssignmentHistory');
        
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
        // Push results onto StudentAssignmentHistory array
        array_push($this, $result);
        
        return true;
    }
	
	
	/**
	 * adds an assignment to this object's array
	 * 
	 * @param AssignmentHistory|int $assignmentHistory an assignment history object or the id of one
	 * @return boolean result of addition
	 */
	public add($assignmentHistory) {
		if ( is_int($assignmentHistory) ) { // if a history id was passed instead of the object
			$id = $assignmentHistory;
			$assignmentHistory = AssignmentHistory::getHistory($id);
			if (is_null($assignmentHistory)) // history does not exist in database, can't add
				return false;
		} else // object was passed, so use internal id
			$id = $assignmentHistory->getID();
		
		if ( !isset($this[$id]) ) {
			$this[$id] = $assignmentHistory;
			return true;
		}
		
		return false;
	}
	
	/**
	 * removes an assignment from this object's array
	 * 
	 * @param AssignmentHistory|int $assignmentHistory an assignment history object or the id of one
	 * @return boolean|AssignmentHistory false if failed or the removed assignment object if success
	 */
	public remove($assignmentHistory) {
		if ( is_int($assignmentHistory) ) // if a history id was passed instead of the object
			$id = $assignmentHistory;
		else // object was passed, so use internal id
			$id = $assignmentHistory->getID();
		
		if ( isset($this[$id]) ) {
			$rObject = $this[$id];
			unset($this[$id]);
			return $rObject;
		}
		
		return false;
	}
	
	/**
	 * get the student assignment history array object
	 * 
	 * @param none
	 * @return array Student's assignments
	 */
	public getAssignments() {
		return $this;
	}
	
	/**
	 * Static function to allow direct pull of assignments
	 * when passed banner id
	 * 
	 * @param int $banner_id the student's banner ID
	 * @return array assignments associated with student
	 */
	public static getAssignments($banner_id) {
		return new StudentAssignmentHistory($banner_id);
	}
}

?>