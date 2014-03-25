<?php

class RlcReapplicationPageTwoView extends hms\View{
    
    private $rlcs;
    private $term;
    private $reApp;
    
    public function __construct(Array $rlcs, $term, HMS_RLC_Application $reApp)
    {
        $this->rlcs = $rlcs;
        $this->term = $term;
        $this->reApp = $reApp;
    }
    
    public function show(){
        
        $tpl = array();
        $tpl['TERM'] = Term::toString($this->term);
        
        $form = new PHPWS_Form('rlc_reapp');
        $submitCmd = CommandFactory::getCommand('SubmitRLCReapplicationPage2');
        $submitCmd->setTerm($this->term);
        $submitCmd->initForm($form);

        foreach($this->rlcs as $i=>$rlc){
            $question = $this->rlcs[$i]->getReturningQuestion();
            if(!isset($question)){
                throw new Exception("Missing returning question for {$this->rlcs[$i]->get_community_name()}");
            }
            if(isset($this->reApp) && isset($this->reApp->{"rlc_question_$i"})){
                $form->addTextArea("rlc_question_$i", $this->reApp->{"rlc_question_$i"});
            }else{
                $form->addTextArea("rlc_question_$i");
            }
            
            $form->setLabel("rlc_question_$i", $this->rlcs[$i]->getReturningQuestion());
        }
        
        $form->addSubmit('submit', 'Submit Application');

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl,'hms', 'student/RlcReapplicationPage2.tpl');
    }
}

?>
