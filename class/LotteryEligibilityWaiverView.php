<?php

PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');

class LotteryEligibilityWaiverView extends View {

    public function show(){
    	$form = new PHPWS_Form('waiver');
        $form->addTextArea('usernames');
        $form->setLabel('usernames', 'ASU User names (one per line):');

        javascript('modules/hms/autoFocus', array('ELEMENT' => $form->getId('usernames')));
        $form->addSubmit('submit_btn', 'Create');

        $cmd = CommandFactory::getCommand('CreateWaiver');
        $cmd->initForm($form);

        $tpl = array();

        $form->mergeTemplate($tpl);

        Layout::addPageTitle("Lottery Eligibility Waiver");

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/eligibility_waiver.tpl');
    }
}

?>
