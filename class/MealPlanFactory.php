<?php

PHPWS_Core::initModClass('hms', 'PdoFactory.php');
PHPWS_Core::initModClass('hms', 'MealPlan.php');
PHPWS_Core::initModClass('hms', 'MealPlanRestored.php');

/**
 * Factory for loading and saving MealPlan objects
 *
 * @author Jeremy Booker
 * @package Homestead
 */
class MealPlanFactory {


    /**
     * Creates a new MealPlan object based on a given HousingApplication.
     *
     * @param HousingApplication $application Housing Application to base this meal plan on (student id, term, and selected meal plan)
     * @return MealPlan
     */
    // public static function newMealPlanViaApplication(HousingApplication $application)
    // {
    //     $planCode = $housingApplication->getMealPlan();
    //
    //     // If the student selected the 'none' plan, then we're done here
    //     if($planCode === MealPlan::BANNER_MEAL_NONE){
    //         return null;
    //     }
    //
    //     return new MealPlan($application->getBannerId(), $application->getTerm(), $planCode);
    // }

    // public static function newMealPlanViaStudentTerm(Student $student, $term)
    // {
    //     // Check for a housing application for this student in this term
    //
    //     // If no housing application, student gets the standard meal plan
    //
    //     // If selected 'none' plan on application, then we're done
    //
    //
    // }

    /**
     * Returns a MealPlanRestored object from the database given a banner id and term.
     *
     * @param integer $bannerId
     * @param string $term
     * @return MealPlanRestored Required meal plan object, null if none exists
     * @throws InvalidArgumentException
     */
    public static function getMealByBannerIdTerm($bannerId, $term)
    {
        if(!isset($bannerId) || is_null($bannerId)){
            throw new \InvalidArgumentException('Missing banner id.');
        }

        if(!isset($term) || is_null($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $db = PdoFactory::getPdoInstance();

        $query = 'SELECT * FROM hms_meal_plan WHERE banner_id = :bannerId AND term = :term';

        $stmt = $db->prepare($query);

        $stmt->execute(array(
                    'bannerId' => $bannerId,
                    'term' => $term
                ));

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'MealPlanRestored');

        $result = $stmt->fetch();

        if($result === false){
            return null;
        }

        return $result;
    }

    /**
     * Returns all MealPlans that need to be reported over to Banner
     *
     * @param string $term
     * @return Array<MealPlanRestored> Array of all MealPlanRestored objects that need to be reported to Banner
     * @throws
     */
    public static function getMealPlansToBeReported($term)
    {
        if(!isset($term) || is_null($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $db = PdoFactory::getPdoInstance();

        $query = "SELECT * FROM hms_meal_plan WHERE term = :term AND status = :status";

        $stmt = $db->prepare($query);

        $stmt->execute(array('term' => $term,
                            'status' => MealPlan::STATUS_NEW));

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'MealPlanRestored');

        return $stmt->fetchAll();
    }

    public static function getQueueSize($term)
    {
        if(!isset($term) || is_null($term)){
            throw new \InvalidArgumentException('Missing term.');
        }

        $db = PdoFactory::getPdoInstance();

        $query = "SELECT count(*) FROM hms_meal_plan WHERE term = :term AND status = :status";

        $stmt = $db->prepare($query);

        $stmt->execute(array('term' => $term,
                            'status' => MealPlan::STATUS_NEW));

        $result = $stmt->fetch();
        
        return $result[0];
    }

    /**
     * Saves (creates or updates) a MealPlan object to the database.
     *
     * @param MealPlan $mealPlan
     */
    public static function saveMealPlan(MealPlan $mealPlan)
    {
        $db = PdoFactory::getPdoInstance();

        $id = $mealPlan->getId();

        $params = array('bannerId'        => $mealPlan->getBannerId(),
                        'term'            => $mealPlan->getTerm(),
                        'mealPlanCode'    => $mealPlan->getPlanCode(),
                        'status'          => $mealPlan->getStatus(),
                        'statusTimestamp' => $mealPlan->getStatusTimestamp()
                    );

        if($id === null){
            // Insert a new meal plan
            $query = "INSERT INTO hms_meal_plan VALUES (nextval('hms_meal_plan_seq'), :bannerId, :term, :mealPlanCode, :status, :statusTimestamp)";
        } else {
            // Update an existing meal plan
            $query = "UPDATE hms_meal_plan SET banner_id = :bannerId, term = :term, meal_plan_code = :mealPlanCode, status = :status, status_timestamp = :statusTimestamp WHERE id = :id";
            $params['id'] = $id;
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        // Update ID for a new object
        if ($id === null) {
            $mealPlan->setId($db->lastInsertId('hms_meal_plan_seq'));
        }
    }
}
