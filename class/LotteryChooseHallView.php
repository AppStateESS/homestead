<?php

class LotteryChooseHallView extends hms\View {
    
    private $student;
    private $term;
    private $rlcAssignment;
    
    public function __construct(Student $student, $term, HMS_RLC_Assignment $rlcAssignment = null)
    {
        $this->student = $student;
        $this->term = $term;
        $this->rlcAssignment = $rlcAssignment;
    }
    
    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
            
        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));
        
        $halls = HMS_Residence_Hall::get_halls($this->term);

        // Check for an RLC Assignment, and that it's in the correct state
        if($this->rlcAssignment != null && $this->rlcAssignment->getStateName() == 'selfselect-invite') {
        	$rlcId = $this->rlcAssignment->getRlc()->getId();
        } else {
        	$rlcId = null;
        }

        // A watch variable, set to true when we find at least one hall that
        // still has an available bed
        $somethingsAvailable = false;

        foreach($halls as $hall){
            $row = array();
            $row['HALL_NAME']       = $hall->hall_name;
            $row['ROW_TEXT_COLOR']  = 'black';

            # Make sure we have a room of the specified gender available in the hall (or a co-ed room)
            if($hall->count_avail_lottery_rooms($this->student->getGender(), $rlcId) <= 0){
                $row['ROW_TEXT_COLOR'] = 'grey';
                $tpl['hall_list'][] = $row;
                continue;
            } else {
            	$somethingsAvailable = true;
            }

            $chooseCmd = CommandFactory::getCommand('LotteryChooseHall');
            $chooseCmd->setHallId($hall->id);
            $row['HALL_NAME']   = $chooseCmd->getLink($hall->hall_name);
            $tpl['hall_list'][] = $row;
        }
        
        if(!$somethingsAvailable){
        	unset($tpl['hall_list']);
            $tpl['NOTHING_LEFT'] = '';
        }

        Layout::addPageTitle("Choose Hall");

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_hall.tpl');
    }
}
