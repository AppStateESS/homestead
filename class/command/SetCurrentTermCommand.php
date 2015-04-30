<?php

class SetCurrentTermCommand extends Command {
    private $term;

    function setTerm($term) {
        $this->term = $term;
    }

    function getRequestVars() {
        $vars = array('action' => 'SetCurrentTerm');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    function execute(CommandContext $context) {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'activate_term')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to set the Current Term.');
        }

        PHPWS_Core::initModClass('hms', 'TermEditView.php');

        $this->term = $context->get('term');

        Term::setCurrentTerm($this->term);

        NQ::simple('hms', hms\NotificationView::SUCCESS,
        'The Current Term has been set to ' .
        Term::getPrintableCurrentTerm());

        $cmd = CommandFactory::getCommand('ShowEditTerm');
        $cmd->redirect();
    }
}

?>