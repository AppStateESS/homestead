<?php

class GetAssignmentTypeDropboxCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        $options = array(
        	ASSIGN_ADMIN		=> 'Administrative',
        	ASSIGN_LOTTERY		=> 'Lottery',
        	ASSIGN_FRESHMEN		=> 'Freshmen',
        	ASSIGN_MEDICAL		=> 'Medical',
        	ASSIGN_ATHLETICS	=> 'Athletics',
        	ASSIGN_HONORS		=> 'Honors',
        	ASSIGN_WATAUGA		=> 'Watauga Global',
        	ASSIGN_TEACHING		=> 'Teaching Fellows',
        	ASSIGN_RLC			=> 'RLC',
        	ASSIGN_SORORITY		=> 'Sorority',
        	ASSIGN_SPECIAL		=> 'Special Needs',
        	ASSIGN_AUTO			=> 'Auto-assigned');
        $form = new PHPWS_Form('select_assignment');
        $form->addSelect('assignment_type', $options);
        $form->setMatch('assignment_type', ASSIGN_ADMIN);

        echo implode($form->getTemplate());
        exit;
    }
}
?>
