<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');
PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');
PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');

class HousingApplicationFormView extends homestead\View {

    private $student;
    private $existingApplication;
    private $term;

    public function __construct(Student $student, $term, HousingApplication $existingApplication = NULL)
    {
        $this->student				= $student;
        $this->term					= $term;
        $this->existingApplication	= $existingApplication;
    }

    public function show()
    {
        PHPWS_Core::initCoreClass('Form.php');
        $form = new PHPWS_Form();

        $submitCmd = CommandFactory::getCommand('HousingApplicationFormSubmit');
        $submitCmd->setTerm($this->term);

        $submitCmd->initForm($form);

        $tpl = array();

        /****************
         * Display Info *
        ****************/
        $tpl['STUDENT_NAME']    = $this->student->getFullName();
        $tpl['GENDER']          = $this->student->getPrintableGender();
        $tpl['ENTRY_TERM']      = Term::toString($this->student->getApplicationTerm());
        $tpl['CLASSIFICATION_FOR_TERM_LBL'] = HMS_Util::formatClass($this->student->getClass());
        $tpl['STUDENT_STATUS_LBL']			= HMS_Util::formatType($this->student->getType());
        $tpl['TERM']			= Term::toString($this->term);

        /**************
         * Cell Phone *
        */

        $form->addText('area_code');
        $form->setSize('area_code', 3);
        $form->setMaxSize('area_code', 3);

        if(!is_null($this->existingApplication)){
            $form->setValue('area_code', substr($this->existingApplication->getCellPhone(), 0, 3));
        }

        $form->addText('exchange');
        $form->setSize('exchange', 3);
        $form->setMaxSize('exchange', 3);

        if(!is_null($this->existingApplication)){
            $form->setValue('exchange', substr($this->existingApplication->getCellPhone(), 3, 3));
        }


        $form->addText('number');
        $form->setSize('number', 4);
        $form->setMaxSize('number', 4);

        if(!is_null($this->existingApplication)){
            $form->setValue('number', substr($this->existingApplication->getCellPhone(), 6));
        }

        $form->addCheck('do_not_call', 1);
        if(!is_null($this->existingApplication) && is_null($this->existingApplication->getCellPhone())){
            $form->setMatch('do_not_call', 1);
        }

        // This is just getting worse and worse.
        // TODO: this, correctly.
        $sem = Term::getTermSem($this->term);
        if($sem == TERM_SPRING || $sem == TERM_FALL) {

            /*************
             * Lifestyle *
            *************/
            // TODO: get rid of the magic numbers!!!
            $form->addDropBox('lifestyle_option', array('1'=>_('Single Gender Building'),
            '2'=>_('Co-Ed Building')));
            if(!is_null($this->existingApplication)){
                $form->setMatch('lifestyle_option',$this->existingApplication->getLifestyleOption());
            }else{
                $form->setMatch('lifestyle_option', '1');
            }

            /************
             * Bed time *
            ************/
            // TODO: magic numbers
            $form->addDropBox('preferred_bedtime', array('1'=>_('Early'),
            '2'=>_('Late')));
            if(!is_null($this->existingApplication)){
                $form->setMatch('preferred_bedtime',$this->existingApplication->getPreferredBedtime());
            }else{
                $form->setMatch('preferred_bedtime', '1');
            }

            /******************
             * Room condition *
            ******************/
            //TODO: magic numbers
            $form->addDropBox('room_condition', array('1'=>_('Neat'),
            '2'=>_('Cluttered')));
            if(!is_null($this->existingApplication)){
                $form->setMatch('room_condition',$this->existingApplication->getRoomCondition());
            }else{
                $form->setMatch('room_condition', '1');
            }

        } else if($sem == TERM_SUMMER1 || $sem == TERM_SUMMER2) {
            /* Private room option for Summer terms */
            $form->addDropBox('room_type', array(ROOM_TYPE_DOUBLE=>'Two person', ROOM_TYPE_PRIVATE=>'Private (if available)'));

            if(!is_null($this->existingApplication)) {
                $form->setMatch('room_type', $this->existingApplication->getRoomType());
            } else {
                $form->setMatch('room_type', '0');
            }
        }
        
        /***************
         * Meal Option *
        */
        if ($sem == TERM_FALL || $sem == TERM_SPRING) {
            if($this->student->getType() == TYPE_FRESHMEN){
                $mealOptions = array(BANNER_MEAL_STD=>'Standard', BANNER_MEAL_HIGH=>'High', BANNER_MEAL_SUPER=>'Super');
            } else {
                $mealOptions = array(BANNER_MEAL_LOW=>_('Low'), BANNER_MEAL_STD=>_('Standard'), BANNER_MEAL_HIGH=>_('High'), BANNER_MEAL_SUPER=>_('Super'));
            }
        } else if ($sem == TERM_SUMMER1 || $sem == TERM_SUMMER2) {
            $mealOptions = array(BANNER_MEAL_5WEEK=>'Summer 5-Week Plan');
        }

        $form->addDropBox('meal_option', $mealOptions);
        $form->setMatch('meal_option', BANNER_MEAL_STD);
        
        if(!is_null($this->existingApplication)){
            $form->setMatch('meal_option',$this->existingApplication->getMealPlan());
        }else{
            $form->setMatch('meal_option', BANNER_MEAL_STD);
        }

        /*********************
         * Emergency Contact *
        *********************/
        $form->addText('emergency_contact_name');
        $form->addText('emergency_contact_relationship');
        $form->addText('emergency_contact_phone');
        $form->addText('emergency_contact_email');
        $form->addTextArea('emergency_medical_condition');

        if(!is_null($this->existingApplication)){
            $form->setValue('emergency_contact_name', $this->existingApplication->getEmergencyContactName());
            $form->setValue('emergency_contact_relationship', $this->existingApplication->getEmergencyContactRelationship());
            $form->setValue('emergency_contact_phone', $this->existingApplication->getEmergencyContactPhone());
            $form->setValue('emergency_contact_email', $this->existingApplication->getEmergencyContactEmail());
            $form->setValue('emergency_medical_condition', $this->existingApplication->getEmergencyMedicalCondition());
        }

        /******************
         * Missing Person *
        ******************/

        $form->addText('missing_person_name');
        $form->addText('missing_person_relationship');
        $form->addText('missing_person_phone');
        $form->addText('missing_person_email');

        if(!is_null($this->existingApplication)){
            $form->setValue('missing_person_name', $this->existingApplication->getMissingPersonName());
            $form->setValue('missing_person_relationship', $this->existingApplication->getMissingPersonRelationship());
            $form->setValue('missing_person_phone', $this->existingApplication->getMissingPersonPhone());
            $form->setValue('missing_person_email', $this->existingApplication->getMissingPersonEmail());
        }

        /*****************
         * Special needs *
        *****************/
        $tpl['SPECIAL_NEEDS_TEXT'] = ''; // setting this template variable to anything causes the special needs text to be displayed
        $form->addCheck('special_need', array('special_need'));
        $form->setLabel('special_need', array('Yes, I require special needs housing.'));

        if(isset($this->existingApplication)){
            if((!is_null($this->existingApplication->physical_disability) && $this->existingApplication->physical_disability != "0") ||
                    (!is_null($this->existingApplication->psych_disability) && $this->existingApplication->psych_disability != "0") ||
                    (!is_null($this->existingApplication->medical_need) && $this->existingApplication->medical_need != "0") ||
                    (!is_null($this->existingApplication->gender_need) && $this->existingApplication->gender_need != "0")) {
                $form->setMatch('special_need', 'special_need');
            }
        }

        if(isset($_REQUEST['special_needs'])){
            $form->addHidden('special_needs', $_REQUEST['special_needs']);
        }

        /*******
         * RLC *
        *******/
        PHPWS_Core::initModClass('hms', 'applicationFeature/RlcApplication.php');
        $rlcReg = new RLCApplicationRegistration();
        if(HMS_RLC_Application::checkForApplication($this->student->getUsername(), $this->term) == TRUE){
            // Student has an RLC application on file already
            $tpl['RLC_SUBMITTED'] = '';
            $form->addHidden('rlc_interest', 0);
        }else if(ApplicationFeature::isEnabledForStudent($rlcReg, $this->term, $this->student)){
            // Feature is enabled, but student hasn't submitted one yet
            $form->addRadio('rlc_interest', array(0, 1));
            $form->setLabel('rlc_interest', array(_("No"), _("Yes")));
             
            if(!is_null($this->existingApplication) && !is_null($this->existingApplication->getRLCInterest())){
                $form->setMatch('rlc_interest', 'rlc_interest');
            }else{
                $form->setMatch('rlc_interest', '0');
            }
        }else{
            // Feature is not enabled
            $form->addHidden('rlc_interest', 0);
        }

        $form->addSubmit('submit', _('Continue'));
        $form->setExtra('submit', 'class="hms-application-submit-button"');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        Layout::addPageTitle("Housing Application Form");

        return PHPWS_Template::process($tpl,'hms','student/student_application.tpl');
    }
}

?>
