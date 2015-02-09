<?php

class LotteryAutoWinnersView extends homestead\View {

    public function show() {

        $submitCmd = CommandFactory::getCommand('LotteryAdminSetWinner');

        $form = new PHPWS_Form('magic_form');
        $submitCmd->initForm($form);

        $form->addTextArea('banner_ids');

        $form->addSubmit('Submit');

        Layout::addPageTitle("Automatic Lottery Winners");

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/lotteryAutoWinnersView.tpl');
    }
}