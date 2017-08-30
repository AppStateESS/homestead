<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\CommandFactory;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;

class SetCurrentTermCommand extends Command {
    private $term;

    public function setTerm($term) {
        $this->term = $term;
    }

    public function getRequestVars() {
        $vars = array('action' => 'SetCurrentTerm');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    public function execute(CommandContext $context) {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'activate_term')) {
            throw new PermissionException('You do not have permission to set the Current Term.');
        }

        $this->term = $context->get('term');

        Term::setCurrentTerm($this->term);

        \NQ::simple('hms', NotificationView::SUCCESS,
        'The Current Term has been set to ' .
        Term::getPrintableCurrentTerm());

        $cmd = CommandFactory::getCommand('ShowEditTerm');
        $cmd->redirect();
    }
}
