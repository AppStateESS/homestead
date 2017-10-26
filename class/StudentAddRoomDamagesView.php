<?php

namespace Homestead;

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
        $tpl = array();

        $assignment = HMS_Assignment::getAssignment($this->student->getUsername(), $this->term);

        $bed = $assignment->get_parent();

        $room = $bed->get_parent();

        $tpl['ROOM_PID'] = $room->getPersistentId();
        $tpl['ROOM_LOCATION'] = $room->where_am_i();
        $tpl['TERM'] = $this->term;

        $tpl['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tpl['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'studentRoomDamage');

        return \PHPWS_Template::process($tpl, 'hms', 'student/addRoomDamages.tpl');
    }
}
