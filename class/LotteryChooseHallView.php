<?php

class LotteryChooseHallView extends homestead\View {
    
    private $student;
    private $term;
    
    public function __construct(Student $student, $term)
    {
        $this->student = $student;
        $this->term = $term;
    }
    
    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
            
        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));
        
        $halls = HMS_Residence_Hall::get_halls($this->term);

        $output_list = array();

        foreach($halls as $hall){
            $row = array();
            $row['HALL_NAME']       = $hall->hall_name;
            $row['ROW_TEXT_COLOR']  = 'black';

            //$rooms_used = $hall->count_lottery_full_rooms();
            
            # Make sure we have a room of the specified gender available in the hall (or a co-ed room)
            if($hall->count_avail_lottery_rooms($this->student->getGender()) <= 0){
                $row['ROW_TEXT_COLOR'] = 'grey';
                $tpl['hall_list'][] = $row;
                continue;
            }

            $chooseCmd = CommandFactory::getCommand('LotteryChooseHall');
            $chooseCmd->setHallId($hall->id);
            $row['HALL_NAME']   = $chooseCmd->getLink($hall->hall_name);
            $tpl['hall_list'][] = $row;
        }

        Layout::addPageTitle("Choose Hall");

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_hall.tpl');
    }
}
