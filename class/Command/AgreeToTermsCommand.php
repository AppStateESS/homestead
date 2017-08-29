<?php

namespace Homestead\Command;

 use \Homestead\CommandFactory;

class AgreeToTermsCommand extends Command {

    private $term;
    private $agreedCommand;

    public function setTerm($term){
        $this->term = $term;
    }

    public function setAgreedCmd(Command $cmd){
        $this->agreedCommand = $cmd;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'AgreeToTerms', 'term'=>$this->term);

        if(!isset($this->agreedCommand)){
            return $vars;
        }

        // Get the action to do when someone agrees to the terms
        $onAgreeVars = $this->agreedCommand->getRequestVars();
        $onAgreeAction = $onAgreeVars['action'];

        // Unset it so it doesn't conlict
        unset($onAgreeVars['action']);

        // Reset it under a different name
        $onAgreeVars['onAgreeAction'] = $onAgreeAction;

        return array_merge($vars, $onAgreeVars);
    }

    public function execute(CommandContext $context)
    {
        $agreed = $context->get('agreedToTerms');

        if($agreed != 1){
            // TODO log the student out here
            exit;
        }

        // Recreate the onAgreeAction
        $action = $context->get('onAgreeAction');

        if(isset($action)){
            $cmd = CommandFactory::getCommand(($action));
            $cmd->setTerm($context->get('term'));
            $cmd->redirect();
        }else{
            throw new \InvalidArgumentException('No action set.');
        }
    }

}
