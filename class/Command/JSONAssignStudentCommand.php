<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\Term;
use \Homestead\StudentFactory;
use \Homestead\HMS_Assignment;
use \Homestead\HousingApplicationFactory;
use \Homestead\MealPlanFactory;
use \Homestead\Exception\PermissionException;
use \Homestead\Exception\AssignmentException;
use \Homestead\Exception\StudentNotFoundException;

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class JSONAssignStudentCommand
{

    public function getRequestVars()
    {
        return array('action' => 'JSONAssignStudent');
    }

    public function execute(CommandContext $context)
    {
        if (!UserStatus::isAdmin() || !\Current_User::allow('hms', 'assign_by_floor')) {
            throw new PermissionException('You do not have permission to assign students by floor.');
        }

        $username = $context->get('username');
        $banner_id = (int) $context->get('banner_id');
        $reason = $context->get('reason');
        $bed_id = $context->get('bed_id');
        $term = Term::getSelectedTerm();

        try {
            if ($banner_id) {
                $student = StudentFactory::getStudentByBannerID($banner_id, Term::getSelectedTerm());
            } elseif (!empty($username)) {
                $student = StudentFactory::getStudentByUsername($username, Term::getSelectedTerm());
            } else {
                $context->setContent(json_encode(array('status' => 'failure', 'message' => 'Did not receive Banner ID or user name.')));
                return;
            }
            try {
                HMS_Assignment::assignStudent($student, $term, null, $bed_id, null, null, $reason);

                // Setup a meal plan
                $application = HousingApplicationFactory::getAppByStudent($student, $term);

                $mealPlan = MealPlanFactory::getMealByBannerIdTerm($student->getBannerId(), $term);
                if($mealPlan === null){
                    $plan = MealPlanFactory::createPlan($student, $term, $application);
                    MealPlanFactory::saveMealPlan($plan);
                }
            } catch (AssignmentException $e) {
                $context->setContent(json_encode(array('status'=>'failure', 'message'=>$e->getMessage())));
                return;
            }
            $message = $student->first_name . ' ' . $student->last_name;

            $context->setContent(json_encode(array('status' => 'success', 'message'=>$message, 'student'=>$student)));
        } catch (\StudentNotFoundException $e) {
            $context->setContent(json_encode(array('status' => 'failure', 'message' => $e->getMessage())));
        }
    }

}
