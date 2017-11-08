<?php

namespace Homestead;

class Autoassigner {

    private $term;

    private $applications;
    private $pairs;
    private $rooms;

    private $pairingStrategies;
    private $assignmentStrategies;

    public function __construct($term)
    {
        $this->term = $term;

        echo "Term is $term\n\n";

        # Load all the unassigned applicants for this term
        $this->applications     = HousingApplication::getUnassignedFreshmenApplications($term, null);

        # Setup the pairing strategies
        $this->pairingStrategies = array();
        $this->pairingStrategies[] = new RequestedRoommatePairingStrategy($term);
        $this->pairingStrategies[] = new PreferencesRoommatePairingStrategy($term);

        # Setup the assignment strategies
        $this->assignmentStrategies = array();
        $this->assignmentStrategies[] = new SpecialAssignmentStrategy($term);
        $this->assignmentStrategies[] = new SingleGenderAssignmentStrategy($term);
        $this->assignmentStrategies[] = new CoedAssignmentStrategy($term);
        $this->assignmentStrategies[] = new RandomAssignmentStrategy($term);
    }

    public function autoassign()
    {
        echo "Pending applications:  " . count($this->applications) . "\n";

        // Run each pairing strategy
        foreach($this->pairingStrategies as $strategy){
            $strategy->doPairing($this->applications, $this->pairs);
        }

        echo "Roommate Pairs: " . count($this->pairs) . "\n";

        // Randomize the array of pairs
        //shuffle($this->pairs);

        if(count($this->pairs) <= 0){
            echo "No roommate pairs to assign. Autoassigner is done. \n";
            return;
        }

        // Sort the array of pairs by application date (use the earliest of the two application dates)
        usort($this->pairs, array("Autoassigner", "pairSort"));

        $fp = fopen('/tmp/WTFISGOINGON', 'w');

        // Run each assignment strategy
        foreach($this->assignmentStrategies as $strategy) {
            $paircount = 0;
            $pairtotal = count($this->pairs);
            $assignedcount = 0;
            foreach($this->pairs as $pair) {
                $paircount++;
                if(!isset($pair->count)) {
                    $pair->count = 0;
                }
                $pair->count++;
                fwrite($fp, "[$paircount/$pairtotal] Strategy is " . get_class($strategy) . ".  " . $pair->__toString() . " seen " . $pair->count . " times.\n");
                fflush($fp);
                if($pair->isAssigned()) continue;
                if($strategy->doAssignment($pair)) {
                    fwrite($fp, "Assigned" . ++$assignedcount . "\n");
                    fflush($fp);
                }
            }
        }

        fclose($fp);

        shuffle($this->pairs);

        foreach($this->pairs as $pair) {
            if($pair->isAssigned()) {
                echo $pair->getStudent1()->getUsername() . " is assigned to " . $pair->getBed1() . "\n";
                echo $pair->getStudent2()->getUsername() . " is assigned to " . $pair->getBed2() . "\n\n";
            }
        }

        foreach($this->pairs as $pair) {
            if(!$pair->isAssigned()) {
                echo $pair->getStudent1()->getUsername() . " did not get assigned.\n";
                echo $pair->getStudent2()->getUsername() . " did not get assigned.\n\n";

            }
        }
    }

    private static function pairSort($a, $b)
    {
        return ($a->getEarliestAppTimestamp() < $b->getEarliestAppTimestamp()) ? -1 : 1;
    }
}
