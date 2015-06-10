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

        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form();

        $submitCmd = CommandFactory::getCommand('LotteryChooseHall');
        $submitCmd->setTerm($this->term);
        $submitCmd->initForm($form);

        $tpl = array();

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));

        $halls = HMS_Residence_Hall::get_halls($this->term);

        $hall_list = array();

        // Check for an RLC Assignment, and that it's in the correct state
        if($this->rlcAssignment != null && $this->rlcAssignment->getStateName() == 'selfselect-invite') {
        	$rlcId = $this->rlcAssignment->getRlc()->getId();
        } else {
        	$rlcId = null;
        }

        // A watch variable, set to true when we find at least one hall that
        // still has an available bed
        $somethingsAvailable = false;

        foreach ($halls as $hall)
        {
          if($hall->count_avail_lottery_rooms($this->student->getGender(), $rlcId) > 0)
          {
            $hall_list[$hall->hall_name] = $hall->hall_name;
            $somethingsAvailable = true;
          }
        }

        if($somethingsAvailable)
        {
          $form->addDropBox('hall_choices', $hall_list);
          $form->addCssClass('hall_choices', 'form-control');
          $tpl['AVAILABLE'] = '';
        }
        else {
          $tpl['NOTHING_LEFT'] = '';
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'student/lottery_choose_hall.tpl');

    }
}
