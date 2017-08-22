<?php

namespace Homestead\command;

use \Homestead\Command;

class EnableMealPlanQueueCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'EnableMealPlanQueue');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'banner_queue')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to enable/disable the Banner queues.');
        }

        $term = new Term(Term::getSelectedTerm());

        $term->setMealPlanQueue(1);
        $term->save();

        NQ::Simple('hms', hms\NotificationView::SUCCESS, 'Meal Plan Queue has been enabled for ' . Term::toString($term->term) . '.');

        $cmd = CommandFactory::getCommand('ShowEditTerm');
        $cmd->redirect();
    }
}
