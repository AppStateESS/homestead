<?php

class LotteryAdminEntryView extends View {

    public function __construct()
    {

    }

    public function show()
    {
        $submitCmd = CommandFactory::getCommand('LotteryAdminCreateApp');
        
        $form = new PHPWS_Form('admin_entry');
        $submitCmd->initForm($form);

        $form->addText('asu_username');
        $form->setLabel('asu_username', 'ASU Username');

        $form->addCheck('physical_disability');
        $form->setLabel('physical_disability', 'Physical Disability');

        $form->addCheck('psych_disability');
        $form->setLabel('psych_disability', 'Psychological Disability');

        $form->addCheck('medical_need');
        $form->setLabel('medical_need', 'Medical Need');

        $form->addCheck('gender_need');
        $form->setLabel('gender_need', 'Gender Need');

        $form->addSubmit('enter_into_lottery', 'Add to lottery');

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/add_to_lottery.tpl');
    }
}

?>