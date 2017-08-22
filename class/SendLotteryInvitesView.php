<?php

namespace Homestead;

class SendLotteryInvitesView extends View {

    public function __construct()
    {

    }

    public function show()
    {
        $tpl = array();

        $submitCmd = CommandFactory::getCommand('SendLotteryInvites');

        $form = new \PHPWS_Form();
        $submitCmd->initForm($form);

        $form->addCheckAssoc('send_reminders', array('send_reminders'=>"Send Invite Reminders"));
        $form->setMatch('send_reminders', 'send_reminders');

        $form->addCheckAssoc('magic_flag', array('magic_flag'=>"Send 'Magic Winner' Invites"));
        $form->setMatch('magic_flag', 'magic_flag');

        $form->addText('sr_male', 0);
        $form->setLabel('sr_male', 'Male:');
        $form->setClass('sr_male', 'form-control');

        $form->addText('sr_female', 0);
        $form->setLabel('sr_female', 'Female:');
        $form->setClass('sr_female', 'form-control');

        $form->addText('jr_male', 0);
        $form->setLabel('jr_male', 'Male:');
        $form->setClass('jr_male', 'form-control');

        $form->addText('jr_female', 0);
        $form->setLabel('jr_female', 'Female:');
        $form->setClass('jr_female', 'form-control');

        $form->addText('soph_male', 0);
        $form->setLabel('soph_male', 'Male:');
        $form->setClass('soph_male', 'form-control');

        $form->addText('soph_female', 0);
        $form->setLabel('soph_female', 'Female:');
        $form->setClass('soph_female', 'form-control');

        $form->addSubmit('submit', 'Send Invites');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return \PHPWS_Template::process($tpl, 'hms', 'admin/SendLotteryInvitesView.tpl');
    }
}
