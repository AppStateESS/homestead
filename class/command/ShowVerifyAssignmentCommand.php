<?php

class ShowVerifyAssignmentCommand extends Command
{
    private $term;

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowVerifyAssignment', 'term' => $this->term);
        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'VerifyAssignmentView.php');

        $view = new VerifyAssignmentView($context->get('term'));
        $context->setContent($view->show());
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }
}
