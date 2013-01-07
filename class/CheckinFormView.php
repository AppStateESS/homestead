<?php

class CheckinFormView extends View {

    private $student;
    private $assignment;
    private $application;
    private $hall;
    private $floor;
    private $room;

    public function __construct(Student $student, HMS_Assignment $assignment, HousingApplication $application = null, HMS_Residence_Hall $hall, HMS_Floor $floor, HMS_Room $room)
    {
        $this->student      = $student;
        $this->assignment   = $assignment;
        $this->application  = $application;
        $this->hall         = $hall;
        $this->floor        = $floor;
        $this->room         = $room;
    }

    public function show()
    {
        $tpl = array();

        $tpl['NAME'] = $this->student->getName();
        $tpl['ASSIGNMENT'] = $this->assignment->where_am_i();

        return PHPWS_Template::process($tpl, 'hms', 'admin/checkinForm.tpl');
    }

}

?>
