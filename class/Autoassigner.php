<?php

PHPWS_Core::initModClass('hms', 'HousingApplication.php');
PHPWS_Core::initModClass('hms', 'HMS_Room.php');

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

        PHPWS_Core::initModClass('hms', 'RoommatePairingStrategy.php');
        PHPWS_Core::initModClass('hms', 'RequestedRoommatePairingStrategy.php');
        PHPWS_Core::initModClass('hms', 'PreferencesRoommatePairingStrategy.php');

        PHPWS_Core::initModClass('hms', 'AssignmentStrategy.php');
        PHPWS_Core::initModClass('hms', 'SpecialAssignmentStrategy.php');
        PHPWS_Core::initModClass('hms', 'SingleGenderAssignmentStrategy.php');
        PHPWS_Core::initModClass('hms', 'CoedAssignmentStrategy.php');
        PHPWS_Core::initModClass('hms', 'RandomAssignmentStrategy.php');

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
        echo "Apps:  " . count($this->applications) . "\n";
        echo "Pairs: " . count($this->pairs) . "\n";

        // Run each pairing strategy
        foreach($this->pairingStrategies as $strategy){
            $strategy->doPairing($this->applications, $this->pairs);
        }
        echo "Apps:  " . count($this->applications) . "\n";
        echo "Pairs: " . count($this->pairs) . "\n";

        // Randomize the array of pairs
        //shuffle($this->pairs);

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
