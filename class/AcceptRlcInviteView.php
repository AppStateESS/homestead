<?php

class AcceptRlcInviteView extends View {
    
    private $rlcApplication;
    private $rlcAssignment;
    private $term;
    
    public function __construct(HMS_RLC_Application $application, HMS_RLC_Assignment $assignment, $term){
        $this->rlcApplication = $application;
        $this->rlcAssignment = $assignment;
        $this->term = $term;
    }
    
    public function show()
    {
        $tpl = array();
        
        $tpl['COMMUNITY_NAME'] = $this->rlcAssignment->getRlcName();
        $tpl['TERM'] = Term::toString($this->term);
        
        $form = new PHPWS_Form();
        
        $submitCmd = CommandFactory::getCommand('AcceptDeclineRlcInvite');
        $submitCmd->initForm($form);
        
        $form->addHidden('term', $this->term);
        
        $form->addCheck('terms_cond', array('true'));
        $form->setLabel('terms_cond', array('I agree to the terms and conditions for this learning community.'));
        
        $form->addRadioAssoc('acceptance',array('accept'=>'Accept this Invitation', 'decline'=>'Decline this invitiation'));
        
        $form->addSubmit('submit', 'Submit');
        
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'student/acceptRlcInviteView.tpl');
    }
}

?>