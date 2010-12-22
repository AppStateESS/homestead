<?php

class LotterySettingsFormView extends View {

    public function show()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');

        $tpl = array();

        $form = new PHPWS_Form();

        $submitCmd = CommandFactory::getCommand('LotterySettingsSubmit');
        $submitCmd->initForm($form);

        $form->addDropBox('lottery_term', Term::getTermsAssoc());
        $form->setMatch('lottery_term', PHPWS_Settings::get('hms', 'lottery_term'));

        //test(PHPWS_Settings::get('hms', 'lottery_term'),1);

        $form->addRadio('phase_radio', array('single_phase', 'multi_phase'));
        $form->setMatch('phase_radio', PHPWS_Settings::get('hms', 'lottery_type'));
        $form->setLabel('phase_radio', array('Single phase', 'Multi-phase'));
        $form->setExtra('phase_radio', 'class="lotterystate"');

        # Percent invites per class for single phase lottery
        $form->addText('lottery_per_soph', PHPWS_Settings::get('hms', 'lottery_per_soph'));
        $form->setSize('lottery_per_soph', 2, 3);
        $form->setExtra('lottery_per_soph', 'class="single_phase"');

        $form->addText('lottery_per_jr', PHPWS_Settings::get('hms', 'lottery_per_jr'));
        $form->setSize('lottery_per_jr', 2, 3);
        $form->setExtra('lottery_per_jr', 'class="single_phase"');

        $form->addText('lottery_per_senior', PHPWS_Settings::get('hms', 'lottery_per_senior'));
        $form->setSize('lottery_per_senior', 2, 3);
        $form->setExtra('lottery_per_senior', 'class="single_phase"');

        # Absolute max invites to send per class for multi-phase lottery
        $form->addText('lottery_max_soph', PHPWS_Settings::get('hms', 'lottery_max_soph'));
        $form->setSize('lottery_max_soph', 2, 4);
        $form->setExtra('lottery_max_soph', 'class="multi_phase"');

        $form->addText('lottery_max_jr', PHPWS_Settings::get('hms', 'lottery_max_jr'));
        $form->setSize('lottery_max_jr', 2, 4);
        $form->setExtra('lottery_max_jr', 'class="multi_phase"');

        $form->addText('lottery_max_senior', PHPWS_Settings::get('hms', 'lottery_max_senior'));
        $form->setSize('lottery_max_senior', 2, 4);
        $form->setExtra('lottery_max_senior', 'class="multi_phase"');

        # Set the initial enabled/disabled state
        $type = PHPWS_Settings::get('hms', 'lottery_type');
        if(isset($type) && $type == 'single_phase'){
            $form->setDisabled('lottery_max_soph');
            $form->setDisabled('lottery_max_jr');
            $form->setDisabled('lottery_max_senior');
        }else{
            $form->setDisabled('lottery_per_soph');
            $form->setDisabled('lottery_per_jr');
            $form->setDisabled('lottery_per_senior');
        }

        $max_soph = PHPWS_Settings::get('hms', 'lottery_max_soph');
        $max_jr   = PHPWS_Settings::get('hms', 'lottery_max_jr');
        $max_sr   = PHPWS_Settings::get('hms', 'lottery_max_sr');

        $form->addSubmit('submit');

        $form->mergeTemplate($tpl);

        Layout::addPageTitle("Lottery Settings");

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/lottery_settings.tpl');
    }
}


?>
