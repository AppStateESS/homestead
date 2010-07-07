<?php

class CoedAssignmentStrategy extends AssignmentStrategy
{
    public function __construct($term)
    {
        parent::__construct($term);
    }

    public function doAssignment($pair)
    {
        if($pair->getLifestyle() != 2) return false;

        $room = $this->roomSearchPlusCoed($pair->getGender(), 2);

        if(is_null($room)){
            return false;
        }

        $this->assign($pair, $room);
        return true;
    }
}

?>
