<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\Term;
use \Homestead\TermEditView;
use \Homestead\Exception\PermissionException;

class ShowEditTermCommand extends Command {

    public function getRequestVars() {
        $vars = array('action' => 'ShowEditTerm');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'edit_terms')) {
            throw new PermissionException('You do not have permission to edit terms.');
        }

        $term = new Term(Term::getSelectedTerm());

        $termView = new TermEditView($term);
        $context->setContent($termView->show());
    }
}
