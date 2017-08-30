<?php

namespace Homestead\Command;

use \Homestead\ContactFormView;

class ShowContactFormCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowContactForm');
    }

    public function execute(CommandContext $context)
    {
        $view = new ContactFormView();
        $context->setContent($view->show());
    }
}
