<?php

namespace Homestead;

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

        $form = new \PHPWS_Form();
        $submitCmd = CommandFactory::getCommand('OffCampusWaitingListFormSave');
        $submitCmd->setTerm($this->term);
        $submitCmd->initForm($form);

        $waitlistReasons = array(
                            '-1' => 'Select a reason',
                            'off_campus' => 'I currently live off campus',
                            'missed_deadline' => 'I missed the re-application deadline'
                        );
        $form->addDropBox('waitlist_reason', $waitlistReasons);
        $form->setLabel('waitlist_reason', 'I am applying for the open waitlist because:');
        $form->addCssClass('waitlist_reason', 'form-control');

        $oncampusReasons = array(
                            '-1' => 'Select a reason',
                            'december_grad' => 'I will graduate in December',
                            'study_abroad' => 'I am currently studying abroad',
                            'financial_need' => 'I have a high financial need',
                            'other' => 'Other, please explain below'
                        );
        $form->addDropBox('oncampus_reason', $oncampusReasons);
        $form->setLabel('oncampus_reason', 'The reason I need on-campus housing is:');
        $form->addCssClass('oncampus_reason', 'form-control');

        $form->addTextArea('oncampus_other_reason');
        $form->addCssClass('oncampus_other_reason', 'form-control');
        $form->setLabel('oncampus_other_reason', 'If you chose "Other" above, please explain your need. Include reasons why on-campus housing better meets this need than off-campus options:');

        $mealOptions = array(MealPlan::BANNER_MEAL_LOW => _('Low'), MealPlan::BANNER_MEAL_STD => _('Standard'), MealPlan::BANNER_MEAL_HIGH => _('High'), MealPlan::BANNER_MEAL_SUPER => _('Super'));
        $form->addDropBox('meal_option', $mealOptions);
        $form->setClass('meal_option', 'form-control');
        $form->setMatch('meal_option', MealPlan::BANNER_MEAL_STD);
        $form->addCssClass('meal_option', 'form-control');

        $form->addText('number');
        $form->setSize('number', 10);
        $form->setMaxSize('number', 10);
        $form->addCssClass('number', 'form-control');

        if (isset($_REQUEST['number'])) {
            $form->setValue('number', $_REQUEST['number'], 0);
        }

        $form->addCheck('do_not_call', 1);
        if (isset($_REQUEST['do_not_call'])) {
            $form->setMatch('do_not_call', 1);
        }


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

        $mealPlans = array(MealPlan::BANNER_MEAL_LOW=>_('Low'),
                MealPlan::BANNER_MEAL_STD=>_('Standard'),
                MealPlan::BANNER_MEAL_HIGH=>_('High'),
                MealPlan::BANNER_MEAL_SUPER=>_('Super'));
        $form->addDropBox('meal_plan', $mealPlans);
        $form->setLabel('meal_plan', 'Meal plan: ');
        $form->setMatch('meal_plan', MealPlan::BANNER_MEAL_STD);

        $form->addCheck('deposit_check', array('deposit_check'));
        $form->setLabel('deposit_check', 'I understand & acknowledge that if I cancel my License Contract after I am assigned a space in a residence hall my student account will be charged $250.  If I cancel my License Contract after July 1, I will be liable for the entire amount of the on-campus housing fees for the Fall semester.');

        $form->addSubmit('submit', 'Submit waiting list application');

        $form->mergeTemplate($tpl);

        return \PHPWS_Template::process($form->getTemplate(), 'hms', 'student/reapplicationOffcampus.tpl');
    }

}
