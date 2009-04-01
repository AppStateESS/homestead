<?php

class SpecialNeedsUI {

    private $action;

    function __construct($action){
        $this->action = $action;
    }

    public function show(){

        PHPWS_Core::initCoreClass('Form.php');
        $form = &new PHPWS_Form();

        $form->addCheck('special_needs', array('physical_disability','psych_disability','medical_need','gender_need'));
        $form->setLabel('special_needs', array('Physical disability', 'Psychological disability', 'Medical need', 'Transgender housing'));

        if(isset($_REQUEST['special_needs'])){
            $form->setMatch('special_needs', $_REQUEST['special_needs']);
        }

        # Carry over all the fields submitted on the first page of the application
        foreach($_POST as $key=>$value){
            if($key == 'module' || $key == 'type' || $key == 'op')
                continue;

            $form->addHidden($key, $value);
        }

        $form->addHidden('module', 'hms');
        $form->addHidden('type','student');
        $form->addHidden('op', $this->action);
        
        $form->addSubmit('submit', 'Continue');

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/special_needs.tpl');       
    }
}

?>
