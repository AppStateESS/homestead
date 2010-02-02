<?php

class StudentMenuProfileView extends View {
    
    private $student;
    private $startDate;
    private $endDate;
    
    public function __construct(Student $student, $startDate, $endDate)
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
    
    public function show()
    {
        $tpl = array();
        
        $tpl['BEGIN_DEADLINE'] = HMS_Util::getFriendlyDate($this->startDate);
        
        return PHPWS_Template::process($tpl, 'hms', 'student/menuBlocks/StudentProfileMenuBlock.tpl');
    }
}