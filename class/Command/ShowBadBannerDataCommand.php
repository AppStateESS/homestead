<?php

namespace Homestead\Command;

 

class ShowBadBannerDataCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowBadBannerData');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'BadBannerDataView.php');
        $view = new BadBannerDataView();
        $context->setContent($view->show());
    }
}
