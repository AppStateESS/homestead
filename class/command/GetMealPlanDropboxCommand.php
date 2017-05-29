<?php

PHPWS_Core::initModClass('hms', 'MealPlan.php');

class GetMealPlanDropboxCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $options = array(MealPlan::BANNER_MEAL_LOW   => 'Low',
                         MealPlan::BANNER_MEAL_STD   => 'Standard',
                         MealPlan::BANNER_MEAL_HIGH  => 'High',
                         MealPlan::BANNER_MEAL_SUPER => 'Super',
                         MealPlan::BANNER_MEAL_SUMMER => 'Summer (5 Weeks)',
                         MealPlan::BANNER_MEAL_NONE  => 'None'
        );
        $form = new PHPWS_Form('select_meal');
        $form->addSelect('mealplan', $options);


        // If summer term, set default as Summer 5Week
        $term = Term::getSelectedTerm();
        if ( (strlen($term) >= 2) &&
                ( (substr($term, -2) == TERM_SUMMER1) || (substr($term, -2) == TERM_SUMMER2) ) ) {
            $form->setMatch('mealplan', MealPlan::BANNER_MEAL_SUMMER);
        } else {
            $form->setMatch('mealplan', MealPlan::BANNER_MEAL_STD);
        }
        $form->setClass('mealplan', 'form-control');

        $template = $form->getTemplate();
        echo \PHPWS_Template::process($template, 'hms', 'admin/get_meal_plan_dropbox.tpl');
        exit;
    }
}
