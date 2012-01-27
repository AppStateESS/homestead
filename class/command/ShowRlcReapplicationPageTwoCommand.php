<?php

class ShowRlcReapplicationPageTwoCommand extends Command {
    
    private $term;
    private $vars;
    
    public function setTerm($term){
        $this->term = $term;
    }
    
    public function setVars($vars){
        $this->vars = $vars;
    }
    
    public function getRequestVars()
    {
        $reqVars = $this->vars;
        unset($reqVars['module']);
        
        $reqVars['action'] = 'ShowRlcReapplicationPageTwo';
        $reqVars['term'] = $this->term;
        
        return $reqVars;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
        PHPWS_Core::initModClass('hms', 'RlcReapplicationPageTwoView.php');
        
        $rlcs = array(new HMS_Learning_Community($context->get('rlc_choice_1')));
        
        if($context->get('rlc_choice_2') != 'none'){
            $rlcs[] = new HMS_Learning_Community($context->get('rlc_choice_2'));
        }
        
        if($context->get('rlc_choice_3') != 'none'){
            $rlcs[] = new HMS_Learning_Community($context->get('rlc_choice_3'));
        }
        
        $view = new RlcReapplicationPageTwoView($rlcs, $context->get('term'));
        
        $context->setContent($view->show());
    }
}

?>