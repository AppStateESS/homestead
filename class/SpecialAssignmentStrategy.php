<?php

class SpecialAssignmentStrategy extends Assignmentstrategy
{
    public function __construct($term)
    {
        parent::__construct($term);
    }

    public function doAssignment($pair)
    {
        $student = $pair->get('mcclainar');
        if($student != null) {
            $room = $this->roomSearchPlusCoed($student->getGender(), FALSE, 'DTR');
            $this->assign($pair, $room);
            return true;
        }

        // TODO: Add other special children here, using above template.

        return false;
    }
}

?>
