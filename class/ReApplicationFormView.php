<?php

class ReApplicationFormView extends View {

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

        $tpl['NAME'] = $this->student->getFullName();
        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));

        $form = new PHPWS_Form();
        $submitCmd = CommandFactory::getCommand('ReApplicationFormSubmit');
        $submitCmd->setTerm($this->term);
        $submitCmd->initForm($form);

        if(isset($_REQUEST['area_code'])){
            $form->addText('area_code', $_REQUEST['area_code']);
        }else{
            $form->addText('area_code');
        }

        $form->setSize('area_code', 3);
        $form->setMaxSize('area_code', 3);

        if(isset($_REQUEST['exchange'])){
            $form->addText('exchange', $_REQUEST['exchange']);
        }else{
            $form->addText('exchange');
        }
        $form->setSize('exchange', 3);
        $form->setMaxSize('exchange', 3);

        if(isset($_REQUEST['number'])){
            $form->addText('number', $_REQUEST['number']);
        }else{
            $form->addText('number');
        }
        $form->setSize('number', 4);
        $form->setMaxSize('number', 4);
        $form->addCheck('do_not_call', 1);


        if(isset($_REQUEST['roommate1'])){
            $form->addText('roommate1', $_REQUEST['roommate1']);
        }else{
            $form->addText('roommate1');
        }

        if(isset($_REQUEST['roommate2'])){
            $form->addText('roommate2', $_REQUEST['roommate2']);
        }else{
            $form->addText('roommate2');
        }

        if(isset($_REQUEST['roommate3'])){
            $form->addText('roommate3', $_REQUEST['roommate3']);
        }else{
            $form->addText('roommate3');
        }

        $mealPlans = array(BANNER_MEAL_LOW=>_('Low'),
            BANNER_MEAL_STD=>_('Standard'),
            BANNER_MEAL_HIGH=>_('High'),
            BANNER_MEAL_SUPER=>_('Super'));
        $form->addDropBox('meal_plan', $mealPlans);
        $form->setLabel('meal_plan', 'Meal plan: ');
        $form->setMatch('meal_plan', BANNER_MEAL_STD);

        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        $special_interests = HMS_Lottery::get_special_interest_groups();

        $form->addDropBox('special_interest', $special_interests);
        $form->setLabel('special_interest', 'Special interest group: ');

        $form->addCheck('special_need', array('special_need'));
        $form->setLabel('special_need', array('Yes, I require special needs housing.'));

        if(isset($_REQUEST['special_need'])){
            $form->setMatch('special_need', $_REQUEST['special_need']);
        }

        $form->addCheck('deposit_check', array('deposit_check'));
        $form->setLabel('deposit_check', 'I understand & acknowledge that if I cancel my License Contract my student account will be charged $250.  If I cancel my License Contract after July 1, I will be liable for the entire amount of the on-campus housing fees for the Fall semester.');

        $form->addSubmit('submit', 'Submit re-application');

        $form->mergeTemplate($tpl);

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/lottery_signup.tpl');
    }
}

?>