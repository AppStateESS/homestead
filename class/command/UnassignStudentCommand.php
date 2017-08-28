<?php

namespace Homestead\command;

use \Homestead\Command;

/**
 * Controller class for removing/un-assigning a student.
 *
 * @author jbooker
 * @package hms
 */
class UnassignStudentCommand extends Command {

    /**
     *
     * @return multitype:string
     */
    public function getRequestVars()
    {
        return array(
                'action' => 'UnassignStudent'
        );
    }

    /**
     *
     * @param CommandContext $context
     * @throws PermissionException
     */
    public function execute(CommandContext $context)
    {
        if (!UserStatus::isAdmin() || !\Current_User::allow('hms', 'assignment_maintenance')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to unassign students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'MealPlanFactory.php');
        PHPWS_Core::initModClass('hms', 'MealPlan.php');

        $username = $context->get('username');
        $unassignReason = $context->get('unassignment_type');

        $cmd = CommandFactory::getCommand('ShowUnassignStudent');
        // $cmd->setUsername($username);

        if (!isset($username) || is_null($username)) {
            \NQ::simple('hms', NotificationView::ERROR, 'Invalid or missing username.');
            $cmd->redirect();
        }

        // Make sure a valid reason was chosen
        if (!isset($unassignReason) || $unassignReason == -1) {
            \NQ::simple('hms', NotificationView::ERROR, 'Please choose a valid reason.');
            $cmd->setUsername($username);
            $cmd->redirect();
        }

        // Check refund percentage field
        $refund = $context->get('refund');

        // Is a required field
        if(!isset($refund) || $refund == '') {
            \NQ::simple('hms', NotificationView::ERROR, 'Please enter a refund percentage.');
            $cmd->redirect();
        }

        // Must be numeric
        if(!is_numeric($refund) || $refund < 0 || $refund > 100) {
            \NQ::simple('hms', NotificationView::ERROR, 'The refund percentage must be between 0 and 100 percent.');
            $cmd->redirect();
        }

        // Must be whole number
        if (is_float($refund)) {
            \NQ::simple('hms', NotificationView::ERROR, 'Only whole number refund percentages are supported, no decimal place is allowed.');
            $cmd->redirect();
        }


        $term = Term::getSelectedTerm();
        $student = StudentFactory::getStudentByUsername($username, $term);
        $notes = $context->get('note');

        try {
            HMS_Assignment::unassignStudent($student, $term, $notes, $unassignReason, $refund);
        } catch (\Exception $e) {
            \NQ::simple('hms', NotificationView::ERROR, 'Error: ' . $e->getMessage());
            $cmd->setUsername($username);
            $cmd->redirect();
        }

        \NQ::simple('hms', NotificationView::SUCCESS, 'Successfully unassigned ' . $student->getFullName());


        // Check for a meal plan, and remove it if it hasn't been sent yet
        $mealPlan = MealPlanFactory::getMealByBannerIdTerm($student->getBannerId(), $term);

        if($mealPlan !== null){
            if($mealPlan->getStatus() === MealPlan::STATUS_NEW){
                MealPlanFactory::removeMealPlan($mealPlan);
            } else {
                // Show a warning that we couldn't remove this meal plan
                \NQ::simple('hms', NotificationView::WARNING, 'This student has a meal plan which has already been sent to Banner, so we couldn\'t remove it.');
            }
        }

        $cmd->redirect();
    }
}
