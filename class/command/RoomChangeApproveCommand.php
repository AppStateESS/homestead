<?php

class RoomChangeApproveCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'RoomChangeApproveCommand');
    }

    public function execute(CommandContext $context)
    {
        test('got here',1);
    }
}

?>