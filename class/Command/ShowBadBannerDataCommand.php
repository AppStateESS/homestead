<?php

namespace Homestead\Command;

use \Homestead\BadBannerDataView;

class ShowBadBannerDataCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowBadBannerData');
    }

    public function execute(CommandContext $context)
    {
        $view = new BadBannerDataView();
        $context->setContent($view->show());
    }
}
