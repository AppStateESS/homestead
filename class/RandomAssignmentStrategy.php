<?php

class RandomAssignmentStrategy extends Assignmentstrategy
{
    public function __construct($term)
    {
        parent::__construct($term);
    }

    public function doAssignment($pair)
    {
        $room = $this->roomSearchPlusCoed($pair->getGender());

        if(is_null($room)){
            echo "Could not find a room for " . $pair->__toString() . "\n";
            return false;
        }

        $this->assign($pair, $room);
        return true;
    }
}


