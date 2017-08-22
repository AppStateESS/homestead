<?php

namespace Homestead;

/**
 * SendRlcInvitesView
 *
 * Shows the view for sending RLC invites.
 *
 * @author jbooker
 * @package HMS
 */
class SendRlcInvitesView extends View {

    public function show()
    {
        $tpl = array();

        $submitCmd = CommandFactory::getCommand('SendRlcInvites');

        $form = new \PHPWS_Form();
        $submitCmd->initForm($form);

        $tpl['RESPOND_BY_DATE'] = javascript('datepicker', array('name'=>'respond_by_date', 'id'=>'respond_by_date'));
        $tpl['TERM'] = Term::toString(Term::getSelectedTerm());

        PHPWS_Core::initModClass('hms', 'HMS_Util.php');
        $form->addDropBox('time', HMS_Util::get_hours());
        $form->setMatch('time', '17');
        $form->setClass('time', 'form-control');

        $form->addRadioAssoc('type', array('freshmen'=>'Freshmen', 'returning'=>'Continuing'));

        $form->addSubmit('submit', 'Send Invites');
        $form->setClass('submit', 'btn btn-primary');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        return \PHPWS_Template::process($tpl, 'hms', 'admin/sendRlcInvites.tpl');
    }
}
