<?php

namespace Homestead;

PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

class StudentAddRoomDamagesView extends View {

    private $student;

    private $term;

    public function __construct(Student $student, $term)
    {
        $this->student  = $student;
        $this->term     = $term;
    }

    public function show()
    {
        javascript('jquery');

        $tpl = array();

        $assignment = HMS_Assignment::getAssignment($this->student->getUsername(), $this->term);

        $bed = $assignment->get_parent();

        $room = $bed->get_parent();

        $tpl['ROOM_PID'] = $room->getPersistentId();
        $tpl['ROOM_LOCATION'] = $room->where_am_i();
        $tpl['TERM'] = $this->term;

        return \PHPWS_Template::process($tpl, 'hms', 'student/addRoomDamages.tpl');
    }
}
