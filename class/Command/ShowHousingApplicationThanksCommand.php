<?php

namespace Homestead\Command;

use \Homestead\HousingApplicationThanksView;

class ShowHousingApplicationThanksCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowHousingApplicationThanks');
    }

    public function execute(CommandContext $context)
    {
        $view = new HousingApplicationThanksView();
        $context->setContent('Submitted housing application');
    }
}
