<?php

class WaitingListSignupView extends View {
    
    private $term;
    
    public function __construct($term)
    {
        $this->term = $term;
    }
    
    public function show()
    {
        $tpl = array();
        $tpl['TERM'] = Term::toString($this->term);
        $tpl['NEXT_TERM'] = Term::toString(Term::getNextTerm($this->term));
        
        $form = new PHPWS_Form('waitinglist-signup');
        
        $submitCmd = CommandFactory::getCommand('WaitingListSignup');
        $submitCmd->initForm($form);
        
        $form->addSubmit('Sign up');
        $form->addHidden('term', $this->term);
        
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'student/waitinglistSignup.tpl');
    }
}
?>