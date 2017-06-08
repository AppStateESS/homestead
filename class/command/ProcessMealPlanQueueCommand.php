<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');
PHPWS_Core::initModClass('hms', 'StudentFactory.php');

class ProcessMealPlanQueueCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ProcessMealPlanQueue');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'banner_queue')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to enable/disable the Banner queue.');
        }

        $term = new Term(Term::getSelectedTerm());

        if(is_null($term) || $term === ''){
            throw new InvalidArgumentException('Missing term.');
        }

        $cmd = CommandFactory::getCommand('ShowEditTerm');

        $queueStatus = $term->getMealPlanQueue();

        // Sanity check the queue status. If it's not enabled, then we can't do anything
        if($queueStatus !== 1){
            NQ::Simple('hms', hms\NotificationView::ERROR, 'The Meal Plan Queue is not enabled, so we can\'t process it.');
            $cmd->redirect();
        }

        // Get the set of meal plans to be reported
        $mealPlans = MealPlanFactory::getMealPlansToBeReported($term->term);

        // If there isn't anything in the queue, then we can just turn it off
        if(count($mealPlans) < 1){
            $term->setMealPlanQueue(0);
            $term->save();

            NQ::Simple('hms', hms\NotificationView::SUCCESS, 'The Meal Plan Queue has been disabled.');
            $cmd->redirect();
            return;
        }

        // Get a SOAP instance for reuse while sending each meal plan
        $soapClient = SOAP::getInstance(UserStatus::getUsername(), SOAP::ADMIN_USER);

        // Process the queue of meal plans, one item at a time
        // Catch exceptions and continue the loop if anything fails
        foreach($mealPlans as $plan){
            try {
                MealPlanProcessor::processMealPlan($plan, $soapClient);
            }catch(MealPlanExistsException $e){
                $plan->setStatus(MealPlan::STATUS_SENT);
                $plan->setStatusTimestamp(time());
                MealPlanFactory::saveMealPlan($plan);
            }

            $student = StudentFactory::getStudentByBannerId($plan->getBannerId(), $term->term);

            // Log that the meal plan was sent to Banner
            HMS_Activity_Log::log_activity($student->getUsername(), ACTIVITY_MEAL_PLAN_SENT, UserStatus::getUsername(), 'Meal Plan sent to Banner: ' . HMS_Util::formatMealOption($plan->getPlanCode()));
        }

        $term->setMealPlanQueue(0);
        $term->save();

        NQ::Simple('hms', hms\NotificationView::SUCCESS, 'Meal Plans were sent to Banner and the Meal Plan Queue has been disabled.');
        $cmd->redirect();
        return;
    }
}
