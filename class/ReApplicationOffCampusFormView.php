<?php


class ReApplicationOffCampusFormView extends View {
    
    private $student;
    private $term;
    
    public function __construct(Student $student, $term)
    {
        $this->student = $student;
        $this->term = $term;
    }
    
    public function show()
    {
        $tpl = array();

        $tpl['NAME'] = $this->student->getFullName();
        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));

        $form = new PHPWS_Form();
        $submitCmd = CommandFactory::getCommand('ReApplicationWaitingListFormSubmit');
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

        $mealPlans = array(BANNER_MEAL_LOW=>_('Low'),
            BANNER_MEAL_STD=>_('Standard'),
            BANNER_MEAL_HIGH=>_('High'),
            BANNER_MEAL_SUPER=>_('Super'));
        $form->addDropBox('meal_plan', $mealPlans);
        $form->setLabel('meal_plan', 'Meal plan: ');
        $form->setMatch('meal_plan', BANNER_MEAL_STD);

        $form->addCheck('special_need', array('special_need'));
        $form->setLabel('special_need', array('Yes, I require special needs housing.'));

        if(isset($_REQUEST['special_need'])){
            $form->setMatch('special_need', $_REQUEST['special_need']);
        }

        $form->addCheck('deposit_check', array('deposit_check'));
        $form->setLabel('deposit_check', 'I understand & acknowledge that if I cancel my License Contract after I am assigned a space in a residence hall my student account will be charged $250.  If I cancel my License Contract after July 1, I will be liable for the entire amount of the on-campus housing fees for the Fall semester.');

        $form->addSubmit('submit', 'Submit waiting list application');

        $form->mergeTemplate($tpl);

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/reapplicationOffcampus.tpl');
    }
    
}