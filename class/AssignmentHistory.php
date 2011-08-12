<?php

PHPWS_Core::initModClass('hms', 'HMS_Item.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');

class AssignmentHistory extends HMS_Item {
	
	private final $db_table = 'hms_assignment_history';
	
	public $id = null;
	public $banner_id;
	public $room;
	public $assigned_on;
	public $assigned_by;
	public $assigned_reason;
	public $removed_on;
	public $removed_by;
	public $removed_reason;
	
	/**
	 * returns the id of this object
	 * 
	 * @param none
	 * @return int id of this object
	 */
	public getID() {
		return $this->$id;
	}
	
	/**
	 * sets the banner id member inside this object
	 * 
	 * @param int $bannerID the banner ID of student
	 * @return boolean flag to signal failure/success
	 */
	public setBanner($bannerID=null) {
		if ( is_null($bannerID) )
			return false;
		
		$this->banner_id = $bannerID;
		return true;
	}
	
	/**
	 * sets the room member inside this object
	 * 
	 * @param String $room the room in which this history relates
	 * @return boolean flag to signal failure/success
	 */
	public setRoom($room=null) {
		if ( is_null($room) )
			return false;
		
		$this->room = $room;
	}
	
	public setAssign($assign_reason=ASSIGN_NOREASON, $assigned_by=null, $assigned_on=null) {
		if ( is_null($assigned_on) ) // use current time
			$this->assigned_on = DateTime::getTimestamp();
		else
			$this->assigned_on = $assigned_on;
			
		if ( is_null($assigned_by) ) // use current user
			$this->assigned_by = UserStatus::getUsername();
		else
			$this->assigned_by = $assigned_by;	
			
		$this->assign_reason = $assign_reason;
	}
	
	public setRemove($removed_reason=UNASSIGN_NOREASON, $removed_by=null, $removed_on=null) {
		if ( is_null($removed_on) ) // use current time
			$this->removed_on = DateTime::getTimestamp();
		else
			$this->removed_on = $removed_on;
			
		if ( is_null($removed_by) ) // use current user
			$this->removed_by = UserStatus::getUsername();
		else
			$this->removed_by = $removed_by;	
		
		$this->removed_reason = $removed_reason;
	}
	
	public init($id=null) {
		if ( is_null($id) ) 
			return false;
		
		// do a database call
		$db = new PHPWS_DB('hms_assignment_history');
    	$db->addWhere('id', $id);
        $result = $db->load($this);
		
		if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
        return true;
	}
	
	public static getHistory($id) {
		if (is_null($id))
			return false;
		
		// do a database call
		$db = new PHPWS_DB('hms_assignment_history');
    	$db->addWhere('id', $id);
        
    	// create an AssignmentHistory object with results
	   	$rObject = new AssignmentHistory;
        $result = $db->load($rObject);
		
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
		// return object
		return $rObject;
	}
	
}
?>