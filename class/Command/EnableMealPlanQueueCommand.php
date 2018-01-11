<?php

namespace Homestead\Command;

use \Homestead\CommandFactory;
use \Homestead\UserStatus;
use \Homestead\Term;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;

class EnableMealPlanQueueCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'EnableMealPlanQueue');
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'banner_queue')){
            throw new PermissionException('You do not have permission to enable/disable the Banner queues.');
        }

        $term = new Term(Term::getSelectedTerm());

        $term->setMealPlanQueue(1);
        $term->save();

        \NQ::Simple('hms', NotificationView::SUCCESS, 'Meal Plan Queue has been enabled for ' . Term::toString($term->term) . '.');

        $cmd = CommandFactory::getCommand('ShowEditTerm');
        $cmd->redirect();
    }
}
