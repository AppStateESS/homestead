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
                         BANNER_MEAL_NONE  => 'None'
                        );
        $form = new PHPWS_Form('select_meal');
        $form->addSelect('plan', $options);
        $form->setMatch('plan', BANNER_MEAL_STD);

        echo implode($form->getTemplate());
        exit;
    }
}
?>
