<?php

namespace Homestead\command;

use \Homestead\Command;

class ShowHousingApplicationThanksCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowHousingApplicationThanks');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplicationThanksView.php');
        $view = new HousingApplicationThanksView();
        $context->setContent('Submitted housing application');
    }
}
