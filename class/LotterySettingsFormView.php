<?php

namespace Homestead;

class LotterySettingsFormView extends View {

    public function show()
    {
        $tpl = array();

        $form = new \PHPWS_Form();

        $submitCmd = CommandFactory::getCommand('LotterySettingsSubmit');
        $submitCmd->initForm($form);

        $form->addDropBox('lottery_term', Term::getTermsAssoc());
        $form->setMatch('lottery_term', PHPWS_Settings::get('hms', 'lottery_term'));
        $form->setLabel('lottery_term', 'Lottery Term');
        $form->setClass('lottery_term', 'form-control');

        $form->addText('hard_cap');
        $form->setLabel('hard_cap', 'Max # Returning Students (hard cap):');
        $form->setValue('hard_cap', PHPWS_Settings::get('hms', 'lottery_hard_cap'));
        $form->setClass('hard_cap', 'form-control');

        /*
        $form->addText('soph_goal');
        $form->setLabel('soph_goal', 'Sophomores:');
        $form->setValue('soph_goal', PHPWS_Settings::get('hms', 'lottery_soph_goal'));
        */

        $form->addText('jr_goal');
        $form->setLabel('jr_goal', 'Juniors:');
        $form->setValue('jr_goal', PHPWS_Settings::get('hms', 'lottery_jr_goal'));
        $form->setClass('jr_goal', 'form-control');

        $form->addText('sr_goal');
        $form->setLabel('sr_goal', 'Senior:');
        $form->setValue('sr_goal', PHPWS_Settings::get('hms', 'lottery_sr_goal'));
        $form->setClass('sr_goal', 'form-control');

        $form->addSubmit('submit', 'Save');

        $form->mergeTemplate($tpl);

        Layout::addPageTitle("Lottery Settings");

        return \PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/lottery_settings.tpl');
    }
}
