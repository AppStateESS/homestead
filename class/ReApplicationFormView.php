<?php

/**
 * Shows the re-application (lottery) form.
 *
 * @author jbooker
 * @package hms
 */

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
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');

        javascript('jquery');
        javascript('jquery_ui');

        $tpl = array();

        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));
        $tpl['FALL_TERM'] = Term::toString($this->term);

        /*
         * onSubmit command
         */
        $form = new PHPWS_Form();
        $submitCmd = CommandFactory::getCommand('ReApplicationFormSubmit');
        $submitCmd->setTerm($this->term);
        $submitCmd->initForm($form);

        /*
         * Contact info
         */
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

        /*
         * Meal Plan
         */
        $mealPlans = array(BANNER_MEAL_LOW=>_('Low'),
        BANNER_MEAL_STD=>_('Standard'),
        BANNER_MEAL_HIGH=>_('High'),
        BANNER_MEAL_SUPER=>_('Super'));
        $form->addDropBox('meal_plan', $mealPlans);
        $form->setLabel('meal_plan', 'Meal plan: ');
        $form->setMatch('meal_plan', BANNER_MEAL_STD);

        /*
         * Special interest stuff
         */
        /*
         PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
         $special_interests = HMS_Lottery::get_special_interest_groups();

         $form->addDropBox('special_interest', $special_interests);
         $form->setLabel('special_interest', 'Special interest group: ');
         */

        // RLC
        $form->addCheck('rlc_interest', array('rlc_interest'));
        $form->setLabel('rlc_interest', "I'm interseted in applying for (or continuing in) a Residential Learning Community.");

        // Sorority
        if($this->student->getGender() == FEMALE){
            $sororities = HMS_Lottery::getSororities();

            $form->addCheck('sorority_check', array('sorority_check'));
            $form->setLabel('sorority_check', "I'm a member of a sorority.");

            $form->addDropBox('sorority_drop', array_merge(array('none'=>'Select...'), HMS_Lottery::getSororities()));
            $form->setLabel('sorority_drop', 'Which sorority?');

            $form->addRadioButton('sorority_pref', array('aph', 'on-campus'));
            $form->setLabel('sorority_pref', array("I would like to live in the APH.", "I would like to live in a central-campus hall."));
        }

        // Teaching Fellows
        if($this->student->isTeachingFellow()){
            $form->addRadioButton('tf_pref', array('with_tf', 'not_tf'));
            $form->setLabel('tf_pref', array("I would like to live with other Teaching Fellows.", "I would like to live elsewhere on-campus."));
        }

        // Watauga Global
        if($this->student->isWataugaMember()){
            $form->addRadioButton('wg_pref', array('with_wg', 'not_wg'));
            $form->setLabel('wg_pref', array("I would like to live with other Watauga Global students.", "I would like to live elsewhere on-campus."));
        }

        // Honors
        if($this->student->isHonors()){
            $form->addRadioButton('honors_pref', array('with_honors', 'not_honors'));
            $form->setLabel('honors_pref', array("I would like to live in Honors Housing.", "I would like to live elsewhere on campus."));
        }

        /*
         * Special needs
         */
        $form->addCheck('special_need', array('special_need'));
        $form->setLabel('special_need', array('Yes, I require special needs housing.'));

        if(isset($_REQUEST['special_need'])){
            $form->setMatch('special_need', $_REQUEST['special_need']);
        }
        
        /*
         * Early Release
         */
        $nextTerm = Term::toString(Term::getNextTerm($this->term));
        $reasons = array();
        $reasons['no']               = "No, I'll be staying through $nextTerm.";
        $reasons['grad']             = "Graduating in December";
        $reasons['student_teaching'] = "Student Teaching in Spring";
        $reasons['internship']       = "ASU-sponsored Internship";
        $reasons['transfer']         = "Transferring to other University";
        $reasons['withdraw']         = "Withdrawing";
        $reasons['marriage']         = "Getting married";
        $reasons['study_abroad']     = "Study Abroad for Spring";
        $reasons['intl_exchagne']    = "International exchange ending";
        
        $form->addDropBox('early_release', $reasons);
        $form->setLabel('early_release', 'Will you apply for early release?');
        $form->setMatch('early_release', 'no');
        

        /*
         * Contract
         */
        $form->addCheck('deposit_check', array('deposit_check'));
        $form->setLabel('deposit_check', 'I understand & acknowledge that if I cancel my License Contract my student account will be charged <strong>$250</strong>.  If I cancel my License Contract after July 1, I will be liable for the entire amount of the on-campus housing fees for the Fall semester.');

        $form->addSubmit('submit', 'Submit re-application');
        //$form->setExtra('submit', 'class="hms-application-submit-button"');

        $form->mergeTemplate($tpl);

        Layout::addPageTitle("Re-Application Form");

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/lottery_signup.tpl');
    }
}

?>