<?php

namespace Homestead;

/**
 * RlcAssignmentState
 *
 * Abstract parent class for various RLC assignment states.
 *
 * @author jbooker
 * @package HMS
 */
abstract class RlcAssignmentState {

    protected $stateName = null;
    protected $rlcAssignment;

    public function __construct(HMS_RLC_Assignment $rlcAssignment){
        $this->rlcAssignment = $rlcAssignment;
    }

    public abstract function onEnter();

    public function getStateName(){
        if($this->stateName == null){
            throw new \InvalidArgumentException('Invalid RlcAssignmentState name.');
        }

        return $this->stateName;
    }
}
