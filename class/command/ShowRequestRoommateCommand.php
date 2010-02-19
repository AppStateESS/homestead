<?php

/**
 * Compatibility layer for old Roommate code.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ShowRequestRoommateCommand extends Command {

    private $term;

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowRequestRoommate');

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isUser()) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to request a roommate.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
        $context->setContent(HMS_Roommate::show_request_roommate(NULL, $context->get('term')));
    }
}

?>
