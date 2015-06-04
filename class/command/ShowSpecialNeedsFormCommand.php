<?php

PHPWS_Core::initModClass('hms', 'SpecialNeedsFormView.php');

class ShowSpecialNeedsFormCommand extends Command {

    private $term;
    private $vars;
    private $onSubmitCmd;

    public function setTerm($term){
        $this->term = $term;
    }

    public function setVars($vars){
        $this->vars = $vars;
    }

    public function setOnSubmitCmd(Command $cmd){
        $this->onSubmitCmd = $cmd;
    }

    public function getRequestVars()
    {
        $reqVars = $this->vars;
        unset($reqVars['module']);

        $reqVars['action'] 	= 'ShowSpecialNeedsForm';
        $reqVars['term']	= $this->term;

        $submitVars = $this->onSubmitCmd->getRequestVars();
        $submitAction = $submitVars['action'];
        unset($submitVars['action']);
        $submitVars['onSubmit'] = $submitAction;

        return array_merge($reqVars, $submitVars);
    }

    public function execute(CommandContext $context)
    {
        $term = $context->get('term');

        $specialNeeds = $context->get('special_needs');

        $submitCmd = CommandFactory::getCommand($context->get('onSubmit'));
        $submitCmd->setTerm($term);
        $submitCmd->loadContext($context);

        $specialNeedsForm = new SpecialNeedsFormView($term, $specialNeeds, $submitCmd);
        $context->setContent($specialNeedsForm->show());
    }
}

