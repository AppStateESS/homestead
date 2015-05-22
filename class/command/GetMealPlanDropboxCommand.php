<?php

class GetMealPlanDropboxCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $options = array(BANNER_MEAL_LOW   => 'Low',
                BANNER_MEAL_STD   => 'Standard',
                BANNER_MEAL_HIGH  => 'High',
                BANNER_MEAL_SUPER => 'Super',
                BANNER_MEAL_5WEEK => 'Summer (5 Weeks)',
                BANNER_MEAL_NONE  => 'None'
        );
        $form = new PHPWS_Form('select_meal');
        $form->addSelect('mealplan', $options);


        // If summer term, set default as Summer 5Week
        $term = Term::getSelectedTerm();
        if ( (strlen($term) >= 2) &&
                ( (substr($term, -2) == TERM_SUMMER1) || (substr($term, -2) == TERM_SUMMER2) ) ) {
            $form->setMatch('mealplan', BANNER_MEAL_5WEEK);
        } else {
            $form->setMatch('mealplan', BANNER_MEAL_STD);
        }
        $form->setClass('mealplan', 'form-control');

        $template = $form->getTemplate();
        echo \PHPWS_Template::process($template, 'hms', 'admin/get_meal_plan_dropbox.tpl');
        exit;
    }
}
?>
