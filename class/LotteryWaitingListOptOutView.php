<?php

class LotteryWaitingListOptOutView extends View {
    
    private $term;
    private $student;

    public function __construct(Student $student, $term)
    {
        $this->term = $term;
        $this->student = $student;
    }
    
    public function show()
    {
        $tpl = array();
        
        $tpl['TERM'] = Term::toString($this->term);
        
        $submitCmd = CommandFactory::getCommand('LotteryOptOut');
        
        $form = new PHPWS_Form;
        $submitCmd->initForm($form);
        
        PHPWS_Core::initCoreClass('Captcha.php');
        $form->addTplTag('CAPTCHA_IMAGE', Captcha::get());
        $form->addSubmit('submit', 'Opt-out of waiting list');
        
        $form->mergeTemplate($tpl);
        
        return PHPWS_Template::process($form->getTemplate(), 'hms', 'student/lotteryWaitingListOptOut.tpl');
    }
}


?>