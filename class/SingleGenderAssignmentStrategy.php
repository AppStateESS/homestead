<?php

class SingleGenderAssignmentStrategy extends Assignmentstrategy
{
    public function __construct($term)
    {
        parent::__construct($term);
    }

    public function doAssignment($pair)
    {
        if($pair->getLifestyle() != 1) return false;

        $room = $this->roomSearch($pair->getGender(), 1);

        if(is_null($room)){
            echo "Could not find a room for ".$pair->__toString()."\n";
            return false;
        }

        $this->assign($pair, $room);
        return true;
    }
}

?>
