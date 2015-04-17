<?php

class HousingApplicationCancelView extends hms\View {
    
    private $application;
    private $student;
    private $assignment;
    
    public function __construct(Student $student, HousingApplication $application, HMS_Assignment $assignment = null){
        $this->application = $application;
        $this->student = $student;
        $this->assignment = $assignment;
    }
    
    public function show()
    {
        $tpl = array();
        
        $tpl['NAME'] = $this->student->getName();
        $tpl['TERM'] = Term::toString($this->application->getTerm());
        
        if(isset($this->assignment)){
            $tpl['ASSIGNMENT'] = $this->assignment->where_am_i();
        }else{
            $tpl['NO_ASSIGNMENT'] = ""; // dummy tag
        }

        $form = new PHPWS_Form('cancel_app_form');
        
        $submitCmd = CommandFactory::getCommand('CancelHousingApplication');
        $submitCmd->initForm($form);
        
        $reasons = array_merge(array(-1=>'Select...'), HousingApplication::getCancellationReasons());
        
        $form->addDropBox('cancel_reason', $reasons);
        $form->setLabel('cancel_reason', 'Reason');
        
        $form->addHidden('applicationId', $this->application->getId());
        $form->addHidden('term', $this->application->getTerm());
        
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/housingApplicationCancelView.tpl');
    }
}

?>