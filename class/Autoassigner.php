<?php

PHPWS_Core::initModClass('hms', 'HousingApplication.php');

class Autoassigner {

    private $term;

    private $maleApplications;
    private $femaleApplications;

    private $unAssignedMalePairs;
    private $unAssignedFemalePairs;

    private $assignedMalePairs;
    private $assignedFemalePairs;

    private $pairingStrategies;
    private $assignmentStrategies;

    public function __construct($term)
    {
        $this->term = $term;

        # Load all the unassigned applicants for this term
        $this->maleApplications     = HousingApplication::getUnassignedFreshmenApplications($term, MALE);
        $this->femaleApplications   = HousingApplication::getUnassignedFreshmenApplications($term, FEMALE);

        # Setup the pairing strategies
        $this->pairingStrategies = array();
        $this->pairingStrategies[] = new RequestedRoommatePairingStrategy();
        //$this->pairingStrategies[] = new PreferencesRoommatePairingStrategy();

        # Setup the assignment strategies
        //$this->assignmentStrategies[] = new SingleGenderAssignmentStrategy();
        //$this->assignmentStrategies[] = new CoedAssignmentStrategy();
        //$this->assignmentStrategies[] = new RandomAssignmentStrategy();
    }

    public function autoassign()
    {
        # Run each pairing strategy
        foreach($this->pairingStrategies as $strategy){
            $strategy->doPairing($maleApplications, $unAssignedMalePairs)
            $strategy->doPairing($femaleApplications, $unAssignedFemalePairs);
        }

        # Run each assignment strategy
    }
}

?>