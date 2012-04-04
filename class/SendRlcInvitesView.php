<?php

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
        
        $form = new PHPWS_Form();
        $submitCmd->initForm($form);

        $tpl['RESPOND_BY_DATE'] = javascript('datepicker', array('name'=>'respond_by_date', 'id'=>'respond_by_date'));
        $tpl['TERM'] = Term::toString(Term::getSelectedTerm());
        
        $form->addSubmit('submit', 'Send Invites');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/sendRlcInvites.tpl');
    }
}

?>