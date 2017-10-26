<?php

namespace Homestead\Command;

use \Homestead\VerifyAssignmentView;

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
        $view = new VerifyAssignmentView($context->get('term'));
        $context->setContent($view->show());
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }
}
