<?php

namespace Homestead\Command;

use \Homestead\StudentDataProvider;
use \Homestead\NotificationView;

class ClearCacheCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'ClearCache');
    }

    public function execute(CommandContext $context)
    {
        $provider = StudentDataProvider::getInstance();
        $provider->clearCache();

        \NQ::simple('hms', NotificationView::SUCCESS, 'Cache cleared.');
    }

}
