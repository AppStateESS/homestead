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
     * Returns a MealPlanRestored object from the database given a banner id and term.
     *
     * @param integer $bannerId
     * @param string $term
     * @return MealPlanRestored Required meal plan object
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

        return $stmt->fetch();
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

        $query = "SELECT * FROM hms_meal_plan WHERE term = :term AND status = 'new'";

        $stmt = $db->prepare($query);

        $stmt->execute(array('term' => $term));

        $stmt->setFetchMode(PDO::FETCH_CLASS, 'MealPlanRestored');

        return $stmt->fetchAll();
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

        if($is !== null){
            // Update an existing meal plan
            $query = "UPDATE hms_meal_plan SET banner_id = :bannerId, term = :term, meal_plan_code = :mealPlanCode, status = :status, status_timestamp = :statusTimestamp)";
        } else {
            // Insert a new meal plan
            $query = "INSERT INTO hms_meal_plan VALUES ((select nextval('hms_meal_plan_seq')), :bannerId, :term, :mealPlanCode, :status, :statusTimestamp)";
        }

        $params = array('bannerId'        => $mealPlan->getBannerId(),
                        'term'            => $mealPlan->getTerm(),
                        'mealPlanCode'    => $mealPlan->getMealPlan(),
                        'status'          => $mealPlan->getStatus(),
                        'statusTimestamp' => $mealPlan->getStatusTimestamp()
                    );
        $stmt = $db->prepare($query);
        $stmt->execute($params);
    }
}
