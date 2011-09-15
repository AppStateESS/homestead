<?php

/**
 * StudentAssignmentHistory.php
 * 
 * @author Adam D. Dixon
 */

PHPWS_Core::initModClass('hms', 'AssignmentHistory.php');
PHPWS_Core::initModClass('hms', 'Term.php');

class StudentAssignmentHistory extends ArrayObject{

	private $theArray;
	
	// http://weierophinney.net/matthew/archives/131-Overloading-arrays-in-PHP-5.2.0.html
	public function __construct($bannerID = null) {
        $this->theArray = array();
		
		if ( !is_null($bannerID) ) {
        	$this->init($bannerID);
        }

        // Allow accessing properties as either array keys or object properties:
        //parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);
    }
    
    public function get() {
    	return $this->theArray;
    }
    
    /**
     * initialize this object (fill the array) with assignment histories
     * 
     * @param int $bannerID banner id of student
     * @param int $term term to be searching
     * @return boolean flag to signal if the initialization was a success
     */
    private function init ($bannerID=null, $term=null) {	
    	if ( is_null($bannerID) )
    		return false;
    	
    	if ( is_null($term) ) // default to current term
    		$term = Term::getCurrentTerm();
 
    	$db = new PHPWS_DB('hms_assignment_history');
    	$db->addWhere('banner_id', $bannerID);
        $db->loadClass('hms', 'AssignmentHistory.php');
		//$db->addOrder('term', 'DESC');
        $db->addOrder(array('term DESC', 'assigned_on DESC'));
        $result = $db->getObjects('AssignmentHistory');
        
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
        if ( isset($result) ) {
	        foreach( $result as $ah ) {
	        	if ( defined($ah->assigned_reason) )
	        		$ah->assigned_reason = constant($ah->assigned_reason); // for pretty text purposes
	        	if ( defined($ah->removed_reason) )
	        		$ah->removed_reason = constant($ah->removed_reason); // for pretty text purposes
	        	
	        	if ( !is_null($ah->assigned_on) ) 
	        		$ah->assigned_on = date('M jS, Y \a\t g:ia', $ah->assigned_on);
	        	if ( !is_null($ah->removed_on) )
	        		$ah->removed_on = date('M jS, Y \a\t g:ia', $ah->removed_on);

	        	if ( !is_null($ah->term) )
	        		$ah->term = Term::toString($ah->term);
	        		
	        	// Combine for ease of view
	        	if ( isset($ah->assigned_reason) ) {
	        		$ah->assignments = 	'<font style="font-style:italic;">'.$ah->assigned_reason.'</font>'.
	        							' by '.$ah->assigned_by.
	        							'<br /><font style="font-size:11px;color:#7C7C7C;">on '.$ah->assigned_on.'</font>';
	        	} else 
	        		$ah->assignments = '<font style="font-style:italic;">No Assignment Record</font>';
	        	
	        	if ( isset($ah->removed_reason) ) {
	        		$ah->unassignments = 	'<font style="font-style:italic;">'.$ah->removed_reason.'</font>'.
	        								' by '.$ah->removed_by.
	        								'<br /><font style="font-size:11px;color:#7C7C7C;">on '.$ah->removed_on.'</font>';
	        	} else
	        		$ah->unassignments = '<font style="font-style:italic;font-weight:bold;">No Unassignment Record</font>';
	        		
	        	$this->theArray[] = (array) $ah;
	        }
        }
        
        return true;
    }
	
	
	/**
	 * adds an assignment to this object's array
	 * 
	 * @param AssignmentHistory|int $assignmentHistory an assignment history object or the id of one
	 * @return boolean result of addition
	 */
	public function add($assignmentHistory) {
		if ( is_int($assignmentHistory) ) { // if a history id was passed instead of the object
			$id = $assignmentHistory;
			$assignmentHistory = AssignmentHistory::getHistory($id);
			if (is_null($assignmentHistory)) // history does not exist in database, can't add
				return false;
		} else // object was passed, so use internal id
			$id = $assignmentHistory->getID();
		
		if ( !isset($this[$id]) ) {
			$this->theArray[$id] = $assignmentHistory;
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
	public function remove($assignmentHistory) {
		if ( is_int($assignmentHistory) ) // if a history id was passed instead of the object
			$id = $assignmentHistory;
		else // object was passed, so use internal id
			$id = $assignmentHistory->getID();
		
		if ( isset($this->theArray[$id]) ) {
			$rObject = $this->theArray[$id];
			unset($this->theArray[$id]);
			return $rObject;
		}
		
		return false;
	}
	
	/**
	 * Static function to allow direct pull of assignments
	 * when passed banner id
	 * 
	 * @param int $banner_id the student's banner ID
	 * @return array assignments associated with student
	 */
	public static function getAssignments($banner_id) {
		$sah = new StudentAssignmentHistory($banner_id);
		return $sah->get();
	}
}

?>
