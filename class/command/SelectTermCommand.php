<?php

/**
 * Marks a term as 'selected' in the user's session.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SelectTermCommand extends Command {

    public $term;

    function setTerm($term) {
        $this->term = $term;
    }

    function getRequestVars()
    {
        $vars = array('action' => 'SelectTerm');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'select_term')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do no have permission to select other terms.');
        }

        if(UserStatus::isGuest()) {
            $context->goBack();
        }

        if(!isset($this->term)) {
            $this->term = $context->get('term');
        }

        Term::setSelectedTerm($this->term);

        $context->goBack();
    }
}


