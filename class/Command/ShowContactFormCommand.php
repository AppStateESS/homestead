<?php

namespace Homestead\Command;

 

class ShowContactFormCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'ShowContactForm');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'ContactFormView.php');

        $view = new ContactFormView();
        $context->setContent($view->show());
    }
}
