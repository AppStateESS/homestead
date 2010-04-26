<?php

/**
 * Autoassigner - Class for automatically assigning freshmen to available beds
 */

class AutoAssigner {
    
    // Settings
    private $term;
    private $debug; // If true, don't actually create/queue any assignments

    /*
     * State tracking  
     */
    // Student state tracking
    private $usersToAssign      = array(); // Simple array of user names to assign
    private $assignedStatus     = array(); // List of all students to be assigned, and whether they've been assigned or not
    // Room state tracking - will pop from room arrays as rooms are used
    private $freeFemaleRooms    = array();
    private $freeMaleRooms      = array();
    
    // Output accumulators
    private $successes  = array();
    private $notices    = array();
    private $problems   = array();
    
    public function __construct($term, $debug)
    {
        $this->term     = $term;
        $this->debug    = $debug;
        
        // Get all the free rooms
        $this->freeFemaleRooms = HMS_Room::getAlLFreeRooms($this->term, FEMALE);
        $this->freeFemaleRooms = HMS_Room::getAlLFreeRooms($this->term, MALE);
        
        // Get everyone who needs to be assigned
    }
    
    public function autoAssign()
    {
        
    }
    
    public function assignForGender($gender)
    {
        
    }
}

?>