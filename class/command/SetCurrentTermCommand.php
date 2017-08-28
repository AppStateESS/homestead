<?php

namespace Homestead\command;

use \Homestead\Command;

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
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to set the Current Term.');
        }

        PHPWS_Core::initModClass('hms', 'TermEditView.php');

        $this->term = $context->get('term');

        Term::setCurrentTerm($this->term);

        \NQ::simple('hms', NotificationView::SUCCESS,
        'The Current Term has been set to ' .
        Term::getPrintableCurrentTerm());

        $cmd = CommandFactory::getCommand('ShowEditTerm');
        $cmd->redirect();
    }
}
