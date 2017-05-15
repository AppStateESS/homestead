<?php

/**
 * Class to represent a meal plan assignment for a given student.
 *
 * @author Jeremy Booker
 * @package Homestead;
 */
class MealPlan {

    private $id;
    private $bannerId;
    private $planCode;
    private $status;
    private $statusTimestamp;


    // Banner Meal Plan Codes (???)
    const BANNER_MEAL_LOW   = '2';
    const BANNER_MEAL_STD   = '1';
    const BANNER_MEAL_HIGH  = '0';
    const BANNER_MEAL_SUPER = '8';
    const BANNER_MEAL_NONE  = '-1';

    const BANNER_MEAL_5WEEK = 'S5';


    /**
     * Constructor - Creates a new MealPlan object.
     * NB: Does not save or process the MealPlan. See MealPlanFactory::save()
     * and MealPlanProcessor::processMealPlan()
     *
     * @param string $bannerId Banner ID for the student this meal plan belongs to.
     * @param string $planCode Meal plan code. Must be one of the constants defined above. Two chars max.
     */
    public function __construct($bannerId, $planCode)
    {
        $this->id = null;
        $this->bannerId = $bannerId;
        $this->status = 'new';
        $this->statusTimestamp = time();
    }

    public function getId(){
        return $this->id;
    }

    public function getBannerId(){
        return $this->bannerId;
    }

    public function getPlanCode(){
        return $this->planCode;
    }

    public function getStatus(){
        return $this->status;
    }

    public function getStatusTimestamp(){
        return $this->statusTimestamp;
    }
}
