<?php

namespace Homestead;

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
        javascript('jquery');
        javascript('jquery_ui');

        $tpl = array();

        $tpl['TERM'] = Term::toString($this->term) . ' - ' . Term::toString(Term::getNextTerm($this->term));
        $tpl['FALL_TERM'] = Term::toString($this->term);

        /*
         * onSubmit command
         */
        $form = new \PHPWS_Form();
        $submitCmd = CommandFactory::getCommand('ReApplicationFormSubmit');
        $submitCmd->setTerm($this->term);
        $submitCmd->initForm($form);

        /*
         * Contact info
         */
        if(isset($_REQUEST['number'])){
            $form->addText('number', $_REQUEST['number']);
        }else{
            $form->addText('number');
        }
        $form->setSize('number', 10);
        $form->setMaxSize('number', 10);
        $form->addCssClass('number', 'form-control');

        $form->addCheck('do_not_call', 1);

        /*********************
         * Emergency Contact *
         *********************/
        $form->addText('emergency_contact_name');
        $form->addCssClass('emergency_contact_name', 'form-control');
        $form->addText('emergency_contact_relationship');
        $form->addCssClass('emergency_contact_relationship', 'form-control');
        $form->addText('emergency_contact_phone');
        $form->addCssClass('emergency_contact_phone', 'form-control');
        $form->addText('emergency_contact_email');
        $form->addCssClass('emergency_contact_email', 'form-control');
        $form->addTextArea('emergency_medical_condition');
        $form->addCssClass('emergency_medical_condition', 'form-control');

/*
        if(!is_null($this->existingApplication)){
            $form->setValue('emergency_contact_name', $this->existingApplication->getEmergencyContactName());
            $form->setValue('emergency_contact_relationship', $this->existingApplication->getEmergencyContactRelationship());
            $form->setValue('emergency_contact_phone', $this->existingApplication->getEmergencyContactPhone());
            $form->setValue('emergency_contact_email', $this->existingApplication->getEmergencyContactEmail());
            $form->setValue('emergency_medical_condition', $this->existingApplication->getEmergencyMedicalCondition());
        }
*/
        /******************
         * Missing Person *
        ******************/

        $form->addText('missing_person_name');
        $form->addCssClass('missing_person_name', 'form-control');
        $form->addText('missing_person_relationship');
        $form->addCssClass('missing_person_relationship', 'form-control');
        $form->addText('missing_person_phone');
        $form->addCssClass('missing_person_phone', 'form-control');
        $form->addText('missing_person_email');
        $form->addCssClass('missing_person_email', 'form-control');
/*
        if(!is_null($this->existingApplication)){
            $form->setValue('missing_person_name', $this->existingApplication->getMissingPersonName());
            $form->setValue('missing_person_relationship', $this->existingApplication->getMissingPersonRelationship());
            $form->setValue('missing_person_phone', $this->existingApplication->getMissingPersonPhone());
            $form->setValue('missing_person_email', $this->existingApplication->getMissingPersonEmail());
        }
*/
        /*
         * Meal Plan
         */
        $mealPlans = array(MealPlan::BANNER_MEAL_LOW=>_('Low'),
                            MealPlan::BANNER_MEAL_STD=>_('Standard'),
                            MealPlan::BANNER_MEAL_HIGH=>_('High'),
                            MealPlan::BANNER_MEAL_SUPER=>_('Super'));
        $form->addDropBox('meal_plan', $mealPlans);
        $form->setLabel('meal_plan', 'Meal plan: ');
        $form->setMatch('meal_plan', MealPlan::BANNER_MEAL_STD);
        $form->addCssClass('meal_plan', 'form-control');

        /*
         * Special interest stuff
         */
        // RLC
        $form->addCheck('rlc_interest', array('rlc_interest'));
        $form->setLabel('rlc_interest', "I'm interested in applying for (or continuing in) a Residential Learning Community.");

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
        $reasons['intl_exchange']    = "International exchange ending";

        $form->addDropBox('early_release', $reasons);
        $form->setLabel('early_release', 'Will you apply for early release?');
        $form->setMatch('early_release', 'no');
        $form->addCssClass('early_release', 'form-control');


        /*
         * Contract
         */
        $form->addCheck('deposit_check', array('deposit_check'));
        $form->setLabel('deposit_check', 'I understand & acknowledge that if I cancel my License Contract my student account will be charged <strong>$250</strong>.  If I cancel my License Contract after July 1, I will be liable for the entire amount of the on-campus housing fees for the Fall semester.');

        $form->addSubmit('submit', 'Submit re-application');

        $form->mergeTemplate($tpl);

        Layout::addPageTitle("Re-Application Form");

        return \PHPWS_Template::process($form->getTemplate(), 'hms', 'student/reapplicationForm.tpl');
    }
}
