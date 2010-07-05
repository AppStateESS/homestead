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
        $this->assign($pair, $room);
        return true;
    }
}

?>
