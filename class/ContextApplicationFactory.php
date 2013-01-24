<?php 

PHPWS_Core::initModClass('hms', 'FallApplication.php');
PHPWS_Core::initModClass('hms', 'SpringApplication.php');
PHPWS_Core::initModClass('hms', 'SummerApplication.php');
PHPWS_Core::initModClass('hms', 'LotteryApplication.php');

abstract class ContextApplicationFactory {

    protected $context;
    protected $app;

    public static function getApplicationFromContext(CommandContext $context, $term, Student $student, $applicationType)
    {
        $concreteFactory = null;

        switch($applicationType){
            case 'fall':
                $application = new RestoredFallApplication();
                $concreteFactory = new FallContextApplicationFactory($context, $application);
                break;
            case 'spring':
                $application = new RestoredSpringApplication();
                $concreteFactory = new SpringContextApplicationFactory($context, $application);
                break;
            case 'summer':
                $application = new RestoredSummerApplication();
                $concreteFactory = new SummerContextApplicationFactory($context, $application);
                break;
            case 'lottery':
                $application = new RestoredLotteryApplication();
                $concreteFactory = new LotteryContextApplicationFactory($context, $application);
                break;
        }

        $concreteFactory->populateSharedFields($term, $student);

        $concreteFactory->populateApplicationSpecificFields();

        return $application;
    }

    public function __construct(CommandContext $context, HousingApplication $application)
    {
        $this->context	= $context;
        $this->app		= $application;
    }

    public abstract function populateApplicationSpecificFields();

    private function populateSharedFields($term, Student $student)
    {
        $this->app->setTerm($term);
        $this->app->setBannerId($student->getBannerId());
        $this->app->setUsername($student->getUsername());
        $this->app->setGender($student->getGender());
        $this->app->setStudentType($student->getType());
        $this->app->setApplicationTerm($student->getApplicationTerm());

        $doNotCall  = $this->context->get('do_not_call');
        $areaCode 	= $this->context->get('area_code');
        $exchange 	= $this->context->get('exchange');
        $number		= $this->context->get('number');

        /* Phone Number */
        if(is_null($doNotCall)){
            //test('ohh hai',1);
            // do not call checkbox was not selected, so check the number
            if(is_null($areaCode) || is_null($exchange) || is_null($number)){
                throw new InvalidArgumentException('Please provide a cell-phone number or click the checkbox stating that you do not wish to share your number with us.');
            }
        }

        if(is_null($doNotCall)){
            $this->app->setCellPhone($areaCode . $exchange . $number);
        }else{
            $this->app->setCellPhone(null);
        }

        /* Meal Plan */
        $mealOption = $this->context->get('meal_option');
        if(!is_numeric($mealOption))
        {
            throw new InvalidArgumentException('Invalid option from context. Please try again.');
        }

        $this->app->setMealPlan($mealOption);

        /* Emergency Contact */
        $this->app->setEmergencyContactName($this->context->get('emergency_contact_name'));
        $this->app->setEmergencyContactRelationship($this->context->get('emergency_contact_relationship'));
        $this->app->setEmergencyContactPhone($this->context->get('emergency_contact_phone'));
        $this->app->setEmergencyContactEmail($this->context->get('emergency_contact_email'));

        /* Missing Persons */
        $this->app->setMissingPersonName($this->context->get('missing_person_name'));
        $this->app->setMissingPersonRelationship($this->context->get('missing_person_relationship'));
        $this->app->setMissingPersonPhone($this->context->get('missing_person_phone'));
        $this->app->setMissingPersonEmail($this->context->get('missing_person_email'));

        /* Medical Conditions */
        $this->app->setEmergencyMedicalCondition($this->context->get('emergency_medical_condition'));

        /* Special Needs */
        $specialNeeds = $this->context->get('special_needs');
        	
        isset($specialNeeds['physical_disability'])?$this->app->setPhysicalDisability(true):null;
        isset($specialNeeds['psych_disability'])?$this->app->setPsychDisability(true):null;
        isset($specialNeeds['gender_need'])?$this->app->setGenderNeed(true):null;
        isset($specialNeeds['medical_need'])?$this->app->setMedicalNeed(true):null;

        if($student->isInternational()){
            $this->app->setInternational(true);
        }else{
            $this->app->setInternational(false);
        }
    }
}

class FallContextApplicationFactory extends ContextApplicationFactory {

    public function populateApplicationSpecificFields()
    {
        $lifestyleOption	= $this->context->get('lifestyle_option');
        $preferredBedtime	= $this->context->get('preferred_bedtime');
        $roomCondition		= $this->context->get('room_condition');

        if(!is_numeric($lifestyleOption) || !is_numeric($preferredBedtime) || !is_numeric($roomCondition)){
            throw new InvalidArgumentException('Invalid option from context. Please try again.');
        }

        // Load the fall-specific fields
        $this->app->setLifestyleOption($lifestyleOption);
        $this->app->setPreferredBedtime($preferredBedtime);
        $this->app->setRoomCondition($roomCondition);

        $rlcInterest = $this->context->get('rlc_interest');
        if(isset($rlcInterest)){
            $this->app->setRlcInterest($this->context->get('rlc_interest'));
        }else{
            $this->app->setRlcInterest(0);
        }

        $this->app->setApplicationType('fall');
    }
}

class SpringContextApplicationFactory extends ContextApplicationFactory {

    public function populateApplicationSpecificFields()
    {
        $this->app->setApplicationType('spring');
        $this->app->setLifestyleOption($this->context->get('lifestyle_option'));
        $this->app->setPreferredBedtime($this->context->get('preferred_bedtime'));
        $this->app->setRoomCondition($this->context->get('room_condition'));
    }
}

class SummerContextApplicationFactory extends ContextApplicationFactory {

    public function populateApplicationSpecificFields()
    {
        $this->app->setApplicationType('summer');
        //TODO single/double room
    }
}

class LotteryContextApplicationFactory extends ContextApplicationFactory {

    public function populateApplicationSpecificFields()
    {
        //TODO lottery stuff here
        $this->app->setApplicationType('lottery');
    }
}
?>