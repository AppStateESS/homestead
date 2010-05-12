<?php

class GetMealPlanDropboxCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $options = array(2  => 'Low',
                         1  => 'Standard',
                         0  => 'High',
                         8  => 'Super',
                         -1 => 'None'
                        );
        $form = new PHPWS_Form('select_meal');
        $form->addSelect('plan', $options);

        echo implode($form->getTemplate());
        exit;
    }
}
?>
