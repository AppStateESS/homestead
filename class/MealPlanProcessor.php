<?php

PHPWS_Core::initModClass('hms', 'MealPlanFactory.php');
PHPWS_Core::initModClass('hms', 'SOAP.php');

class MealPlanProcessor {

    public static function queueMealPlan(MealPlan $mealPlan, SOAP $soapClient)
    {
        // Start by saving the meal plan in whatever status it already has (probably 'new')
        // This will add it to the hms_meal_plan table, with the 'new' status (unless status was set otherwise)
        MealPlanFactory::saveMealPlan($mealPlan);

        // Get the term object for this meal plan
        $termObj = new Term($mealPlan->getTerm());

        // If the meal plan queue is enabled, then we're done here
        if($termObj->getMealPlanQueue() == 1){
            return;
        }

        // If the queue was not enabled, then we're ready to send via the web Service
        self::processMealPlan($mealPlan, $soapClient);
    }

    /**
     * Processes a single Meal Plan by sending it over the Web Service interface.
     *
     * @param MealPlan $mealPlan The Meal Plan object to process.
     * @param SOAP $soapClient A concrete instance of a sub-class of the SOAP class. See: SOAP::getInstance()
     * @return void
     * @throws SOAPException, BannerException
     */
    public static function processMealPlan(MealPlan $mealPlan, SOAP $soapClient)
    {
        // Use the SOAP Client to send the meal plan
        // If this fails, it'll throw an exception. We don't catch it here, so it
        // can be handeled by the calling method/interface.
        $soapClient->createMealPlan($mealPlan->getBannerId(), $mealPlan->getTerm(), $mealPlan->getPlanCode());

        // Update the meal plan's status and timestamp
        $mealPlan->setStatus(MealPlan::STATUS_SENT);
        $mealPlan->setStatusTimestamp(time());
        MealPlanFactory::saveMealPlan($mealPlan);
    }

    /**
     * Uses the MealPlanFactory to get all meal plans that have not yet been sent
     * for a given term and sends them.
     *
     * @param string $term The term to send meal plans for.
     * @return Array An array of meal plans that failed to send. Each element is a sub-array with the mealplan object and exception object.
     */
    public static function processMealPlansForTerm($term)
    {
        $mealPlans = MealPlanFactory::getMealPlansToBeReported($term);

        $failedPlans = array();

        foreach($mealPlans as $mealPlan) {
            try {
                self::processMealPlan($mealPlan);
            } catch(\Exception $e){
                // Add meal plan to the list of failed MealPlans
                $failedPlans[] = array('plan' => $mealPlan, 'exception' => $e);
            }
        }

        return $failedPlans;
    }
}
