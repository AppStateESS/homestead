<?php

class RlcReapplicationPageTwoView extends View {
    
    private $rlcs;
    private $term;
    
    public function __construct(Array $rlcs, $term)
    {
        $this->rlcs = $rlcs;
        $this->term = $term;
    }
    
    public function show(){
        
        $tpl = array();
        $tpl['TERM'] = Term::toString($this->term);
        
        $form = new PHPWS_Form('rlc_reapp');
        $submitCmd = CommandFactory::getCommand('SubmitRLCReapplicationPage2');
        //$submitCmd->setTerm($this->term);
        $submitCmd->setVars($_REQUEST);
        $submitCmd->initForm($form);
        
        foreach($this->rlcs as $i=>$rlc){
            $question = $this->rlcs[$i]->getReturningQuestion();
            if(!isset($question)){
                continue;
            }
            $form->addTextArea("rlc_question_$i");
            $form->setLabel("rlc_question_$i", $this->rlcs[$i]->getReturningQuestion());
        }
        
        $form->addSubmit('submit', 'Submit Application');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        //TODO
        return PHPWS_Template::process($tpl,'hms', 'student/RlcReapplicationPage2.tpl');
    }
}

?>