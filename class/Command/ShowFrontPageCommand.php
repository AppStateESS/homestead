<?php

namespace Homestead\Command;

use \Homestead\FrontPageView;

class ShowFrontPageCommand extends Command {

    public function getRequestVars(){
        return array('action'=>'GetFrontPage');
    }

    public function execute(CommandContext $context)
    {
        $view = new FrontPageView();

        $context->setContent($view->show());
    }
}
