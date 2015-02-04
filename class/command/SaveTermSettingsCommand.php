<?php

class SaveTermSettingsCommand extends Command {
	
    public function getRequestVars()
    {
    	return array('action'=>'SaveTermSettings');
    }
    
    public function execute(CommandContext $context)
    {
    	$term = new Term(Term::getSelectedTerm());
        
        $term->setDocusignTemplate($context->get('template'));
        $term->setDocusignUnder18Template($context->get('under18_template'));
        
        $term->save();
        
        $cmd = CommandFactory::getCommand('ShowEditTerm');
        $cmd->redirect();
    }
}

?>