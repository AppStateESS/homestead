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
        
        // Get everyone who needs to be assigned
    }
    
    public function autoAssign()
    {
        // Get a list of all the single-gender rooms for each gender
        
        // Assign all the females who want single gender rooms
        
        // Assign all the males who want single gender rooms
        
        // Re-compile the list of free rooms for each gender, regardless of single or co-ed
        // This ensures we use all available rooms, even if we end up assigning a co-ed
        // preference person to a single-gender hall
        $this->freeFemaleRooms  = HMS_Room::getAlLFreeRooms($this->term, FEMALE);
        $this->freeMaleRooms    = HMS_Room::getAlLFreeRooms($this->term, MALE);
        
        // Re-compile the list of people to be assigned
        // Doing this after the single-gender assignments
        // ensures that we'll catch students who couldn't be assigned
        // if we ran out of single-gender rooms
        $this->usersToAssign = HousingApplication::getUnassignedApplicants($this->term);
        
        // Assign everyone (males and females at the same time, to ensure we don't
        // run out of rooms with a bunch of one gender left
        
    }
    
    /**
     * Takes a gender, and a gender preference (single gender vs. co-ed)
     * and returns an array of free rooms of that type 
     */
    private function getFreeRooms($gender, $genderPreference = NULL){
        
    }
}

?>