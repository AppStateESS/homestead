<?php

class ShowVerifyAssignmentCommand extends Command
{
    private $username;

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowVerifyAssignment', 'username' => $this->username);
        return $vars;

    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'VerifyAssignmentView.php');
        $view = new VerifyAssignmentView($context->get('username'));
        $context->setContent($view->show());
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }
}

