<?php

class UploadTermsConditionsCommand extends Command {
	
	private $term;
	private $type;
    
    public function getRequestVars() {
        $vars = array('action' => 'ShowUploadTermsConditions');
        
        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }
        
        if(isset($this->type)) {
            $vars['type'] = $this->type;
        }
        
        return $vars;
    }
    
    public function setTerm($term) {
        $this->term = $term;
    }
    
    public function setType($type) {
        $this->type = $type;
    }
    
    public function execute(CommandContext $context) {
    }
}

?>