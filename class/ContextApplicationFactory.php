<?php

namespace Homestead;

use \Homestead\Command\CommandContext;

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
            /*case 'lottery':
                $application = new RestoredLotteryApplication(); //class does not exist
                $concreteFactory = new LotteryContextApplicationFactory($context, $application);
                break;*/
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
        /*
        if(is_null($doNotCall)){
            //test('ohh hai',1);
            // do not call checkbox was not selected, so check the number
            if(is_null($areaCode) || is_null($exchange) || is_null($number)){
                throw new \InvalidArgumentException('Please provide a cell-phone number or click the checkbox stating that you do not wish to share your number with us.');
            }
        }
        */

        if(is_null($doNotCall)){
            $this->app->setCellPhone($areaCode . $exchange . $number);
        }else{
            $this->app->setCellPhone(null);
        }

        /* Meal Plan */
        $mealOption = $this->context->get('meal_option');
        if(!isset($mealOption))
        {
            //throw new \InvalidArgumentException('Missing meal option from context.');
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

        if($student->isInternational()){
            $this->app->setInternational(true);
        }else{
            $this->app->setInternational(false);
        }
    }
}
