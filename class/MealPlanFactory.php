<?php

namespace Homestead;
PHPWS_Core::initModClass('hms', 'PdoFactory.php');
PHPWS_Core::initModClass('hms', 'MealPlan.php');
PHPWS_Core::initModClass('hms', 'MealPlanRestored.php');
PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
PHPWS_Core::initModClass('hms', 'HousingApplication.php');

/**
 * Factory for loading and saving MealPlan objects
 *
 * @author Jeremy Booker
 * @package Homestead
 */
class MealPlanFactory {




    /**
     * Creates a meal plan given a Student, term, and (optionally) a Housing Application.
     * If no HousingApplication is given, the student gets the Standard level meal plan.
     * Summer terms always use the Summer meal plan. If the student selected "none" meal option,
     * the we return null.
     *
     * NB: Does not check to see if a student has an existing plan. This method always makes a new MealPlan object.
     *
     * @param Student $student
     * @param int $term
     * @param HousingApplication|null $application
     * @return MealPlan|null
     */
    public static function createPlan(Student $student, $term, HousingApplication $application = null, HMS_Residence_Hall $hall = null)
    {
        if($application === null){
            $planCode = MealPlan::BANNER_MEAL_STD;
        } else {
            $planCode = $application->getMealPlan();
        }

        // If the term is summer 1 or summer 2, then we always use the summer plan
        $semester = Term::getTermSem($term);
        if($semester == TERM_SUMMER1 || $semester == TERM_SUMMER2){
            $planCode = MealPlan::BANNER_MEAL_SUMMER;
        }

        // If the student selected the 'none' plan, make sure that's allowed by the residence hall
        if($planCode === MealPlan::BANNER_MEAL_NONE){
            // If we have a ResidenceHall parameter, check its meal plan setting
            if($hall !== null){
                if($hall->mealPlanRequired() === 1){
                    // Meal plan is required, so use standard plan
                    $planCode = MealPlan::BANNER_MEAL_STD;
                } else {
                    // Meal plan is optional and 'none' was requested, so we're done here
                    return null;
                }
            }

            // We didn't have a hall param, so we can't check required status.
            return null;
        }

        // Make a new MealPlan object and return it
        return new MealPlan($student->getBannerId(), $term, $planCode);
    }

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

        // 'meal_plan_code' is a char(2) field, so it always comes out as two characters. Trim it.
        $result->meal_plan_code = trim($result->meal_plan_code);

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

    public static function removeMealPlan(MealPlan $mealPlan)
    {
        $db = PdoFactory::getPdoInstance();

        $planId = $mealPlan->getId();

        if($planId === null || !isset($planId)){
            throw new \InvalidArgumentException('Attempting to delete a MealPlan without a valid id.');
        }

        $query = "DELETE FROM hms_meal_plan WHERE id = :id and banner_id = :bannerId AND term = :term";

        $params = array('id'        => $planId,
                        'bannerId'  => $mealPlan->getBannerId(),
                        'term'      => $mealPlan->getTerm()
                    );
        $stmt = $db->prepare($query);
        $stmt->execute($params);
    }
}
