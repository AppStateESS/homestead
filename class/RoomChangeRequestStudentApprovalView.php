<?php

class RoomChangeRequestStudentApprovalView {

    private $student;
    private $request;
    private $participants;
    private $thisParticpant;

    public function __construct(Student $student, RoomChangeRequest $request, Array $participants, RoomChangeParticipant $thisParticipant)
    {
        $this->student = $student;
        $this->request = $request;
        $this->participants = $participants;
        $this->thisParticpant = $thisParticipant;
    }

    public function show()
    {
        return 'blah';
    }
}

?>
