<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ShowRoommateConfirmationCommand extends Command
{
    private $roommateId;

    public function getRequestVars() {
        $vars = array('action' => 'ShowRoommateConfirmation');

        if(isset($this->roommateId)) {
            $vars['roommateId'] = $this->roommateId;
        }

        return $vars;
    }

    public function setRoommateId($id)
    {
        $this->roommateId = $id;
    }

    public function execute(CommandContext $context)
    {
    }
}

?>
