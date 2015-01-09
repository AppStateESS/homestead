<?php

class ShowTermsAgreementCommand extends Command {

    private $term;
    private $agreedCommand;

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setAgreedCommand(Command $cmd){
        $this->agreedCommand = $cmd;
    }

    public function getRequestVars()
    {
        $vars = array('action'=>'ShowTermsAgreement', 'term'=>$this->term);

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

        $term = $context->get('term');

        // Recreate the agreedToCommand
        $agreedCmd = CommandFactory::getCommand($context->get('onAgreeAction'));
        $agreedCmd->setTerm($term);

        //$submitCmd = CommandFactory::getCommand('AgreeToTerms');
        //$submitCmd->setTerm($term);
        //$submitCmd->setAgreedCmd($agreedCmd);
        
        $docusignCmd = CommandFactory::getCommand('BeginDocusign');
        $docusignCmd->setTerm($term);
        $docusignCmd->setReturnCmd($agreedCmd);
        

        PHPWS_Core::initModClass('hms', 'TermsAgreementView.php');
        $agreementView = new TermsAgreementView($term, $docusignCmd);

        $context->setContent($agreementView->show());
    }
}

?>
