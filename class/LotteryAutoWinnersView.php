<?php

class LotteryAutoWinnersView extends View {
    
    public function show() {
        
        $submitCmd = CommandFactory::getCommand('LotteryAdminSetWinner');
        
        $form = new PHPWS_Form('magic_form');
        $submitCmd->initForm($form);

        $form->addText('asu_username');
        $form->setLabel('asu_username', 'Banner ID Or User name: ');

        $form->addCheck('magic', array('enabled'));
        $form->setLabel('magic', array('Magic Flag: '));

        javascript('/jquery_ui/');
        javascript('/modules/hms/autoFocus', array('ELEMENT' => $form->getId('asu_username')));
        javascript('/modules/hms/new_autosuggest', array('ELEMENT' => $form->getId('asu_username')));
        Layout::addStyle('hms', 'css/autosuggest2.css');

        $form->addSubmit('Submit');

        Layout::addPageTitle("Automatic Lottery Winners");
        
        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/lotteryAutoWinnersView.tpl');
    }
}